
@yield('email_body')

@yield('email_complimentary_close')

@yield('client_geo_location')

(c) {{ Carbon\Carbon::now()->format('Y') }} {{ config('myapp.name') }}. All rights reserved.
