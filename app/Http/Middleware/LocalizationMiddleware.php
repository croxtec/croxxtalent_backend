<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LocalizationMiddleware
{ 
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->determineLocale($request);
        
        if ($this->isValidLocale($locale)) {
            App::setLocale($locale);
            // Log for debugging
            Log::info('Locale set to: ' . $locale);
        } else {
            // Log invalid locale attempts
            Log::warning('Invalid locale attempted: ' . $locale);
        }

        return $next($request);
    }

    /**
     * Determine the locale from various sources
     */
    private function determineLocale(Request $request): string
    {
        // Priority order:
        // 1. URL parameter (highest priority for testing)
        if ($request->has('lang') && $this->isValidLocale($request->get('lang'))) {
            return $request->get('lang');
        }

        // 2. Authenticated user's language preference
        if (Auth::check() && Auth::user()->language && $this->isValidLocale(Auth::user()->language)) {
            return Auth::user()->language;
        }

        // 3. Request header (for API calls)
        if ($request->hasHeader('Accept-Language')) {
            $locale = $this->parseAcceptLanguageHeader($request->header('Accept-Language'));
            if ($locale && $this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 4. Default fallback
        return config('app.locale', 'en');
    }

    /**
     * Parse Accept-Language header properly
     */
    private function parseAcceptLanguageHeader(string $header): ?string
    {
        // Handle headers like "fr-FR,fr;q=0.9,en;q=0.8"
        $languages = explode(',', $header);
        
        foreach ($languages as $language) {
            $locale = trim(explode(';', $language)[0]);
            $locale = substr($locale, 0, 2); // Get first 2 characters
            
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }
        
        return null;
    }

    /**
     * Check if the locale is supported
     */
    private function isValidLocale(string $locale): bool
    {
        $supportedLocales = config('app.supported_locales', ['en', 'fr']);
        return in_array($locale, $supportedLocales);
    }
}