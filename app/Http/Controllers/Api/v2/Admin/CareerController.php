<?php

namespace App\Http\Controllers\Api\v2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competency\CompetencySetup;
use App\Models\CV;
use App\Libraries\OpenAIService;

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

    public function review(Request $request)
    {
        $cvJobTitles = CV::select('job_title')->distinct()->pluck('job_title');
        $competencyJobTitles = CompetencySetup::select('job_title')->distinct()->pluck('job_title');

        // Combine and get unique job titles
        $allJobTitles = $cvJobTitles->concat($competencyJobTitles)->unique();

        $competencies = CompetencySetup::select('job_title', 'competency')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('job_title', $allJobTitles)
            ->groupBy('job_title', 'competency')
            ->get();

        // Organize the data
        $data = $allJobTitles->mapWithKeys(function ($jobTitle) use ($competencies) {
            $jobCompetencies = $competencies->where('job_title', $jobTitle);
            return [
                $jobTitle => [
                    'competencies' => $jobCompetencies->map(function ($item) {
                        return $a[] = [
                            'name' => $item->competency,
                            'total' => $item->total
                        ];
                    }),
                    'total_competencies' => $jobCompetencies->sum('total')
                ]
            ];
        });

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $job_title = "Web Developer";

        $existingCompetenciesCount = CompetencySetup::where('job_title', $job_title)->count();

        if ($existingCompetenciesCount < 8) {
            $competencies = $this->openAIService->generateCompetenciesByJobTitle($job_title);

            foreach ($competencies as $competency) {
                CompetencySetup::firstOrCreate([
                    'industry_id' => $request->input('industry_id') ?? 1,
                    'job_title' => $job_title,
                    'competency' => $competency['competency'],
                ],[
                    'match_percentage' => (int)$competency['match_percentage'],
                    'benchmark' => (int)$competency['benchmark'],
                    'description' => $competency['description'],
                ]);
            }
        }

        $allCompetencies = CompetencySetup::where('job_title', $job_title)->get();

        return response()->json([
            'status' => true,
            'message' => "Competencies for '{$job_title}'",
            'data' => $allCompetencies
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
