When and where this happened:
Date:                       {{ $clientGeoLocation->dateTime }} (UTC/GMT)
IP:                         {{ $clientGeoLocation->ip }} ==> (http://db-ip.com/{{ $clientGeoLocation->ip }})
Browser:                    {{ $clientGeoLocation->browser }}
Operating System:           {{ $clientGeoLocation->os }}
Approximate Location:       {{ $clientGeoLocation->location }}

{{-- Didn't do this? Be sure to reset your password right away: {{ route('password_reset') }} --}}
Didn't do this? Be sure to reset your password right away.
