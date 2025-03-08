<?php
namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

use App\Models\EmployerJobcode;
use App\Models\PerformanceRecord;
use App\Models\User;

class PerformanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
    */
    public function run(): void
    {
        // Sample department IDs - replace with actual IDs from your database
        $departmentIds = [74, 80]; // Example department IDs (EmployerJobcode)
        $employeeIds = [187, 121, 188]; // Example employee IDs (User)

        // Generate records for each department for the past 12 months
        $currentYear = date('Y');
        $currentMonth = date('n');

        foreach ($departmentIds as $departmentId) {
            for ($month = 1; $month <= 12; $month++) {
                // Skip future months
                if ($currentYear == date('Y') && $month > $currentMonth) {
                    continue;
                }

                $this->createSampleDepartmentRecord($departmentId, $currentYear, $month);
            }
        }

        // Generate records for employees
        foreach ($employeeIds as $employeeId) {
            for ($month = 1; $month <= 12; $month++) {
                // Skip future months
                if ($currentYear == date('Y') && $month > $currentMonth) {
                    continue;
                }

                $this->createSampleEmployeeRecord($employeeId, $currentYear, $month);
            }
        }
    }

    /**
     * Create a sample performance record for a department
     */
    private function createSampleDepartmentRecord($departmentId, $year, $month)
    {
        // Generate sample performance data with some variance between months
        $baseScore = rand(60, 85);
        $variance = rand(-5, 5);

        $record = new PerformanceRecord();
        $record->recordable_id = $departmentId;
        $record->recordable_type = EmployerJobcode::class;
        $record->year = $year;
        $record->month = $month;
        $record->overall_score = max(0, min(100, $baseScore + $variance));

        // Assessment data
        $record->assessment_score = max(0, min(100, $baseScore + rand(-10, 10)));
        $record->assessment_participation_rate = rand(70, 95);

        // Peer review data
        $record->peer_review_score = max(0, min(100, $baseScore + rand(-8, 8)));
        $record->peer_review_trend = ['improving', 'stable', 'declining'][rand(0, 2)];

        // Goals data
        $record->goal_completion_rate = max(0, min(100, $baseScore + rand(-15, 5)));
        $record->goal_participation_rate = rand(65, 90);

        // Project metrics
        $record->project_completion_rate = max(0, min(100, $baseScore + rand(-10, 10)));
        $record->project_on_time_rate = max(0, min(100, $baseScore - rand(5, 15)));
        $record->project_participation_rate = rand(60, 95);

        // Competency metrics
        $record->competency_average_score = max(0, min(100, $baseScore + rand(-7, 7)));

        // KPI metrics
        $record->kpi_overall_achievement = max(0, min(100, $baseScore + rand(-5, 5)));
        $record->kpi_technical_achievement = max(0, min(100, $baseScore + rand(-8, 8)));
        $record->kpi_soft_achievement = max(0, min(100, $baseScore + rand(-12, 12)));

        // Training metrics
        $record->training_score = max(0, min(100, $baseScore + rand(-10, 10)));
        $record->training_completion_rate = rand(75, 95);

        // Detailed data for competencies and KPIs
        $record->meta_data = [
            'competency_details' => $this->generateSampleCompetencyDetails(),
            'kpi_details' => $this->generateSampleKpiDetails()
        ];

        $record->save();
    }

    /**
     * Create a sample performance record for an employee
     */
    private function createSampleEmployeeRecord($employeeId, $year, $month)
    {
        // Similar logic to department records but with employee-specific adjustments
        $baseScore = rand(65, 90);
        $variance = rand(-8, 8);

        $record = new PerformanceRecord();
        $record->recordable_id = $employeeId;
        $record->recordable_type = Employee::class;
        $record->year = $year;
        $record->month = $month;
        $record->overall_score = max(0, min(100, $baseScore + $variance));

        // Fill in employee-specific performance metrics
        // (Similar structure to department metrics but typically higher variance)
        $record->assessment_score = max(0, min(100, $baseScore + rand(-15, 15)));
        $record->competency_average_score = max(0, min(100, $baseScore + rand(-10, 10)));

        // Simplified employee meta_data
        $record->meta_data = [
            'competency_scores' => $this->generateSampleEmployeeCompetencyScores(),
            'strengths' => $this->getRandomStrengths(),
            'areas_for_improvement' => $this->getRandomAreasForImprovement()
        ];

        $record->save();
    }

    /**
     * Generate sample competency details
     */
    private function generateSampleCompetencyDetails()
    {
        $competencies = [
            'software_development', 'cybersecurity', 'network_administration',
            'communication_skills', 'QA Tester'
        ];

        $details = [];
        foreach ($competencies as $competency) {
            $score = rand(50, 85);
            $details[] = [
                'competency' => $competency,
                'average_score' => $score,
                'kpi_count' => rand(1, 3),
                'employee_count' => rand(3, 8),
                'status' => $score >= 70 ? 'satisfactory' : 'needs_improvement'
            ];
        }

        return $details;
    }

    /**
     * Generate sample KPI details
     */
    private function generateSampleKpiDetails()
    {
        $kpiNames = [
            'software_development', 'cybersecurity', 'network_administration',
            'QA Tester', 'communication_skills'
        ];

        $details = [];
        foreach ($kpiNames as $index => $kpiName) {
            $rate = rand(50, 85);
            $category = $index < 4 ? 'technical' : 'soft';

            $details[] = [
                'kpi_name' => $kpiName,
                'kpi_type' => $category === 'technical' ? 'technical_skill' : 'soft_skill',
                'category' => $category,
                'target' => 70,
                'department_achievement_rate' => $rate,
                'status' => $rate >= 70 ? 'satisfactory' : 'needs_improvement',
                'employee_participation' => rand(3, 8)
            ];
        }

        return $details;
    }

    /**
     * Generate sample employee competency scores
     */
    private function generateSampleEmployeeCompetencyScores()
    {
        $competencies = [
            'software_development', 'cybersecurity', 'network_administration',
            'communication_skills', 'problem_solving'
        ];

        $scores = [];
        foreach ($competencies as $competency) {
            $scores[$competency] = rand(50, 95);
        }

        return $scores;
    }

    /**
     * Get random strengths for an employee
     */
    private function getRandomStrengths()
    {
        $allStrengths = [
            'Technical problem solving',
            'Communication with clients',
            'Project management',
            'Code quality',
            'Documentation',
            'Team collaboration',
            'Meeting deadlines',
            'Creative solutions',
            'Knowledge sharing'
        ];

        shuffle($allStrengths);
        return array_slice($allStrengths, 0, rand(2, 4));
    }

    /**
     * Get random areas for improvement for an employee
     */
    private function getRandomAreasForImprovement()
    {
        $allAreas = [
            'Advanced technical skills',
            'Time management',
            'Detailed documentation',
            'Public speaking',
            'Technical writing',
            'Cross-team collaboration',
            'Initiative taking',
            'Leadership skills',
            'Knowledge of emerging technologies'
        ];

        shuffle($allAreas);
        return array_slice($allAreas, 0, rand(1, 3));
    }
}
