<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'employee_id',
        'is_supervisor'
    ];



    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id');
    }

}
