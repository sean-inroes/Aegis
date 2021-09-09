<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
use App\Models\TestValue;
use App\Models\User;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RankReward implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $purchase;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->purchase->user_id);
        $user_group = Group::find($user->group_id);
        $group_id = $user_group->parent_id;

        $whilebool = true;
        while($whilebool)
        {
            $referer = User::where('group_id', $group_id)->first();
            $referer_group = Group::find($referer->group_id);

            if($referer_group->parent_id == null)
            {
                $whilebool = false;
            }
            else
            {
                $group_id = $referer_group->parent_id;
            }

            switch($referer->level)
            {
                case 2: //2 스타 승급
                    $left_team = $this->leftRevenue($referer->group_id, 2);
                    $right_team = $this->rightRevenue($referer->group_id, 2);

                    $org_sum = $left_team['revenue'] + $right_team['revenue'];

                    $bool = false;
                    if(($left_team['num'] + $right_team['num']) >= 3)
                    {
                        $bool = true;
                    }

                    if($org_sum >= 12000 && $bool)
                    {
                        $referer->level = 3;

                        UserLogLevel::create([
                            'user_id' => $referer->id,
                            'level' => 3
                        ]);

                        $ref_purchase = Purchase::where('user_id', $referer->id)->where('status', 1)->orderby('id', 'desc')->first();
                        if($ref_purchase != null)
                        {
                            if($ref_purchase->package->price >= 1000)
                            {
                                $ref_amount = $referer->point_1;
                                $referer->point_1 = $referer->point_1 + 100;
                                $buyer = $this->purchase->user->username;

                                UserLogPoint::create([
                                    'user_id' => $referer->id,
                                    'purchase_id' => $this->purchase->id,
                                    'before' => $ref_amount,
                                    'amount' => 100,
                                    'after' => $ref_amount + 100,
                                    'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 매니저 직급 보너스 $ 100을 적립했습니다. ",
                                    'type' => 2,
                                    'status' => 1
                                ]);
                            }
                        }

                        $referer->save();
                    }
                    break;

                case 3: //3 스타 승급
                    $left_team = $this->leftRevenue($referer->group_id, 3);
                    $right_team = $this->rightRevenue($referer->group_id, 3);

                    $org_sum = $left_team['revenue'] + $right_team['revenue'];

                    $bool = false;
                    if($left_team['num'] >= 1 && $right_team['num'] >= 1 && ($left_team['num'] + $right_team['num'] >= 3))
                    {
                        $bool = true;
                    }

                    if($org_sum >= 60000 && $bool)
                    {
                        $referer->level = 4;

                        UserLogLevel::create([
                            'user_id' => $referer->id,
                            'level' => 4
                        ]);

                        $ref_purchase = Purchase::where('user_id', $referer->id)->where('status', 1)->orderby('id', 'desc')->first();
                        if($ref_purchase != null)
                        {
                            if($ref_purchase->package->price >= 1000)
                            {
                                $ref_amount = $referer->point_1;
                                $referer->point_1 = $referer->point_1 + 250;
                                $buyer = $this->purchase->user->username;

                                UserLogPoint::create([
                                    'user_id' => $referer->id,
                                    'purchase_id' => $this->purchase->id,
                                    'before' => $ref_amount,
                                    'amount' => 250,
                                    'after' => $ref_amount + 250,
                                    'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 디렉터 직급 보너스 $ 250을 적립했습니다. ",
                                    'type' => 2,
                                    'status' => 1
                                ]);
                            }
                        }

                        $referer->save();
                    }
                    break;

                case 4: //4 스타 승급
                    $left_team = $this->leftRevenue($referer->group_id, 4);
                    $right_team = $this->rightRevenue($referer->group_id, 4);

                    $org_sum = $left_team['revenue'] + $right_team['revenue'];

                    $bool = false;
                    if($left_team['num'] >= 1 && $right_team['num'] >= 1 && ($left_team['num'] + $right_team['num'] >= 3))
                    {
                        $bool = true;
                    }

                    if($org_sum >= 300000 && $bool)
                    {
                        $referer->level = 5;

                        UserLogLevel::create([
                            'user_id' => $referer->id,
                            'level' => 5
                        ]);

                        $ref_purchase = Purchase::where('user_id', $referer->id)->where('status', 1)->orderby('id', 'desc')->first();
                        if($ref_purchase != null)
                        {
                            if($ref_purchase->package->price >= 5000)
                            {
                                $ref_amount = $referer->point_1;
                                $referer->point_1 = $referer->point_1 + 3500;
                                $buyer = $this->purchase->user->username;

                                UserLogPoint::create([
                                    'user_id' => $referer->id,
                                    'purchase_id' => $this->purchase->id,
                                    'before' => $ref_amount,
                                    'amount' => 3500,
                                    'after' => $ref_amount + 3500,
                                    'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 어시스터 직급 보너스 $ 3500을 적립했습니다. ",
                                    'type' => 2,
                                    'status' => 1
                                ]);
                            }
                        }

                        $referer->save();
                    }
                    break;

                case 5: //5 스타 승급
                    $left_team = $this->leftRevenue($referer->group_id, 5);
                    $right_team = $this->rightRevenue($referer->group_id, 5);

                    $org_sum = $left_team['revenue'] + $right_team['revenue'];

                    $bool = false;
                    if($left_team['num'] >= 1 && $right_team['num'] >= 1 && ($left_team['num'] + $right_team['num'] >= 3))
                    {
                        $bool = true;
                    }

                    if($org_sum >= 2400000 && $bool)
                    {
                        $referer->level = 6;

                        UserLogLevel::create([
                            'user_id' => $referer->id,
                            'level' => 6
                        ]);

                        $ref_purchase = Purchase::where('user_id', $referer->id)->where('status', 1)->orderby('id', 'desc')->first();
                        if($ref_purchase != null)
                        {
                            if($ref_purchase->package->price >= 8000)
                            {
                                $ref_amount = $referer->point_1;
                                $referer->point_1 = $referer->point_1 + 50000;
                                $buyer = $this->purchase->user->username;

                                UserLogPoint::create([
                                    'user_id' => $referer->id,
                                    'purchase_id' => $this->purchase->id,
                                    'before' => $ref_amount,
                                    'amount' => 50000,
                                    'after' => $ref_amount + 50000,
                                    'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 캡틴 직급 보너스 $ 50000을 적립했습니다. ",
                                    'type' => 2,
                                    'status' => 1
                                ]);
                            }
                        }

                        $referer->save();
                    }
                    break;

                case 6: //6 스타 승급
                    $left_team = $this->leftRevenue($referer->group_id, 6);
                    $right_team = $this->rightRevenue($referer->group_id, 6);

                    $org_sum = $left_team['revenue'] + $right_team['revenue'];

                    $bool = false;
                    if($left_team['num'] >= 1 && $right_team['num'] >= 1 && ($left_team['num'] + $right_team['num'] >= 3))
                    {
                        $bool = true;
                    }

                    if($org_sum >= 12000000 && $bool)
                    {
                        $referer->level = 7;

                        UserLogLevel::create([
                            'user_id' => $referer->id,
                            'level' => 7
                        ]);

                        $ref_purchase = Purchase::where('user_id', $referer->id)->where('status', 1)->orderby('id', 'desc')->first();
                        if($ref_purchase != null)
                        {
                            if($ref_purchase->package->price >= 24000)
                            {
                                $ref_amount = $referer->point_1;
                                $referer->point_1 = $referer->point_1 + 100000;
                                $buyer = $this->purchase->user->username;

                                UserLogPoint::create([
                                    'user_id' => $referer->id,
                                    'purchase_id' => $this->purchase->id,
                                    'before' => $ref_amount,
                                    'amount' => 100000,
                                    'after' => $ref_amount + 100000,
                                    'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 마스터 직급 보너스 $ 100000을 적립했습니다. ",
                                    'type' => 2,
                                    'status' => 1
                                ]);
                            }
                        }

                        $referer->save();
                    }
                    break;
            }
        }
    }

    private function leftRevenue($group_id, $level)
    {
        $leftteams = User::select('a.*', 'b.id as index', 'b.revenue')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', $group_id)->where('b.type', 1)->get();
        $allteams = User::select('a.*', 'b.id as index', 'b.parent_id', 'b.revenue')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->get();
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

            array_push($team_queue, $leftteam->index);
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
                    array_push($team_queue, $allteam->index);
                }
            }
        }

        return $boolean;
    }

    private function rightRevenue($group_id, $level)
    {
        $leftteams = User::select('a.*', 'b.id as index', 'b.revenue')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', $group_id)->where('b.type', 2)->get();
        $allteams = User::select('a.*', 'b.id as index', 'b.parent_id', 'b.revenue')->from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->get();
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

            array_push($team_queue, $leftteam->index);
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
                    array_push($team_queue, $allteam->index);
                }
            }
        }

        return $boolean;
    }
}
