<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function handle($request, Closure $next)
    {
        /*
        if(!Session::has('locale'))
        {
            Session::put('locale', $request->getPreferredLanguage());
        }

        var_dump(Session::get('locale'));
        echo "<br>";
        var_dump(app()->currentLocale());
        echo "<br>";
        var_dump(app()->getLocale());

        app()->setLocale(Session::get('locale'));
        */

        if (session()->has('applocale')) {
            App::setLocale(session()->get('applocale'));
        }
        else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(config('app.fallback_locale'));
        }

        return $next($request);
    }
}
