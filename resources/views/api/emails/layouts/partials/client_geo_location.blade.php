<div style="background-color: #eeeeee; padding: 5px; font-size: 13px; overflow:hidden;">
    When and where this happened:<br>
    <table style="width: 100%; border:none;background-color: #eeeeee; font-size: 13px;">
        <tr>
            <td valign="top" style="color: #9b9b9b;">Date:</td>
            <td valign="top">{{ $clientGeoLocation->dateTime }} (UTC/GMT)</td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">IP:</td>
            <td valign="top"><a target="_blank" href="http://db-ip.com/{{ $clientGeoLocation->ip }}">{{ $clientGeoLocation->ip }}</a></td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">Browser:</td>
            <td valign="top">{{ $clientGeoLocation->browser }}</td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">Operating System:</td>
            <td valign="top">{{ $clientGeoLocation->os }} </td>
        </tr>
        <tr>
            <td valign="top" style="color: #9b9b9b;">Approximate Location:</td>
            <td valign="top">{{ $clientGeoLocation->location }}</td>
        </tr>
    </table>
    <br>
    {{-- <strong>Didn't do this?</strong> Be sure to <a href="{{ route('password_reset') }}">reset your password</a> right away. --}}
    <strong>Didn't do this?</strong> Be sure to reset your password right away.
</div>
