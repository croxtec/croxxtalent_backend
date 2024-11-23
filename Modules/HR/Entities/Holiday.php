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
        'name',
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

    protected static function newFactory(): HolidayFactory
    {
        //return HolidayFactory::new();
    }
}
