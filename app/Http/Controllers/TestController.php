<?php

namespace App\Http\Controllers;

use App\Jobs\RewardJob;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseDeposit;
use App\Models\UserLogPoint;
use App\Models\Group;
use App\Models\GroupJoin;
use App\Models\GroupLog;
use App\Models\Board;
use App\Models\EthereumSetting;
use App\Models\EthereumWallet;
use App\Models\EthereumWalletTx;
use App\Models\EtherscanTx;
use App\Models\User;
use App\Models\Transaction;
use GuzzleHttp\Client;
use App\Models\UserLogJoin;
use Illuminate\Support\Str;
use EOSPHP\EOSClient;
use App\Models\AosSetting;
use App\Models\UserLogBp;
use App\Models\UserLogLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class TestController extends Controller
{
    public function test($id)
    {
        $left = $this->leftRevenue($id, 0);
        $right = $this->rightRevenue($id, 0);

        var_dump($left);
        echo "<br>";
        var_dump($right);
    }

    private function leftRevenue($group_id, $level)
    {
        $leftteams = User::select('a.username', 'a.level', 'b.*')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', $group_id)->where('b.type', 1)->get();
        $allteams = User::select('a.username', 'a.level', 'b.*')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->get();
        $team_queue = [];

        $boolean['num'] = 0;
        $boolean['revenue'] = 0;

        foreach($leftteams as $leftteam)
        {
            $boolean['revenue'] += $leftteam->revenue;

            if($leftteam->level >= $level)
            {
                $boolean['num']++;
            }
            array_push($team_queue, $leftteam->id);
        }

        for($i = 0 ; $i < count($team_queue) ; $i++)
        {
            $index = $team_queue[$i];

            foreach($allteams as $allteam)
            {
                if($allteam->parent_id == $index)
                {
                    $boolean['revenue'] += $allteam->revenue;

                    if($allteam->level >= $level)
                    {
                        $boolean['num']++;
                    }
                    array_push($team_queue, $allteam->id);
                }
            }
        }

        return $boolean;
    }

    private function rightRevenue($group_id, $level)
    {
        $leftteams = User::select('a.username', 'a.level', 'b.*')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', $group_id)->where('b.type', 2)->get();
        $allteams = User::select('a.username', 'a.level', 'b.*')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->get();
        $team_queue = [];

        $boolean['num'] = 0;
        $boolean['revenue'] = 0;

        foreach($leftteams as $leftteam)
        {
            $boolean['revenue'] += $leftteam->revenue;

            if($leftteam->level >= $level)
            {
                $boolean['num']++;
            }

            array_push($team_queue, $leftteam->id);
        }

        for($i = 0 ; $i < count($team_queue) ; $i++)
        {
            $index = $team_queue[$i];

            foreach($allteams as $allteam)
            {
                if($allteam->parent_id == $index)
                {
                    $boolean['revenue'] += $allteam->revenue;;

                    if($allteam->level >= $level)
                    {
                        $boolean['num']++;
                    }
                    array_push($team_queue, $allteam->id);
                }
            }
        }

        return $boolean;
    }

    public function setlocale($locale)
    {
        //return $locale;
        //Session::put('locale', $locale);
        Session::put('applocale', $locale);

        return redirect()->route('user.dashboard.index');
    }

    public function gettest($run)
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

            if($run == 1)
            {
                $left_bp->bp = $left_bps;
                $left_bp->bigger = $left_bigger;
                $left_bp->save();

                $right_bp->bp = $right_bps;
                $right_bp->bigger = $right_bigger;
                $right_bp->save();
            }

            echo $user->username."의 왼쪽 매출은 ".$left_bp->bp." 오른쪽 매출은 ".$right_bp->bp;
            echo "<br>";
            echo $user->username."의 왼쪽 매출은 ".$left_revenue." 왼쪽 BP는 ".$left_bps."#".$left_bigger." 오른쪽 매출은 ".$right_revenue." 오른쪽 BP는 ".$right_bps."#".$right_bigger;
            echo "<br>";
            if($left_bp->bp != $left_bps || $right_bp->bp != $right_bps)
            {
                echo $user->username."의 BP가 다름!!";
                echo "<br>";
            }
        }
    }

    public function executePurchase(Purchase $purchase)
    {
        if($purchase->status == 0)
        {
            $target_amount = $purchase->paid / 100 * 85;
            $target_aos = $purchase->paid / 100 * 15;

            if($user->point_1 >= $target_amount && $user->point_3 >= $target_aos)
            {
                $package = Package::find($purchase->package_id);

                if($package == null)
                {
                    return redirect()->back()->with('error', '올바르지 않은 패키지입니다.');
                }

                $available = $user->point_1;
                $available_aos = $user->point_3;

                $user->point_1 = $user->point_1 - $target_amount;
                $user->point_2 = $user->point_2 + $package->bp;
                $user->point_3 = $user->point_3 - $target_aos;
                $user->level = 1;
                $user->save();

                UserLogPoint::create([
                    'user_id' => $user->id,
                    'purchase_id' => $purchase->id,
                    'before' => $available,
                    'amount' => $target_amount,
                    'after' => $user->point_1,
                    'log' => "{$user->username} 님이 패키지 구매로 {$target_amount} 차감되었습니다.",
                    'type' => 0,
                    'status' => 1
                ]);

                UserLogPoint::create([
                    'user_id' => $user->id,
                    'purchase_id' => $purchase->id,
                    'before' => 0,
                    'amount' => $package->bp,
                    'after' => $user->point_2,
                    'log' => "$user->username 님이 패키지 구매로 BP $package->bp 적립되었습니다.",
                    'type' => 1,
                    'status' => 1
                ]);

                UserLogPoint::create([
                    'user_id' => $user->id,
                    'purchase_id' => $purchase->id,
                    'before' => $available_aos,
                    'amount' => $target_aos,
                    'after' => $user->point_3,
                    'log' => "{$user->username} 님이 패키지 구매로 AOS $target_aos 차감되었습니다.",
                    'type' => 8,
                    'status' => 1
                ]);

                $group = Group::find($user->group_id);
                $group->revenue = $group->revenue + $purchase->paid;
                $group->save();

                $org = Organization::where('user_id', $user->id)->first();
                $org->revenue = $org->revenue + $purchase->paid;
                $org->save();

                $purchase->status = 1;
                $purchase->save();

                $referer = User::where('group_id', $group->parent_id)->first();

                if($referer != null)
                {
                    if($referer->level == 1)
                    {
                        $referer_group_count = Group::where('parent_id', $referer->group_id)->where('revenue', '<>', 0)->count();

                        if($referer_group_count >= 5)
                        {
                            $referer->level = 2;
                            $referer->save();

                            UserLogLevel::create([
                                'user_id' => $referer->id,
                                'level' => 2
                            ]);
                        }
                    }
                }

                //RewardController::start($purchase);
                RewardJob::dispatch($purchase);
            }
        }
    }

    private function hex2str($hex) {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
    }

    private function wei2eth($wei)
    {
        return bcdiv($wei,1000000000000000000,18);
    }

    private function bchexdec($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }

    public function generateUser($parent_id, $repetition)
    {
        $parent = User::find($parent_id);
        $parent_group = Group::find($parent->group_id);

        for($i = 0; $i < $repetition; $i++)
        {
            $upline_user = $parent;
            $upline_group = $parent_group;

            $grouporder = Group::orderBy('label', 'desc')->first();
            $member_group = Group::create([
                'label' => $grouporder->label + 1,
                'layer' => $parent_group->layer + 1,
                'member' => 1
            ]);

            $random = Str::random(10);

            $user = User::create([
                'username' => $random,
                'email' => "$random@dokyo-hot.net",
                'nickname' => '관리자',
                'name' => '이아무개',
                'password' => bcrypt("1234"),
                'referer_code' => $random,
                'group_id' => $member_group->id
            ]);

            GroupJoin::create([
                'group_id' => $member_group->id,
                'user_id' => $user->id
            ]);

            $count = UserLogJoin::where('recommend_id', $parent->id)->count();
            $groups = Group::where('parent_id', $parent->group_id)->orderBy('id', 'asc')->get();
            $create_group = false;

            if($count < 3)
            {
                if(count($groups) == 0)
                {
                    $group = Group::create([
                        'parent_id' => $parent_group->id,
                        'layer' => $parent_group->layer + 1,
                        'label' => $parent_group->label,
                        'member' => 1
                    ]);

                    $create_group = true;

                    GroupJoin::create([
                        'group_id' => $group->id,
                        'user_id' => $user->id
                    ]);
                }
                else
                {
                    $group = $groups[0];
                    $group->member = $group->member + 1;
                    $group->save();

                    GroupJoin::create([
                        'group_id' => $group->id,
                        'user_id' => $user->id
                    ]);
                }
            }
            else
            {
                if(count($groups) == 1)
                {
                    $group = Group::create([
                        'parent_id' => $parent_group->id,
                        'layer' => $parent_group->layer + 1,
                        'label' => $parent_group->label,
                        'member' => 1
                    ]);

                    $create_group = true;

                    GroupJoin::create([
                        'group_id' => $group->id,
                        'user_id' => $user->id
                    ]);
                }
                else
                {
                    $group = $groups[1];
                    $group->member = $group->member + 1;
                    $group->save();

                    GroupJoin::create([
                        'group_id' => $group->id,
                        'user_id' => $user->id
                    ]);
                }
            }

            UserLogJoin::create([
                'recommend_id' => $parent->id,
                'user_id' => $user->id
            ]);

            $whilebool = true;
            while($whilebool)
            {
                $userlog = UserLogJoin::where('user_id', $upline_user->id)->first();
                if($userlog == null)
                {
                    $whilebool = false;
                }
                else
                {
                    $upline_user = User::find($userlog->recommend_id);
                    $upline_group = Group::find($upline_user->group_id);

                    $groups = Group::where('label', $upline_group->label)
                        ->where('layer', $member_group->layer)
                        ->orderBy('id', 'asc')->get();

                    $groupjoins = GroupJoin::where('user_id', $parent->id)->get();
                    $upline_id = null;
                    foreach($groupjoins as $groupjoin)
                    {
                        $searchgroup = Group::find($groupjoin->group_id);
                        if($searchgroup->label == $upline_group->label)
                        {
                            $upline_id = $searchgroup;
                        }
                    }
                    echo "그룹 카운트 : ";
                    var_dump(count($groups));
                    echo "<br>";
                    if(count($groups) == 0)
                    {
                        $creategroup = Group::create([
                            'parent_id' => $upline_id->id,
                            'layer' => $member_group->layer,
                            'label' => $upline_group->label,
                            'member' => 1
                        ]);

                        GroupJoin::create([
                            'group_id' => $creategroup->id,
                            'user_id' => $user->id
                        ]);
                    }
                    else
                    {
                        foreach($groups as $key => $group)
                        {
                            echo "그룹 인덱스 : ";
                            var_dump($key);
                            echo "<br>";
                            if($key == 0 && $group->member >= 3)
                            {
                                if(count($groups) == 1)
                                {
                                    $creategroup = Group::create([
                                        'parent_id' => $upline_id->id,
                                        'layer' => $member_group->layer,
                                        'label' => $upline_group->label,
                                        'member' => 1
                                    ]);

                                    GroupJoin::create([
                                        'group_id' => $creategroup->id,
                                        'user_id' => $user->id
                                    ]);
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                $group->member = (int)$group->member + 1;
                                $group->save();

                                GroupJoin::create([
                                    'group_id' => $group->id,
                                    'user_id' => $user->id
                                ]);
                            }
                        }
                    }
                }

            }
        }
    }
}
