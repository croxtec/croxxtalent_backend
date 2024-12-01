<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HR\Database\factories\ShiftTemplateFactory;

class ShiftTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'shift_name',
        'start_time',
        'end_time',
        'status',
    ];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper method to calculate shift duration
    public function getShiftDurationAttribute()
    {
        return (strtotime($this->end_time) - strtotime($this->start_time)) / 3600; // Returns duration in hours
    }

    protected static function newFactory(): ShiftTemplateFactory
    {
        //return ShiftTemplateFactory::new();
    }
}
