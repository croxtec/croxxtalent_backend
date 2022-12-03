<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'payment_id',
        'currency_code',
        'amount',
        'start_at',
        'end_at',
        'renew_at',
        'renew_attempt',
        'is_current',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'renew_at' => 'datetime',
        'is_current' => 'boolean',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'country_name'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo('App\Models\SubscriptPlan', 'subscription_plan_id', 'id');
    }
}
