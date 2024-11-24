<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HR\Database\factories\TimesheetFactory;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'date',
        'task_description',
        'start_time',
        'end_time',
        'hours_spent',
        'notes',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // public function project()
    // {
    //     return $this->belongsTo(Project::class);
    // }

    // Helper method to calculate hours spent
    public function getCalculatedHoursAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return (strtotime($this->end_time) - strtotime($this->start_time)) / 3600;
        }
        return 0;
    }

    protected static function newFactory(): TimesheetFactory
    {
        //return TimesheetFactory::new();
    }
}
