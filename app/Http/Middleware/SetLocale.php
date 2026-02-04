<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Check for Accept-Language header
    $locale = $request->header('Accept-Language');

    // Also support X-Locale header for explicit locale setting
    if (!$locale) {
      $locale = $request->header('X-Locale');
    }

    // Parse the locale (e.g., "ar-SA,ar;q=0.9,en;q=0.8" -> "ar")
    if ($locale) {
      $locale = strtolower(substr($locale, 0, 2));
    }

    // Validate the locale - only allow supported locales
    $supportedLocales = ['en', 'ar'];

    if ($locale && in_array($locale, $supportedLocales)) {
      App::setLocale($locale);
    } else {
      // Default to English
      App::setLocale('en');
    }

    return $next($request);
  }
}
