<?php

namespace App\Http\Controllers\User\Pay;

use App\Http\Controllers\Controller;
use App\Models\UserLogPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class RouteController extends Controller
{
    protected $list = ['bonus', 'mining'];

    public function list($type, Request $request)
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

        if(in_array($type, $this->list))
        {
            if($type == "bonus")
            {
                $logs = UserLogPoint::where('user_id', Auth::id())
                    ->whereIn('type', [2, 3, 4, 5, 6, 7])
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->orderByDesc('id')
                    ->paginate(20);
            }
            else
            {
                $logs = UserLogPoint::where('user_id', Auth::id())
                    ->whereIn('type', [9])
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->orderByDesc('id')
                    ->paginate(20);
            }

            $agent = new Agent();

            if($agent->isMobile())
            {
                return view('user.pages.pay.mobile-list', compact('logs', 'type', 'startdate', 'enddate'));
            }
            else
            {
                return view('user.pages.pay.list', compact('logs', 'type', 'startdate', 'enddate'));
            }
        }
        else
        {
            return abort(404);
        }
    }
}
