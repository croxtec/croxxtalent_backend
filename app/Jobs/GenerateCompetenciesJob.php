<?php

namespace App\Jobs;

// use App\Models\CompetencySetup;

use App\Models\Competency\CompetencySetup;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\CroxxAI\CroxxAIService;

class GenerateCompetenciesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobTitle;
    public $industryId;
    public $userId;
    public $language;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($jobTitle, $industryId, $userId, $language = 'en')
    {
        $this->jobTitle = $jobTitle;
        $this->industryId = $industryId;
        $this->userId = $userId;
        $this->language = $language;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService, CroxxAIService $croxxAI )
    {
        try {
            Log::info("Starting competency generation", [
                'job_title' => $this->jobTitle,
                'user_id' => $this->userId,
                'industry_id' => $this->industryId
            ]);

            // Check if competencies still need to be generated
            $existingCount = CompetencySetup::where('job_title', $this->jobTitle)
                                ->where('language', $this->language)->count();

            if ($existingCount >= 8) {
                Log::info("Competencies already exist, skipping generation", [
                    'job_title' => $this->jobTitle,
                    'existing_count' => $existingCount
                ]);
                return;
            }

            // Generate competencies using OpenAI
            $competencies = $croxxAI->generateCompetenciesByJobTitle($this->jobTitle,  $this->language);

            if (empty($competencies)) {
                throw new \Exception('No competencies returned from OpenAI service');
            }

            $createdCount = 0;
            $competenciesNeeded = 8 - $existingCount;
            $competenciesToProcess = array_slice($competencies, 0, $competenciesNeeded);

            // Store the competencies
            foreach ($competencies as $competency) {
                $created = CompetencySetup::firstOrCreate([
                    'industry_id' => $this->industryId,
                    'language' => $this->language,
                    'job_title' => $this->jobTitle,
                    'competency' => $competency['competency'],
                ], [
                    'match_percentage' => (int)($competency['match_percentage'] ?? 75),
                    'benchmark' => (int)($competency['benchmark'] ?? 70),
                    'description' => $competency['description'] ?? '',
                ]);

                if ($created->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            Log::info("Competency generation completed", [
                'job_title' => $this->jobTitle,
                'user_id' => $this->userId,
                'created_count' => $createdCount,
                'total_competencies' => $existingCount + $createdCount
            ]);

            // Optional: Notify user that generation is complete
            // You can dispatch a notification job here if needed

        } catch (\Exception $e) {
            Log::error("Competency generation failed", [
                'job_title' => $this->jobTitle,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Rethrow to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Competency generation job failed permanently", [
            'job_title' => $this->jobTitle,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        // Optional: Notify administrators or queue for manual review
    }

}
