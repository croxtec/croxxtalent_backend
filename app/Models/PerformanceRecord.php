<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'recordable_id',
        'recordable_type',
        'year',
        'month',
        'overall_score',
        'assessment_score',
        'assessment_participation_rate',
        'peer_review_score',
        'peer_review_trend',
        'goal_completion_rate',
        'goal_participation_rate',
        'project_completion_rate',
        'project_on_time_rate',
        'project_participation_rate',
        'competency_average_score',
        'kpi_overall_achievement',
        'kpi_technical_achievement',
        'kpi_soft_achievement',
        'training_score',
        'training_completion_rate',
        'meta_data'
    ];

    protected $casts = [
        'overall_score' => 'float',
        'assessment_score' => 'float',
        'assessment_participation_rate' => 'float',
        'peer_review_score' => 'float',
        'goal_completion_rate' => 'float',
        'goal_participation_rate' => 'float',
        'project_completion_rate' => 'float',
        'project_on_time_rate' => 'float',
        'project_participation_rate' => 'float',
        'competency_average_score' => 'float',
        'kpi_overall_achievement' => 'float',
        'kpi_technical_achievement' => 'float',
        'kpi_soft_achievement' => 'float',
        'training_score' => 'float',
        'training_completion_rate' => 'float',
        'meta_data' => 'json',
    ];

    /**
     * Get the parent recordable model (Department or Employee).
     */
    public function recordable()
    {
        return $this->morphTo();
    }

    /**
     * Create a performance record from department performance data
     *
     * @param int $recordableId
     * @param string $recordableType
     * @param int $year
     * @param int $month
     * @param array $data Department performance data
     * @param float $overallScore Overall performance score
     * @return self
     */
    public static function createFromDepartmentData($recordableId, $recordableType, $year, $month, $data, $overallScore)
    {
        $sections = $data['sections'] ?? [];
        $kpiAchievement = $data['kpi_achievement'] ?? [];

        $record = new self();
        $record->recordable_id = $recordableId;
        $record->recordable_type = $recordableType;
        $record->year = $year;
        $record->month = $month;
        $record->overall_score = $overallScore;

        // Assessment data
        if (isset($sections['assessments'])) {
            $record->assessment_score = $sections['assessments']['average_score'] ?? 0;
            $record->assessment_participation_rate = $sections['assessments']['employee_participation_rate'] ?? 0;
        }

        // Peer review data
        if (isset($sections['peer_reviews'])) {
            $record->peer_review_score = $sections['peer_reviews']['average_score'] ?? 0;
            $record->peer_review_trend = $sections['peer_reviews']['trend']['direction'] ?? 'stable';
        }

        // Goals data
        if (isset($sections['goals'])) {
            $record->goal_completion_rate = $sections['goals']['completion_rate'] ?? 0;
            $record->goal_participation_rate = $sections['goals']['employee_participation_rate'] ?? 0;
        }

        // Projects data
        if (isset($sections['projects'])) {
            $record->project_completion_rate = $sections['projects']['completion_rate'] ?? 0;
            $record->project_on_time_rate = $sections['projects']['on_time_completion_rate'] ?? 0;
            $record->project_participation_rate = $sections['projects']['employee_participation_rate'] ?? 0;
        }

        // Competencies data
        if (isset($sections['competencies'])) {
            $record->competency_average_score = $sections['competencies']['average_score'] ?? 0;

            // Store detailed competency data in meta_data
            $metaData = $record->meta_data ?? [];
            $metaData['competency_details'] = $sections['competencies']['details'] ?? [];
            $record->meta_data = $metaData;
        }

        // Training data
        if (isset($sections['trainings'])) {
            $record->training_score = $sections['trainings']['average_score'] ?? 0;
            $record->training_completion_rate = $sections['trainings']['completion_rate'] ?? 0;
        }

        // KPI data
        if (isset($kpiAchievement)) {
            $record->kpi_overall_achievement = $kpiAchievement['overall_achievement'] ?? 0;
            $record->kpi_technical_achievement = $kpiAchievement['technical_achievement'] ?? 0;
            $record->kpi_soft_achievement = $kpiAchievement['soft_achievement'] ?? 0;

            // Store detailed KPI data in meta_data
            $metaData = $record->meta_data ?? [];
            $metaData['kpi_details'] = $kpiAchievement['kpi_details'] ?? [];
            $record->meta_data = $metaData;
        }

        $record->save();
        return $record;
    }
}
