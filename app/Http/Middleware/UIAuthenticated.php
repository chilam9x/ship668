<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UIAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guest()) {
            return redirect('/');
        }

        if (Auth::user()->role != 'customer') {
            abort(403, 'Bạn không có quyền truy cập vào trang này!');
        }

        return $next($request);
    }
}
