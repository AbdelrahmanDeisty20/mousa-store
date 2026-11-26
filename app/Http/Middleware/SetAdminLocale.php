<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    /**
     * Set active locale for the Filament admin panel based on session preference.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');

        if (in_array($requested, ['ar', 'en'], true)) {
            session(['lang' => $requested]);
            $locale = $requested;
        } else {
            $locale = session('lang', 'ar');
        }

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
