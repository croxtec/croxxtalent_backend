<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'key', 'value'];


    protected $casts = [
        'value' => 'array',
    ];

    protected $attributes = [
        'value' => '[]',
    ];


    public static function setUserSetting($userId, $key, $value)
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function getUserSetting($userId, $key, $default = null)
    {
        $setting = self::where('user_id', $userId)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function generaeDefaultSetting($user){
        $defaultSettings = [
            'general_notifications' => [
                'new_messages' => false,//Consideration
                'job_postings' => true,
                'platform_updates' => true,
                'weekly_blog_digest' => true
            ],
            'security_notifications' => [
                'login_alerts' => true,// No Provision
                'password_changes' => true,
                'suspicious_activity' => true //Consideration
            ],
            'profile_access' => [
                "open_job" => true,
                "resume_available" => true,
                "profile_available" => true,
            ],
            // Document Request Table
            "companies_documentation" => [

            ]
            //Single Company Content Table
            // Single General Settings Table
            // Consideration Social media
        ];

        foreach ($defaultSettings as $key => $value) {
            UserSetting::setUserSetting($user->id, $key, $value);
        }
    }
}
