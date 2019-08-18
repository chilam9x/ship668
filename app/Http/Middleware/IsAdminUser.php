<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class IsAdminUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = $request->user();

        if (empty($user)) {
            // abort(403, 'Bạn không có quyền truy cập vào trang này!');
            return redirect('login');
        } else {
            if (($user->role != 'admin' && $user->role != 'collaborators') || empty($user->role)) {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
