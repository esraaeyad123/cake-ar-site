<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * لغة صفحات الكيك (عربي / إنجليزي): ?lang=en أو ?lang=ar تتحفظ في الجلسة، والافتراضي عربي.
 * لما تنتقل الصفحات لموقع جوماكيك، لغة موقعهم هي اللي تمشي وهذا الملف ما يحتاج.
 */
class SetCakeLocale
{
    public const LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->query('lang');
        if (in_array($lang, self::LOCALES, true) && $request->hasSession()) {
            $request->session()->put('cake_locale', $lang);
        }

        $locale = in_array($lang, self::LOCALES, true) ? $lang
            : ($request->hasSession() ? $request->session()->get('cake_locale', 'ar') : 'ar');
        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'ar');

        return $next($request);
    }
}
