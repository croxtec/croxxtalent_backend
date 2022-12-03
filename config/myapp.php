<?php
/**
 * My Custom App Configuration
 */

 return [
    'name' => env('APP_NAME', 'Laravel'),
    'domain' => env('DOMAIN', 'api.localhost.test'),
    'api_domain' => env('API_DOMAIN', 'api.localhost.test'),
    'url' => env('APP_URL', 'http://localhost.test'),
    'api_url' => env('API_URL', 'http://api.localhost.test/v1'),
    'asset_url' => env('ASSET_URL', null),
 ];