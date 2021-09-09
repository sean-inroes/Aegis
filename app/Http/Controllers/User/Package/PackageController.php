<?php

namespace App\Http\Controllers\User\Package;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\UserLogPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class PackageController extends Controller
{
    public function buy()
    {
        $packages = Package::where('status', 1)->get();
        $purchase = Purchase::where('user_id', Auth::id())->orderby('id', 'desc')->first();
        return view('user.pages.package.purchase', compact('packages', 'purchase'));
    }

    public function list(Request $request)
    {
        $start = $request->has('start') ? $request->get('start') : null;
        $end = $request->has('end') ? $request->get('end') : null;

        if($start == null || $end == null)
        {
            $now = Carbon::now();
            $start = $now->startOfWeek()->timestamp;
            $end = $now->endOfWeek()->timestamp;
        }

        $startdate = Carbon::createFromTimestamp($start)->format('Y-m-d');
        $enddate = Carbon::createFromTimestamp($end)->format('Y-m-d');

        $logs = Purchase::where('user_id', Auth::id())
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->orderByDesc('id')
            ->paginate(20);

        $agent = new Agent();

        if($agent->isMobile())
        {
            return view('user.pages.package.mobile-list', compact('logs', 'startdate', 'enddate'));
        }
        else
        {
            return view('user.pages.package.list', compact('logs', 'startdate', 'enddate'));
        }
    }
}
