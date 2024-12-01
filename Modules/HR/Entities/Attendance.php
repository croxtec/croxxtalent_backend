<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HR\Database\factories\AttendanceFactory;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper method to calculate hours worked
    public function getHoursWorkedAttribute()
    {
        if ($this->check_in && $this->check_out) {
            return (strtotime($this->check_out) - strtotime($this->check_in)) / 3600;
        }
        return 0;
    }

    protected static function newFactory(): AttendanceFactory
    {
        //return AttendanceFactory::new();
    }
}
