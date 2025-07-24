<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'employer_id',
        'employee_id',
        'mediable_type',
        'mediable_id',
        'collection_name',
        'file_name',
        'original_name',
        'file_type',
        'file_size',
        'file_url',
        'cloudinary_public_id',
        'metadata',
        'uploaded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'uploaded_at' => 'datetime'
    ];

    // Polymorphic relationship
    public function mediable()
    {
        return $this->morphTo();
    }

    // Relationships for easy querying
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employer()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes for easy filtering
    public function scopeForCompany($query, $employerId)
    {
        return $query->where('employer_id', $employerId);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByCollection($query, $collection)
    {
        return $query->where('collection_name', $collection);
    }
}