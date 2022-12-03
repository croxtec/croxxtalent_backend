<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Verification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'verifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'action',
        'token',
        'sent_to',
        'is_otp',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_otp' => 'boolean',
        'metadata' => 'json',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'country_name'
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($query) {
            
            // generate and set token value
            if (!$query->token) {
                if ($query->is_otp) {
                    $query->token = self::generateOTP($query->sent_to);
                } else {
                    $query->token = self::generateToken($query->verifiable_id . '-'. $query->sent_to);
                }
            }

            // delete recently created verification token of the same action and verifiable
            self::where('verifiable_type', $query->verifiable_type)
                ->where('verifiable_id', $query->verifiable_id)
                ->where('action', $query->action)
                ->where('sent_to', $query->sent_to)
                ->where('is_otp', $query->is_otp)
                ->delete();
        });
    }

    /**
     * Get all of the owning verifiable models.
     */
    public function verifiable()
    {
        return $this->morphTo();
    }

    /**
     * Generate new unique verification token.
     *
     * @param string $hash_key
     * @return string
     */
    public static function generateToken($hash_string = null)
    {
        // first, call to delete expired tokens
        self::deleteExpiredTokens('all');
        // proceed
        $hash_key = (empty($hash_string)) ? Str::random(10) : $hash_string;
        $hash_data = Str::random(15) . $hash_string . Str::random(15);
        $token = hash_hmac('sha256', $hash_data, $hash_key);
        $verification = self::where(['token' => $token])->first();
        if (!$verification) {
            return $token; // the token is valid
        } else {
            $hash_string . Str::random(5);
            return self::generateToken($hash_string); // token already exist, call recursively to re-generate
        }
    }

    public static function generateOTP($sent_to)
    {
        // first, call to delete expired tokens
        self::deleteExpiredTokens('all');
        // proceed
        $token = mt_rand (100000, 999999);
        $verification = self::where(['sent_to' => $sent_to, 'token' => $token])->first();
        if (!$verification) {
            return $token; // the token is valid
        } else {
            return self::generateToken($sent_to); // token already exist, call recursively to re-generate
        }
    }


    /**
     * Delete expired tokens
     *
     * @param string $type all | otp | token
     */
    private static function deleteExpiredTokens ($type = 'all')
    {
        if ($type == 'all' || $type == 'token') {
            // delete  expired tokens: older than 3 days
            $dt = Carbon::now();
            $expired_datetime = $dt->subDays(3);
            self::where('is_otp', false)
                ->where('created_at', '<', $expired_datetime)
                ->delete();
        }

        if ($type == 'all' || $type == 'otp') {
            // delete  otp older than 30 mins
            $dt = Carbon::now();
            $expired_datetime = $dt->subMinutes(30);
            self::where('is_otp', true)
                ->where('created_at', '<', $expired_datetime)
                ->delete();
        }
    }
}
