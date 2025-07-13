<div style="background-color: #eeeeee; padding: 5px; font-size: 13px; overflow:hidden;">
    {{ __('notifications.geo_location.title') }}<br>
    <table style="width: 100%; border:none;background-color: #eeeeee; font-size: 13px;">
        <tr>
            <td valign="top" style="color: #9b9b9b;">{{ __('notifications.geo_location.date') }}</td>
            <td valign="top">{{ $clientGeoLocation->dateTime }} (UTC/GMT)</td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">{{ __('notifications.geo_location.ip') }}</td>
            <td valign="top"><a target="_blank" href="http://db-ip.com/{{ $clientGeoLocation->ip }}">{{ $clientGeoLocation->ip }}</a></td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">{{ __('notifications.geo_location.browser') }}</td>
            <td valign="top">{{ $clientGeoLocation->browser }}</td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">{{ __('notifications.geo_location.os') }}</td>
            <td valign="top">{{ $clientGeoLocation->os }}</td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">{{ __('notifications.geo_location.location') }}</td>
            <td valign="top">{{ $clientGeoLocation->location }}</td>
        </tr>
    </table>
    <br>
    <strong>{{ __('notifications.geo_location.security_warning') }}</strong> {{ __('notifications.geo_location.security_action') }}
</div>