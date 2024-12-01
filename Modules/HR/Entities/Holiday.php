<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HR\Database\factories\HolidayFactory;

class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    use HasFactory;

    protected $fillable = [
        'holiday_name',
        'holiday_date',
        'type',
        'company_id',
        'applicable_to',
        'is_recurring',
    ];

    protected $casts = [
        'applicable_to' => 'array', // Convert JSON to array
        'holiday_date' => 'date',  // Handle as Carbon date
    ];

    // Check if a holiday applies to an employee
    public function appliesToEmployee($employeeId, $departmentId)
    {
        if (in_array($employeeId, $this->applicable_to['employees'] ?? [])) {
            return true;
        }

        if (in_array($departmentId, $this->applicable_to['departments'] ?? [])) {
            return true;
        }

        return false;
    }


    protected static function newFactory(): HolidayFactory
    {
        //return HolidayFactory::new();
    }
}
