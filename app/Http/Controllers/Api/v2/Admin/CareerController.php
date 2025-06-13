<?php

namespace App\Http\Controllers\Api\v2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competency\CompetencySetup;
use App\Models\CV;
use App\Services\OpenAIService;

class CareerController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $this->authorize('view-any', CompetencySetup::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'job_title');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any
        $industry = $request->input('industry');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $competencies = CompetencySetup::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->where('status', 'drafted');
                } else {
                    $query->where('status', 'publish');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('competency', 'LIKE', "%{$search}%")
                  ->orWhere('job_title', 'LIKE', "%{$search}%");
        })
        ->when($industry, function($query) use ($industry){
            $query->where('industry_id', $industry);
        })
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $competencies->get();
            $competencies = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $competencies = $competencies->paginate($per_page);
        }

        foreach($competencies as $job){
            $job->industry;
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($competencies)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }


    /**
     * Review function - showcases all inputted job titles with their competency statistics
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function review(Request $request)
    {
        try {
            // Get all unique job titles from both CV and CompetencySetup tables
            $cvJobTitles = CV::select('job_title')
                ->whereNotNull('job_title')
                ->where('job_title', '!=', '')
                ->distinct()
                ->pluck('job_title');

            $competencyJobTitles = CompetencySetup::select('job_title')
                ->whereNotNull('job_title')
                ->where('job_title', '!=', '')
                ->distinct()
                ->pluck('job_title');

            // Combine and get unique job titles
            $allJobTitles = $cvJobTitles->concat($competencyJobTitles)->unique()->values();

            if ($allJobTitles->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => "No job titles found in the system",
                    'data' => []
                ], 200);
            }

            // Get competency statistics for each job title
            $competencyStats = CompetencySetup::select('job_title', 'competency', 'industry_id')
                ->selectRaw('COUNT(*) as frequency')
                ->selectRaw('AVG(match_percentage) as avg_match_percentage')
                ->selectRaw('AVG(benchmark) as avg_benchmark')
                ->whereIn('job_title', $allJobTitles)
                ->groupBy('job_title', 'competency', 'industry_id')
                ->get();

            // Get CV count for each job title
            $cvCounts = CV::select('job_title')
                ->selectRaw('COUNT(*) as cv_count')
                ->whereIn('job_title', $allJobTitles)
                ->groupBy('job_title')
                ->pluck('cv_count', 'job_title');

            // Organize the data by job title
            $data = $allJobTitles->map(function ($jobTitle) use ($competencyStats, $cvCounts) {
                $jobCompetencies = $competencyStats->where('job_title', $jobTitle);

                return [
                    'job_title' => $jobTitle,
                    'cv_count' => $cvCounts->get($jobTitle, 0),
                    'competencies' => $jobCompetencies->map(function ($item) {
                        return [
                            'name' => $item->competency,
                            'frequency' => $item->frequency,
                            'avg_match_percentage' => round($item->avg_match_percentage, 2),
                            'avg_benchmark' => round($item->avg_benchmark, 2),
                            'industry_id' => $item->industry_id
                        ];
                    })->values(),
                    'total_competencies' => $jobCompetencies->count(),
                    'total_competency_entries' => $jobCompetencies->sum('frequency'),
                    'competency_coverage' => $jobCompetencies->count() >= 8 ? 'Complete' : 'Incomplete',
                    'needs_generation' => $jobCompetencies->count() < 8
                ];
            })->sortBy('job_title')->values();

            return response()->json([
                'status' => true,
                'message' => "Found " . $allJobTitles->count() . " unique job titles",
                'summary' => [
                    'total_job_titles' => $allJobTitles->count(),
                    'job_titles_with_competencies' => $competencyStats->pluck('job_title')->unique()->count(),
                    'job_titles_needing_generation' => $data->where('needs_generation', true)->count()
                ],
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "An error occurred while fetching job titles review",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store function - processes and generates competencies for a specific job title
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'job_title' => 'required|string|max:255',
                'industry_id' => 'nullable|integer|exists:industries,id',
                'force_regenerate' => 'nullable|boolean'
            ]);

            $jobTitle = trim($request->input('job_title'));
            $industryId = $request->input('industry_id', 1);
            $forceRegenerate = $request->input('force_regenerate', false);

            // Check existing competencies for this job title
            $existingCompetencies = CompetencySetup::where('job_title', $jobTitle)->get();
            $existingCount = $existingCompetencies->count();

            // Determine if we need to generate competencies
            $shouldGenerate = $existingCount < 8 || $forceRegenerate;

            if (!$shouldGenerate) {
                return response()->json([
                    'status' => true,
                    'message' => "Competencies for '{$jobTitle}' already exist and are complete",
                    'data' => [
                        'job_title' => $jobTitle,
                        'existing_count' => $existingCount,
                        'competencies' => $existingCompetencies->map(function ($competency) {
                            return [
                                'id' => $competency->id,
                                'competency' => $competency->competency,
                                'match_percentage' => $competency->match_percentage,
                                'benchmark' => $competency->benchmark,
                                'description' => $competency->description,
                                'industry_id' => $competency->industry_id
                            ];
                        })
                    ],
                    'generated_new' => false
                ], 200);
            }

            // If force regenerate is true, delete existing competencies
            if ($forceRegenerate && $existingCount > 0) {
                CompetencySetup::where('job_title', $jobTitle)->delete();
                $existingCount = 0;
            }

            // Generate competencies using OpenAI service
            $competencies = $this->openAIService->generateCompetenciesByJobTitle($jobTitle);

            if (empty($competencies)) {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to generate competencies for '{$jobTitle}'. Please try again.",
                    'data' => []
                ], 400);
            }

            $createdCompetencies = [];
            $competenciesNeeded = max(0, 8 - $existingCount);
            $competenciesToProcess = array_slice($competencies, 0, $competenciesNeeded);

            // Store the generated competencies
            foreach ($competenciesToProcess as $competency) {
                $createdCompetency = CompetencySetup::firstOrCreate([
                    'industry_id' => $industryId,
                    'job_title' => $jobTitle,
                    'competency' => $competency['competency'],
                ], [
                    'match_percentage' => (int)$competency['match_percentage'],
                    'benchmark' => (int)$competency['benchmark'],
                    'description' => $competency['description'] ?? '',
                ]);

                $createdCompetencies[] = $createdCompetency;
            }

            // Get all competencies for this job title (existing + newly created)
            $allCompetencies = CompetencySetup::where('job_title', $jobTitle)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => "Successfully processed competencies for '{$jobTitle}'",
                'data' => [
                    'job_title' => $jobTitle,
                    'industry_id' => $industryId,
                    'total_competencies' => $allCompetencies->count(),
                    'newly_generated' => count($createdCompetencies),
                    'existing_count' => $existingCount,
                    'competencies' => $allCompetencies->map(function ($competency) {
                        return [
                            'id' => $competency->id,
                            'competency' => $competency->competency,
                            'match_percentage' => $competency->match_percentage,
                            'benchmark' => $competency->benchmark,
                            'description' => $competency->description,
                            'industry_id' => $competency->industry_id,
                            'created_at' => $competency->created_at->format('Y-m-d H:i:s')
                        ];
                    })
                ],
                'generated_new' => count($createdCompetencies) > 0
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Validation failed",
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "An error occurred while processing competencies",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to batch process multiple job titles
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchStore(Request $request)
    {
        try {
            $request->validate([
                'job_titles' => 'required|array|min:1',
                'job_titles.*' => 'required|string|max:255',
                'industry_id' => 'nullable|integer|exists:industries,id',
                'force_regenerate' => 'nullable|boolean'
            ]);

            $jobTitles = $request->input('job_titles');
            $results = [];

            foreach ($jobTitles as $jobTitle) {
                $request->merge(['job_title' => $jobTitle]);
                $response = $this->store($request);
                $results[] = [
                    'job_title' => $jobTitle,
                    'success' => $response->getStatusCode() < 400,
                    'data' => $response->getData()
                ];
            }

            return response()->json([
                'status' => true,
                'message' => "Batch processing completed",
                'results' => $results,
                'summary' => [
                    'total_processed' => count($results),
                    'successful' => collect($results)->where('success', true)->count(),
                    'failed' => collect($results)->where('success', false)->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Batch processing failed",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
