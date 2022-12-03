<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeoIPLocation;
use Jenssegers\Agent\Agent;

class Audit extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'url',
        'ip',
        'location',
        'user_agent',
        'browser',
        'os',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'event_title', 'event_description'
    ];

    /**
     * Get all of the owning auditable models.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Save audit log
     * 
     * @param int $user_id
     * @param string $event
     * @param array $old_values
     * @param array $new_values
     * @param string $auditable_type
     * @param int $auditable_id
     * @return \App\Models\Audit
     */
    public static function log($user_id, $event, array $old_values = [], array $new_values = [], $auditable_type = null, $auditable_id = null)
    {
        $agent = new Agent();
        $browser = $agent->browser();
        $browser_name = $browser . ' ' . $agent->version($browser);
        $platform = $agent->platform();
        $os_name = $platform . ' ' . $agent->version($platform);

        $audit_data = [];
        $audit_data['user_id'] = $user_id;
        $audit_data['event'] = $event;

        if (count($old_values) > 0 && count($new_values) > 0) {
            $old_values = collect($old_values);
            $new_values = $new_values;

            $old_values = $old_values->only(array_keys($new_values));
            $old_values = $old_values->toArray();
            $new_values = array_diff($new_values, $old_values);
            $new_values = count($new_values) > 0 ? $new_values : null;

            $audit_data['old_values'] = $old_values;
            $audit_data['new_values'] = $new_values;
        } else {
            $audit_data['old_values'] = count($old_values) > 0 ? $old_values : null;
            $audit_data['new_values'] = count($new_values) > 0 ? $new_values : null;
        }

        $audit_data['auditable_type'] = $auditable_type;
        $audit_data['auditable_id'] = $auditable_id;

        $audit_data['url'] = url()->full();
        $audit_data['ip'] = GeoIPLocation::getIP();
        $audit_data['location'] = GeoIPLocation::getLocation();
        $audit_data['user_agent'] = request()->header('User-Agent');
        $audit_data['browser'] = $browser_name;
        $audit_data['os'] = $os_name;

        return self::create($audit_data);
    }

    // Get Custom Model Attributes

    public function getEventTitleAttribute()
    {
        switch ($this->event) {
            case 'login':
                $value = "Login";
                break;   
            case 'register':
                $value = "Registration";
                break;   
            case 'email_verified':
                $value = "Email Verified";
                break;
            case 'users.email.updated':
                $value = "Email Changed";
                break;
            case 'users.updated':
                $value = "Profile Updated";
                break;
            case 'users.photo.updated':
                $value = "Photo Updated";
                break;
            case 'cvs.created':
                $value = "CV Created";
                break;               
            case 'cvs.updated':
                $value = "CV Updated";
                break;               
            default:
                $value = preg_replace("/_/", ' ', $this->event);
                $value = ucwords($value);
                break;
        }
        $event_title = $value;
        return $event_title;
    }

    public function getEventDescriptionAttribute()
    {
        switch ($this->event) {
            case 'login':
                $value = "Logged in from {$this->location}.";
                break;   
            case 'register':
                $value = "Registered from {$this->location}.";
                break;   
            case 'email_verified':
                $value = "Account email address verified.";
                break;
            case 'users.email.updated':
                $value = "Email changed";
                break;
            case 'users.updated':
                $value = "Updated profile information.";
                break;
            case 'users.photo.updated':
                $value = "Updated profile photo.";
                break;
            case 'cvs.created':
                $value = "Created a new CV.";
                break;               
            case 'cvs.updated':
                $value = "Updated CV information.";
                break;               
            default:
                $value = preg_replace("/_/", ' ', $this->event);
                $value = ucfirst($value);
                break;
        }
        $event_desc = $value;
        return $event_desc;
    }

}
