<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * يجعل url() وروابط Livewire (data-update-uri) تطابق مضيف المتصفح الحالي.
 * بدون ذلك، إن كان APP_URL على منفذ آخر عن عنوان الشريط، يفشل POST إلى /livewire/update فيظهر في الواجهة status: null.
 */
class ForceRootUrlFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $root = $request->getSchemeAndHttpHost();
        if ($root !== '') {
            URL::forceRootUrl($root);
        }

        return $next($request);
    }
}
