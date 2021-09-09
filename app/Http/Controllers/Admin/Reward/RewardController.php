<?php

namespace App\Http\Controllers\Admin\Reward;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
use App\Models\TestValue;
use App\Models\User;
use App\Models\UserLogBp;
use App\Models\UserLogBpDetails;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use Illuminate\Support\Carbon;

class RewardController extends Controller
{
    //
    public function index(Request $request)
    {
        $rewards = Reward::orderByDesc('id')->paginate(20);

        return view('manage.pages.reward.index', compact('rewards'));
    }

    public function show($reward)
    {
        $rewards = Reward::orderByDesc('id')->paginate(20);

        return view('manage.pages.reward.show', compact('rewards'));
    }

    public function create()
    {
        $users = User::get();

        foreach($users as $foruser)
        {
            $user = User::find($foruser->id);
            $user_org = Organization::where('user_id', $user->id)->first();

            $org_left = Organization::where('parent_id', $user->id)->where('team', 0)->first();
            $org_right = Organization::where('parent_id', $user->id)->where('team', 1)->first();
            $orgs = Organization::get();

            $left_bp = UserLogBp::where('user_id', $user->id)->where('type', 0)->first();
            $right_bp = UserLogBp::where('user_id', $user->id)->where('type', 1)->first();

            if($left_bp == null)
            {
                $left_bp = UserLogBp::create([
                    'user_id' => $user->id,
                    'type' => 0,
                    'bp' => 0,
                    'bigger' => 0
                ]);
            }

            if($right_bp == null)
            {
                $right_bp = UserLogBp::create([
                    'user_id' => $user->id,
                    'type' => 1,
                    'bp' => 0,
                    'bigger' => 0
                ]);
            }

            $left_queue = [];
            $right_queue = [];

            $queue = [];
            if($org_left != null)
            {
                foreach($orgs as $org)
                {
                    if($org->user_id == $org_left->id)
                    {
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
                            array_push($queue, $org->user_id);
                        }
                    }
                }
            }

            $left_queue = $queue;

            $queue = [];
            if($org_right != null)
            {
                foreach($orgs as $org)
                {
                    if($org->user_id == $org_right->id)
                    {
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
                            array_push($queue, $org->user_id);
                        }
                    }
                }
            }

            $right_queue = $queue;

            $all_queue = array_merge($left_queue, $right_queue);
            $level_logs = UserLogLevel::where('user_id', $user->id)->get();

            $allpurchases = Purchase::whereIn('user_id', $all_queue)->orderby('updated_at', 'asc')->get();
            $left_revenue = 0;
            $left_bps = 0;
            $left_bigger = 0;
            $right_revenue = 0;
            $right_bps = 0;
            $right_bigger = 0;

            $count = 0;
            $previous_timestamp = null;

            foreach($allpurchases as $pur)
            {
                if($pur->status == 0) continue;
                $level = 0;
                foreach($level_logs as $level_log)
                {
                    if($pur->updated_at >= $level_log->created_at)
                    {
                        $level = $level_log->level;
                    }
                }

                if($level == 1)
                {
                    $cycle = 1;
                }
                elseif($level == 2)
                {
                    $cycle = 4;
                }
                elseif($level == 3)
                {
                    $cycle = 5;
                }
                elseif($level == 4)
                {
                    $cycle = 6;
                }
                elseif($level == 5)
                {
                    $cycle = 8;
                }
                elseif($level == 6)
                {
                    $cycle = 10;
                }
                elseif($level == 7)
                {
                    $cycle = 12;
                }
                else
                {
                    $cycle = 0;
                }

                if(in_array($pur->user_id, $left_queue))
                {
                    $left_revenue += $pur->paid;
                    $left_bps += $pur->bp;
                }
                else
                {
                    $right_revenue += $pur->paid;
                    $right_bps += $pur->bp;
                }

                $jungsanbool = true;

                $std_timestamp = Carbon::parse(Carbon::parse($pur->updated_at)->tz('Asia/Seoul')->format('Y-m-d 00:00:00'))->timestamp;
                //$std_timestamp_tomorrow = $std_timestamp + 86399;

                while($jungsanbool)
                {
                    if($previous_timestamp == null)
                    {
                        $previous_timestamp = $std_timestamp;
                    }

                    if($previous_timestamp != $std_timestamp)
                    {
                        $count = 0;
                        $previous_timestamp = $std_timestamp;
                    }

                    $logbinary = $count;

                    if($logbinary < $cycle)
                    {
                        if($left_bps >= 6 && $right_bps < 6)
                        {
                            $left_bigger = 1;

                            $right_bigger = 0;

                            $jungsanbool = false;
                        }
                        elseif($left_bps < 6 && $right_bps >= 6)
                        {
                            $left_bigger = 0;

                            $right_bigger = 1;

                            $jungsanbool = false;
                        }
                        elseif($left_bps < 6 && $right_bps < 6)
                        {

                            $left_bigger = 0;

                            $right_bigger = 0;

                            $jungsanbool = false;
                        }
                        else
                        {
                            $count++;
                            $left_bps -= 6;
                            $right_bps -= 6;
                        }
                    }
                    else
                    {
                        if($left_bigger == 1)
                        {
                            $right_bps = 0;
                        }

                        if($right_bigger == 1)
                        {
                            $left_bps = 0;
                        }

                        $jungsanbool = false;
                    }
                }
            }

            if($left_revenue == 0 && $right_revenue == 0) continue;

            echo $user->username."의 왼쪽 매출은 ".$left_revenue." 왼쪽 BP는 ".$left_bps." 오른쪽 매출은 ".$right_revenue." 오른쪽 BP는 ".$right_bps;
            echo "<br>";
        }
    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function destory()
    {

    }
}
