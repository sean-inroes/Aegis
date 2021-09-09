<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            if(Auth::user()->getRoleNames()[0] == "관리자" && Route::is('admin.*'))
            {
                return redirect()->route('admin.index');
            }
            else
            {
                return redirect()->route('user.dashboard.index');
            }
        }

        return $next($request);
    }
}
