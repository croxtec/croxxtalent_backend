<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->getLocale($request);
        
        // Validate locale
        if (!in_array($locale, config('app.available_locales'))) {
            $locale = config('app.fallback_locale');
        }
        
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        return $next($request);
    }
    
    /**
     * Get locale from various sources in order of priority
     */
    private function getLocale(Request $request): string
    {
        // 1. URL parameter (?lang=es)
        if ($request->has('lang')) {
            return $request->get('lang');
        }
        
        // 2. Authorization header for API (Accept-Language)
        if ($request->hasHeader('Accept-Language')) {
            $acceptLanguage = $request->header('Accept-Language');
            $locale = substr($acceptLanguage, 0, 2);
            if (in_array($locale, config('app.available_locales'))) {
                return $locale;
            }
        }
        
        // 3. User preference (if authenticated) - prioritize user's saved language
        if (auth()->check() && auth()->user()->language) {
            return auth()->user()->language;
        }
        
        // 4. Session
        if (Session::has('locale')) {
            return Session::get('locale');
        }
        
        // 5. Browser detection
        if ($request->hasHeader('Accept-Language')) {
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($browserLang, config('app.available_locales'))) {
                return $browserLang;
            }
        }
        
        return config('app.fallback_locale');
    }
}