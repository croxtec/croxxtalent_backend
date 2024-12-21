<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HR\Database\factories\PolicyFactory;

class Policy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'policy_name',
        'department_id',
        'policy_description',
        'status',
        'policy_document'
    ];

    protected static function newFactory(): PolicyFactory
    {
        //return PolicyFactory::new();
    }
}
