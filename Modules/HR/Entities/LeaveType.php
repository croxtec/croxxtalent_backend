<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\HR\Database\factories\LeaveTypeFactory;

class LeaveType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected $casts = [
        'monthly_limit' => 'float',
    ];


    protected static function newFactory(): LeaveTypeFactory
    {
        //return LeaveTypeFactory::new();
    }


    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'leave_type_id');
    }

    public function leavesCount(): HasOne
    {
        return $this->hasOne(Leave::class, 'leave_type_id')
            ->selectRaw('leave_type_id, count(*) as count, SUM(if(duration="half day", 1, 0)) AS halfday')
            ->groupBy('leave_type_id');
    }
}
