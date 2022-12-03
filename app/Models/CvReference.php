<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\CvReferenceRequest;

class CvReference extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cv_references';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'cv_id',
        'name',
        'company',
        'position',
        'email',
        'phone',
        'description',
        'sort_order',
        'is_approved',
        'approved_at',
        'feedback',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'feedback' => 'json',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'country_name'
    ];

    // Set Custom Attributes

    public function setPhoneAttribute($value)
    {
        if ($value) {
            $value = preg_replace("/ /", '', $value);
        }
        $this->attributes['phone'] = $value;
    }

    public function cv()
    {
        return $this->belongsTo('App\Models\Cv', 'cv_id', 'id');
    }

    public function sendReferenceRequestEmail()
    {
        // send email notification
        if ($this->email && !$this->is_approved) {      
            $cvReference = $this;        
            // if (config('mail.queue_send')) {
            //     Mail::to($this->email)->queue(new CvReferenceRequest($cvReference));
            // } else {
            // }
            Mail::to($this->email)->send(new CvReferenceRequest($cvReference));
        }
    }
}
