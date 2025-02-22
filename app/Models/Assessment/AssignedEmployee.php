<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssignedEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'employee_id',
        'is_supervisor'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
            ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }


}
