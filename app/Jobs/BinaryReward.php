<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
use App\Models\TestValue;
use App\Models\User;
use App\Models\UserLogBp;
use App\Models\UserLogBpDetails;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class BinaryReward implements ShouldQueue
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
        $user_org = Organization::where('user_id', $user->id)->first();

        while(true)
        {
            if($user_org->parent_id == null)
            {
                break;
            }

            $referer = User::where('group_id', $user_org->parent_id)->first();
            $referer_group = Group::find($referer->group_id);

            $org_left = Organization::where('parent_id', $referer->id)->where('team', 0)->first();
            $org_right = Organization::where('parent_id', $referer->id)->where('team', 1)->first();
            $orgs = Organization::get();

            $left_bp = UserLogBp::where('user_id', $referer->id)->where('type', 0)->first();
            $right_bp = UserLogBp::where('user_id', $referer->id)->where('type', 1)->first();

            if($left_bp == null)
            {
                $left_bp = UserLogBp::create([
                    'user_id' => $referer->id,
                    'type' => 0,
                    'bp' => 0,
                    'bigger' => 0
                ]);
            }

            if($right_bp == null)
            {
                $right_bp = UserLogBp::create([
                    'user_id' => $referer->id,
                    'type' => 1,
                    'bp' => 0,
                    'bigger' => 0
                ]);
            }

            /*
            $now = Carbon::now()->timestamp;
            $std = 60 * 30;

            $b = $now;
            $a = $std;

            $quotient = ($b - ($b % $a)) / $a;

            $cut = $quotient * $std;

            $timestamp = $cut;
            */

            $timestamp = Carbon::today('Asia/Seoul')->timestamp;
            //$timestamp_before = Carbon::yesterday('Asia/Seoul')->timestamp;

            //$timestamp = Carbon::now('Asia/Seoul')->subMinutes(10)->timestamp;
            //$timestamp_before = Carbon::now('Asia/Seoul')->subMinutes(20)->timestamp;

            if($org_left != null)
            {
                $queue = [];
                foreach($orgs as $org)
                {
                    if($org->user_id == $org_left->id)
                    {
                        $org_purchases = Purchase::where('user_id', $org->user_id)->where('created_at', '>=', $timestamp)->get();

                        foreach($org_purchases as $org_purchase)
                        {
                            if($org_purchase->status == 1)
                            {
                                $count = UserLogBpDetails::where('user_id', $referer->id)->where('purchase_id', $org_purchase->id)->count();

                                if($count == 0)
                                {
                                    $left_bp->bp = $left_bp->bp + $org_purchase->bp;
                                    $left_bp->save();

                                    UserLogBpDetails::create([
                                        'user_id' => $referer->id,
                                        'purchase_id' => $org_purchase->id
                                    ]);
                                }
                            }
                        }

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
                            $org_purchases = Purchase::where('user_id', $org->user_id)->where('created_at', '>=', $timestamp)->get();

                            foreach($org_purchases as $org_purchase)
                            {
                                if($org_purchase->status == 1)
                                {
                                    $count = UserLogBpDetails::where('user_id', $referer->id)->where('purchase_id', $org_purchase->id)->count();

                                    if($count == 0)
                                    {
                                        $left_bp->bp = $left_bp->bp + $org_purchase->bp;
                                        $left_bp->save();

                                        UserLogBpDetails::create([
                                            'user_id' => $referer->id,
                                            'purchase_id' => $org_purchase->id
                                        ]);
                                    }
                                }
                            }
                            array_push($queue, $org->user_id);
                        }
                    }
                }
            }

            if($org_right != null)
            {
                $queue = [];
                foreach($orgs as $org)
                {
                    if($org->user_id == $org_right->id)
                    {
                        $org_purchases = Purchase::where('user_id', $org->user_id)->where('created_at', '>=', $timestamp)->get();

                        foreach($org_purchases as $org_purchase)
                        {
                            if($org_purchase->status == 1)
                            {
                                $count = UserLogBpDetails::where('user_id', $referer->id)->where('purchase_id', $org_purchase->id)->count();

                                if($count == 0)
                                {
                                    $right_bp->bp = $right_bp->bp + $org_purchase->bp;
                                    $right_bp->save();

                                    UserLogBpDetails::create([
                                        'user_id' => $referer->id,
                                        'purchase_id' => $org_purchase->id
                                    ]);
                                }
                            }
                        }

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
                            $org_purchases = Purchase::where('user_id', $org->user_id)->where('created_at', '>=', $timestamp)->get();

                            foreach($org_purchases as $org_purchase)
                            {
                                if($org_purchase->status == 1)
                                {
                                    $count = UserLogBpDetails::where('user_id', $referer->id)->where('purchase_id', $org_purchase->id)->count();

                                    if($count == 0)
                                    {
                                        $right_bp->bp = $right_bp->bp + $org_purchase->bp;
                                        $right_bp->save();

                                        UserLogBpDetails::create([
                                            'user_id' => $referer->id,
                                            'purchase_id' => $org_purchase->id
                                        ]);
                                    }
                                }
                            }

                            array_push($queue, $org->user_id);
                        }
                    }
                }
            }

            if($referer->level == 1)
            {
                $cycle = 1;
            }
            elseif($referer->level == 2)
            {
                $cycle = 4;
            }
            elseif($referer->level == 3)
            {
                $cycle = 5;
            }
            elseif($referer->level == 4)
            {
                $cycle = 6;
            }
            elseif($referer->level == 5)
            {
                $cycle = 8;
            }
            elseif($referer->level == 6)
            {
                $cycle = 10;
            }
            elseif($referer->level == 7)
            {
                $cycle = 12;
            }
            else
            {
                $cycle = 0;
            }

            $jungsanbool = true;

            while($jungsanbool)
            {
                $logbinary = UserLogPoint::where('user_id', $referer->id)->where('type', 5)->where('created_at', '>=', $timestamp)->count();

                TestValue::create([
                    'log' => "{$referer->username}의 바이너리 : {$logbinary}, 사이클 : {$cycle}, 왼쪽레그BP : {$left_bp->bp}, 오른쪽레그BP : {$right_bp->bp}"
                ]);

                if($logbinary < $cycle)
                {
                    if($left_bp->bp >= 6 && $right_bp->bp < 6)
                    {
                        TestValue::create([
                            'log' => "바이너리-1"
                        ]);

                        $left_bp->bigger = 1;
                        $left_bp->save();

                        $right_bp->bigger = 0;
                        $right_bp->save();

                        $jungsanbool = false;
                    }
                    elseif($left_bp->bp < 6 && $right_bp->bp >= 6)
                    {
                        TestValue::create([
                            'log' => "바이너리-2"
                        ]);

                        $left_bp->bigger = 0;
                        $left_bp->save();

                        $right_bp->bigger = 1;
                        $right_bp->save();

                        $jungsanbool = false;
                    }
                    elseif($left_bp->bp < 6 && $right_bp->bp < 6)
                    {
                        TestValue::create([
                            'log' => "바이너리-3"
                        ]);

                        $left_bp->bigger = 0;
                        $left_bp->save();

                        $right_bp->bigger = 0;
                        $right_bp->save();

                        $jungsanbool = false;
                    }
                    else
                    {
                        TestValue::create([
                            'log' => "바이너리-4"
                        ]);

                        $recommend_available = $referer->point_1;
                        $recommend_amount = 200;
                        $buyer = $user->username;

                        $referer->point_1 = $recommend_available + $recommend_amount;
                        $referer->save();

                        $left_bp->bp = $left_bp->bp - 6;
                        $left_bp->save();

                        $right_bp->bp = $right_bp->bp - 6;
                        $right_bp->save();

                        UserLogPoint::create([
                            'user_id' => $referer->id,
                            'purchase_id' => $this->purchase->id,
                            'before' => $recommend_available,
                            'amount' => $recommend_amount,
                            'after' => $recommend_available + $recommend_amount,
                            'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 후원보너스 $ $recommend_amount 을 적립했습니다. ",
                            'type' => 5,
                            'status' => 1
                        ]);

                        $referer_parents = Group::get();
                        $parents_queue = [];
                        foreach($referer_parents as $parent)
                        {
                            if($referer_group->parent_id == $parent->id)
                            {
                                array_push($parents_queue, $parent->id);
                            }
                        }

                        for($j = 0 ; $j < count($parents_queue) ; $j++)
                        {
                            $parent_index = $parents_queue[$j];

                            foreach($referer_parents as $parent)
                            {
                                if($parent_index == $parent->id)
                                {
                                    array_push($parents_queue, $parent->parent_id);
                                }
                            }

                            TestValue::create([
                                'log' => json_encode($parents_queue)
                            ]);

                            $parent_user = User::where('group_id', $parent_index)->first();

                            if($parent_user != null)
                            {
                                if($parent_user->level == 2)
                                {
                                    if($j < 2)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                                elseif($parent_user->level == 3)
                                {
                                    if($j < 4)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                                elseif($parent_user->level == 4)
                                {
                                    if($j < 6)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                                elseif($parent_user->level == 5)
                                {
                                    if($j < 8)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                                elseif($parent_user->level == 6)
                                {
                                    if($j < 9)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                                elseif($parent_user->level == 7)
                                {
                                    if($j < 10)
                                    {
                                        $recommend_available = $parent_user->point_1;
                                        $recommend_amount = 10;
                                        $buyer = $user->username;

                                        $parent_user->point_1 = $recommend_available + $recommend_amount;
                                        $parent_user->save();

                                        UserLogPoint::create([
                                            'user_id' => $parent_user->id,
                                            'purchase_id' => $this->purchase->id,
                                            'before' => $recommend_available,
                                            'amount' => $recommend_amount,
                                            'after' => $recommend_available + $recommend_amount,
                                            'log' => "$buyer 님의 패키지 구매로 {$parent_user->username} 님이 매칭보너스 $ $recommend_amount 을 적립했습니다. ",
                                            'type' => 6,
                                            'status' => 1
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    TestValue::create([
                        'log' => "바이너리-5"
                    ]);

                    if($left_bp->bigger == 1)
                    {
                        $right_bp->bp = 0;
                        $right_bp->save();
                    }

                    if($right_bp->bigger == 1)
                    {
                        $left_bp->bp = 0;
                        $left_bp->save();
                    }

                    $jungsanbool = false;
                }
            }

            $user_org = Organization::where('user_id', $referer->id)->first();
        }
    }
}
