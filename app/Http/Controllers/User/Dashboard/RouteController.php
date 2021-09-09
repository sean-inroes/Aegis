<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseActiveLog;
use App\Models\User;
use App\Models\UserLogBp;
use App\Models\UserLogPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class RouteController extends Controller
{
    public function test()
    {
        $packages = Package::where('status', 1)->get();
        $logs = UserLogPoint::where('user_id', Auth::id())->orderby('created_at', 'desc')->get();

        return view('test', compact('packages', 'logs'));
    }

    public function index()
    {
        $myteams = User::from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', Auth::user()->group_id)->get();

        $team_revenue = 0;
        foreach($myteams as $team)
        {
            $team_revenue += $team->revenue;
        }

        $level = null;
        switch(Auth::user()->level)
        {
            case 0:
                $level = "비회원";
                break;

            case 1:
                $level = "회원";
                break;

            case 2:
                $level = "1 STAR";
                break;

            case 3:
                $level = "2 STAR";
                break;

            case 4:
                $level = "3 STAR";
                break;

            case 5:
                $level = "4 STAR";
                break;

            case 6:
                $level = "5 STAR";
                break;

            case 7:
                $level = "6 STAR";
                break;
        }

        $arr = [];
        $queue = [];
        $orgs = Organization::orderBy('team', 'asc')->get();
        $org_left_team = Organization::where('parent_id', Auth::id())->where('team', 0)->first();
        $org_right_team = Organization::where('parent_id', Auth::id())->where('team', 1)->first();
        $org_left_sum = 0;
        $org_right_sum = 0;
        $org_left_revenue = 0;
        $org_right_revenue = 0;

        $timestamp = Carbon::today('Asia/Seoul')->timestamp;

        if($org_left_team != null)
        {
            foreach($orgs as $org)
            {
                if($org->user_id == $org_left_team->user_id)
                {
                    $org_left_sum++;
                    $org_left_revenue += $org->revenue;
                    array_push($queue, $org->user_id);
                }
            }

            for($i = 0 ; $i < count($queue) ; $i++)
            {
                $index = $queue[$i];

                foreach($orgs as $org)
                {
                    if($org->parent_id == $index)
                    {
                        $org_left_sum++;
                        $org_left_revenue += $org->revenue;
                        array_push($queue, $org->user_id);
                    }
                }
            }
        }

        $today_left_purchases = PurchaseActiveLog::whereIn('user_id', $queue)->where('created_at', '>=', $timestamp)->get();
        $today_left_purchase = 0;
        foreach($today_left_purchases as $today_pur)
        {
            $today_left_purchase += $today_pur->purchase->paid;
        }

        $arr = [];
        $queue = [];
        if($org_right_team != null)
        {
            foreach($orgs as $org)
            {
                if($org->user_id == $org_right_team->user_id)
                {
                    $org_right_sum++;
                    $org_right_revenue += $org->revenue;
                    array_push($queue, $org->user_id);
                }
            }

            for($i = 0 ; $i < count($queue) ; $i++)
            {
                $index = $queue[$i];

                foreach($orgs as $org)
                {
                    if($org->parent_id == $index)
                    {
                        $org_right_sum++;
                        $org_right_revenue += $org->revenue;
                        array_push($queue, $org->user_id);
                    }
                }
            }
        }

        $today_right_purchases = PurchaseActiveLog::whereIn('user_id', $queue)->where('created_at', '>=', $timestamp)->get();
        $today_right_purchase = 0;
        foreach($today_right_purchases as $today_pur)
        {
            $today_right_purchase += $today_pur->purchase->paid;
        }

        $point0 = 0;
        $point1 = 0;
        $point2 = 0;
        $point3 = 0;
        $point4 = 0;
        $point5 = 0;
        $point6 = 0;
        $point7 = 0;

        $today_timestamp = Carbon::today('Asia/Seoul')->timestamp;

        $logpoints = UserLogPoint::where('user_id', Auth::id())->where('created_at', '>=', $today_timestamp)->get();
        foreach($logpoints as $point)
        {
            if($point->type == 0)
            {
                $point0 += $point->amount;
            }
            elseif($point->type == 1)
            {
                $point1 += $point->amount;
            }
            elseif($point->type == 2)
            {
                $point2 += $point->amount;
            }
            elseif($point->type == 3)
            {
                $point3 += $point->amount;
            }
            elseif($point->type == 4)
            {
                $point4 += $point->amount;
            }
            elseif($point->type == 5)
            {
                $point5 += $point->amount;
            }
            elseif($point->type == 6)
            {
                $point6 += $point->amount;
            }
            elseif($point->type == 7)
            {
                $point7 += $point->amount;
            }
        }

        $bp_left = UserLogBp::where('user_id', Auth::id())->where('type', 0)->first();
        $bp_right = UserLogBp::where('user_id', Auth::id())->where('type', 1)->first();

        $agent = new Agent();
        if($agent->isMobile())
        {
            return view('user.pages.dashboard.mobile', compact('myteams', 'team_revenue', 'level', 'org_left_sum', 'org_right_sum', 'org_left_revenue', 'org_right_revenue', 'point0', 'point1', 'point2', 'point3', 'point4', 'point5', 'point6', 'point7', 'bp_left', 'bp_right', 'today_left_purchase', 'today_right_purchase'));
        }
        else
        {
            return view('user.pages.dashboard.index', compact('myteams', 'team_revenue', 'level', 'org_left_sum', 'org_right_sum', 'org_left_revenue', 'org_right_revenue', 'point0', 'point1', 'point2', 'point3', 'point4', 'point5', 'point6', 'point7', 'bp_left', 'bp_right', 'today_left_purchase', 'today_right_purchase'));
        }
    }

    public function test_page()
    {
        $myteams = User::from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', Auth::user()->group_id)->get();

        $team_revenue = 0;
        foreach($myteams as $team)
        {
            $team_revenue += $team->revenue;
        }

        $level = null;
        switch(Auth::user()->level)
        {
            case 0:
                $level = "비회원";
                break;

            case 1:
                $level = "회원";
                break;

            case 2:
                $level = "1 STAR";
                break;

            case 3:
                $level = "2 STAR";
                break;

            case 4:
                $level = "3 STAR";
                break;

            case 5:
                $level = "4 STAR";
                break;

            case 6:
                $level = "5 STAR";
                break;

            case 7:
                $level = "6 STAR";
                break;
        }

        $arr = [];
        $queue = [];
        $orgs = Organization::orderBy('team', 'asc')->get();
        $org_left_team = Organization::where('parent_id', Auth::id())->where('team', 0)->first();
        $org_right_team = Organization::where('parent_id', Auth::id())->where('team', 1)->first();
        $org_left_sum = 0;
        $org_right_sum = 0;
        $org_left_revenue = 0;
        $org_right_revenue = 0;

        if($org_left_team != null)
        {
            foreach($orgs as $org)
            {
                if($org->user_id == $org_left_team->user_id)
                {
                    $org_left_sum++;
                    $org_left_revenue += $org->revenue;
                    array_push($queue, $org->user_id);
                }
            }

            for($i = 0 ; $i < count($queue) ; $i++)
            {
                $index = $queue[$i];

                foreach($orgs as $org)
                {
                    if($org->parent_id == $index)
                    {
                        $org_left_sum++;
                        $org_left_revenue += $org->revenue;
                        array_push($queue, $org->user_id);
                    }
                }
            }
        }

        $arr = [];
        $queue = [];
        if($org_right_team != null)
        {
            foreach($orgs as $org)
            {
                if($org->user_id == $org_right_team->user_id)
                {
                    $org_right_sum++;
                    $org_right_revenue += $org->revenue;
                    array_push($queue, $org->user_id);
                }
            }

            for($i = 0 ; $i < count($queue) ; $i++)
            {
                $index = $queue[$i];

                foreach($orgs as $org)
                {
                    if($org->parent_id == $index)
                    {
                        $org_right_sum++;
                        $org_right_revenue += $org->revenue;
                        array_push($queue, $org->user_id);
                    }
                }
            }
        }

        $point0 = 0;
        $point1 = 0;
        $point2 = 0;
        $point3 = 0;
        $point4 = 0;
        $point5 = 0;
        $point6 = 0;
        $point7 = 0;

        $logpoints = UserLogPoint::where('user_id', Auth::id())->get();
        foreach($logpoints as $point)
        {
            if($point->type == 0)
            {
                $point0 += $point->amount;
            }
            elseif($point->type == 1)
            {
                $point1 += $point->amount;
            }
            elseif($point->type == 2)
            {
                $point2 += $point->amount;
            }
            elseif($point->type == 3)
            {
                $point3 += $point->amount;
            }
            elseif($point->type == 4)
            {
                $point4 += $point->amount;
            }
            elseif($point->type == 5)
            {
                $point5 += $point->amount;
            }
            elseif($point->type == 6)
            {
                $point6 += $point->amount;
            }
            elseif($point->type == 7)
            {
                $point7 += $point->amount;
            }
        }

        $bp_left = UserLogBp::where('user_id', Auth::id())->where('type', 0)->first();
        $bp_right = UserLogBp::where('user_id', Auth::id())->where('type', 1)->first();

        return view('user.pages.dashboard.test', compact('myteams', 'team_revenue', 'level', 'org_left_sum', 'org_right_sum', 'org_left_revenue', 'org_right_revenue', 'point0', 'point1', 'point2', 'point3', 'point4', 'point5', 'point6', 'point7', 'bp_left', 'bp_right'));
    }
}
