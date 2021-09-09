<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organization;
use App\Models\Purchase;
use App\Models\TestValue;
use App\Models\UnpaidReward;
use App\Models\User;
use App\Models\UserLogBp;
use App\Models\UserLogBpDetails;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RewardController extends Controller
{
    protected $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function start()
    {
        $user = User::find($this->purchase->user_id);
        $user_group = Group::find($user->group_id);

        $referer = User::where('group_id', $user_group->parent_id)->first();

        $unpaids = UnpaidReward::where('user_id', $user->id)->where('status', 0)->get();

        foreach($unpaids as $unpaid)
        {
            if($this->purchase->package->price > $unpaid->paid)
            {
                if($this->purchase->package->price >= $unpaid->price)
                {
                    $target_price = $unpaid->unpaid;
                }
                else
                {
                    $target_price = $this->purchase->package->price - $unpaid->paid;
                }

                $amount = $target_price / 100 * $unpaid->fee;

                $ava_point = $user->point_1;

                $user->point_1 = $ava_point + $amount;
                $user->save();

                $type = "추천보너스";

                if($unpaid->type == 4)
                {
                    $type = "레벨업보너스";
                }

                UserLogPoint::create([
                    'user_id' => $user->id,
                    'purchase_id' => $this->purchase->id,
                    'before' => $ava_point,
                    'amount' => $amount,
                    'after' => $ava_point + $amount,
                    'log' => "{$user->username} 님이 패키지 구매로 주문번호 #{$unpaid->purchase_id} 의 미지급 {$type} $ {$amount} 을 적립했습니다. ",
                    'type' => $unpaid->type,
                    'status' => 1
                ]);

                if($this->purchase->package->price >= $unpaid->price)
                {
                    $unpaid->status = 1;
                }
                else
                {
                    $unpaid->paid = $this->purchase->package->price;
                    $unpaid->unpaid = $unpaid->price - $this->purchase->package->price;
                }

                $unpaid->save();
            }
        }

        if($referer != null)
        {
            $this->rank();
            $this->recommend();
            $this->binary();
            /*
            RankReward::dispatch($this->purchase);
            RecommendReward::dispatch($this->purchase);
            BinaryReward::dispatch($this->purchase);
            */
            //LeverageReward::dispatch($this->purchase);
        }

        $pur = Purchase::find($this->purchase->id);
        $pur->reward = 1;
        $pur->save();
    }

    private function rank()
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

                    $org_sum = $left_team['revenue'] + $right_team['revenue'] + $referer_group->revenue;

                    $bool = false;
                    if($left_team['num'] >= 1 && $right_team['num'] >= 1 && ($left_team['num'] + $right_team['num'] >= 3))
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

                    $org_sum = $left_team['revenue'] + $right_team['revenue'] + $referer_group->revenue;

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

                    $org_sum = $left_team['revenue'] + $right_team['revenue'] + $referer_group->revenue;

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

                    $org_sum = $left_team['revenue'] + $right_team['revenue'] + $referer_group->revenue;

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

                    $org_sum = $left_team['revenue'] + $right_team['revenue'] + $referer_group->revenue;

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

    private function recommend()
    {
        $user = User::find($this->purchase->user_id);
        $user_group = Group::find($user->group_id);
        $user_purchases = Purchase::where('user_id', $user->id)->where('status', 1)->where('id', '<>', $this->purchase->id)->orderBy('id', 'desc')->first();

        $referer = User::where('group_id', $user_group->parent_id)->first();
        $referer_purchases = Purchase::where('user_id', $referer->id)->where('status', 1)->orderBy('id', 'desc')->first();

        //구매가
        $recommend_std = $referer_purchases->package->price;
        $recommend_fee = $user_group->fee;
        $recommend_available = $referer->point_1;

        if($this->purchase->package->price > $recommend_std)
        {
            if($user_purchases == null)
            {
                $recommend_amount = $recommend_std / 100 * $recommend_fee;
            }
            else
            {
                $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
            }

            UnpaidReward::create([
                'purchase_id' => $this->purchase->id,
                'user_id' => $referer->id,
                'price' => $this->purchase->package->price,
                'paid' => $recommend_std,
                'unpaid' => $this->purchase->package->price - $recommend_std,
                'fee' => $recommend_fee,
                'type' => 3,
                'status' => 0
            ]);
        }
        else
        {
            $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
        }

        if($recommend_amount > 0)
        {
            $referer->point_1 = $recommend_available + $recommend_amount;
            $referer->save();

            UserLogPoint::create([
                'user_id' => $referer->id,
                'purchase_id' => $this->purchase->id,
                'before' => $recommend_available,
                'amount' => $recommend_amount,
                'after' => $recommend_available + $recommend_amount,
                'log' => "{$user->username} 님의 패키지 구매로 {$referer->username} 님이 추천보너스 $ $recommend_amount 을 적립했습니다. ",
                'type' => 3,
                'status' => 1
            ]);
        }

        $group_id = $user->group_id;
        $group_arr = [];
        while(true)
        {
            $recommend_group = Group::find($group_id);

            if($recommend_group->type == 0)
            {
                break;
            }
            else
            {
                array_push($group_arr, $recommend_group->type);
            }

            if(count($group_arr) < 2)
            {
                $group_id = $recommend_group->parent_id;
                if($group_id == 1)
                {
                    break;
                }
                else
                {
                    continue;
                }
            }

            $bool = false;

            for($i = 0 ; $i < count($group_arr) ; $i++)
            {
                $group_item = $group_arr[$i];

                if($i == (count($group_arr) - 1))
                {
                    if($group_item == 2)
                    {
                        $bool = true;
                    }
                    else
                    {
                        $bool = false;
                    }
                }
                else
                {
                    if($group_item == 1)
                    {
                        $bool = true;
                    }
                    else
                    {
                        $bool = false;
                        break;
                    }
                }
            }

            TestValue::create([
                'log' => json_encode($group_arr)
            ]);

            if($bool)
            {
                $gruop_group = Group::find($recommend_group->parent_id);
                $group_user = User::where('group_id', $gruop_group->id)->first();

                $referer_purchases = Purchase::where('user_id', $group_user->id)->where('status', 1)->orderBy('id', 'desc')->first();
                $recommend_std = $referer_purchases->package->price;
                $recommend_fee = 5;
                $recommend_available = $group_user->point_1;

                if($group_user->level == 2)
                {
                    if(count($group_arr) <= 10)
                    {
                        if($this->purchase->package->price > $recommend_std)
                        {
                            if($user_purchases == null)
                            {
                                $recommend_amount = $recommend_std / 100 * $recommend_fee;
                            }
                            else
                            {
                                $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
                            }

                            UnpaidReward::create([
                                'purchase_id' => $this->purchase->id,
                                'user_id' => $group_user->id,
                                'price' => $this->purchase->package->price,
                                'paid' => $recommend_std,
                                'unpaid' => $this->purchase->package->price - $recommend_std,
                                'fee' => $recommend_fee,
                                'type' => 4,
                                'status' => 0
                            ]);
                        }
                        else
                        {
                            $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
                        }

                        if($recommend_amount > 0)
                        {
                            $group_user->point_1 = $recommend_available + $recommend_amount;
                            $group_user->save();

                            UserLogPoint::create([
                                'user_id' => $group_user->id,
                                'purchase_id' => $this->purchase->id,
                                'before' => $recommend_available,
                                'amount' => $recommend_amount,
                                'after' => $recommend_available + $recommend_amount,
                                'log' => "{$user->username} 님의 패키지 구매로 {$group_user->username} 님이 레벨업보너스 $ $recommend_amount 을 적립했습니다. ",
                                'type' => 4,
                                'status' => 1
                            ]);
                        }
                    }
                }
                elseif($group_user->level == 3)
                {
                    if(count($group_arr) <= 15)
                    {
                        if($this->purchase->package->price > $recommend_std)
                        {
                            if($user_purchases == null)
                            {
                                $recommend_amount = $recommend_std / 100 * $recommend_fee;
                            }
                            else
                            {
                                $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
                            }

                            UnpaidReward::create([
                                'purchase_id' => $this->purchase->id,
                                'user_id' => $group_user->id,
                                'price' => $this->purchase->package->price,
                                'paid' => $recommend_std,
                                'unpaid' => $this->purchase->package->price - $recommend_std,
                                'fee' => $recommend_fee,
                                'type' => 4,
                                'status' => 0
                            ]);
                        }
                        else
                        {
                            $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
                        }

                        if($recommend_amount > 0)
                        {
                            $group_user->point_1 = $recommend_available + $recommend_amount;
                            $group_user->save();

                            UserLogPoint::create([
                                'user_id' => $group_user->id,
                                'purchase_id' => $this->purchase->id,
                                'before' => $recommend_available,
                                'amount' => $recommend_amount,
                                'after' => $recommend_available + $recommend_amount,
                                'log' => "{$user->username} 님의 패키지 구매로 {$group_user->username} 님이 레벨업보너스 $ $recommend_amount 을 적립했습니다. ",
                                'type' => 4,
                                'status' => 1
                            ]);
                        }
                    }
                }
                elseif($group_user->level == 4)
                {
                    if(count($group_arr) <= 20)
                    {
                        if($this->purchase->package->price > $recommend_std)
                        {
                            if($user_purchases == null)
                            {
                                $recommend_amount = $recommend_std / 100 * $recommend_fee;
                            }
                            else
                            {
                                $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
                            }

                            UnpaidReward::create([
                                'purchase_id' => $this->purchase->id,
                                'user_id' => $group_user->id,
                                'price' => $this->purchase->package->price,
                                'paid' => $recommend_std,
                                'unpaid' => $this->purchase->package->price - $recommend_std,
                                'fee' => $recommend_fee,
                                'type' => 4,
                                'status' => 0
                            ]);
                        }
                        else
                        {
                            $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
                        }

                        if($recommend_amount > 0)
                        {
                            $group_user->point_1 = $recommend_available + $recommend_amount;
                            $group_user->save();

                            UserLogPoint::create([
                                'user_id' => $group_user->id,
                                'purchase_id' => $this->purchase->id,
                                'before' => $recommend_available,
                                'amount' => $recommend_amount,
                                'after' => $recommend_available + $recommend_amount,
                                'log' => "{$user->username} 님의 패키지 구매로 {$group_user->username} 님이 레벨업보너스 $ $recommend_amount 을 적립했습니다. ",
                                'type' => 4,
                                'status' => 1
                            ]);
                        }
                    }
                }
                elseif($group_user->level == 5)
                {
                    if(count($group_arr) <= 30)
                    {
                        if($this->purchase->package->price > $recommend_std)
                        {
                            if($user_purchases == null)
                            {
                                $recommend_amount = $recommend_std / 100 * $recommend_fee;
                            }
                            else
                            {
                                $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
                            }

                            UnpaidReward::create([
                                'purchase_id' => $this->purchase->id,
                                'user_id' => $group_user->id,
                                'price' => $this->purchase->package->price,
                                'paid' => $recommend_std,
                                'unpaid' => $this->purchase->package->price - $recommend_std,
                                'fee' => $recommend_fee,
                                'type' => 4,
                                'status' => 0
                            ]);
                        }
                        else
                        {
                            $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
                        }

                        if($recommend_amount > 0)
                        {
                            $group_user->point_1 = $recommend_available + $recommend_amount;
                            $group_user->save();

                            UserLogPoint::create([
                                'user_id' => $group_user->id,
                                'purchase_id' => $this->purchase->id,
                                'before' => $recommend_available,
                                'amount' => $recommend_amount,
                                'after' => $recommend_available + $recommend_amount,
                                'log' => "{$user->username} 님의 패키지 구매로 {$group_user->username} 님이 레벨업보너스 $ $recommend_amount 을 적립했습니다. ",
                                'type' => 4,
                                'status' => 1
                            ]);
                        }
                    }
                }
                elseif($group_user->level >= 6)
                {
                    if($this->purchase->package->price > $recommend_std)
                    {
                        if($user_purchases == null)
                        {
                            $recommend_amount = $recommend_std / 100 * $recommend_fee;
                        }
                        else
                        {
                            $recommend_amount = ($recommend_std - $user_purchases->package->price) / 100 * $recommend_fee;
                        }

                        UnpaidReward::create([
                            'purchase_id' => $this->purchase->id,
                            'user_id' => $group_user->id,
                            'price' => $this->purchase->package->price,
                            'paid' => $recommend_std,
                            'unpaid' => $this->purchase->package->price - $recommend_std,
                            'fee' => $recommend_fee,
                            'type' => 4,
                            'status' => 0
                        ]);
                    }
                    else
                    {
                        $recommend_amount = $this->purchase->paid / 100 * $recommend_fee;
                    }

                    if($recommend_amount > 0)
                    {
                        $group_user->point_1 = $recommend_available + $recommend_amount;
                        $group_user->save();

                        UserLogPoint::create([
                            'user_id' => $group_user->id,
                            'purchase_id' => $this->purchase->id,
                            'before' => $recommend_available,
                            'amount' => $recommend_amount,
                            'after' => $recommend_available + $recommend_amount,
                            'log' => "{$user->username} 님의 패키지 구매로 {$group_user->username} 님이 레벨업보너스 $ $recommend_amount 을 적립했습니다. ",
                            'type' => 4,
                            'status' => 1
                        ]);
                    }
                }
            }

            $group_id = $recommend_group->parent_id;
        }
    }

    private function binary()
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

            $timestamp = Carbon::today('Asia/Seoul')->timestamp;
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

            if(in_array($this->purchase->user_id, $left_queue))
            {
                $left_bp->bp = $left_bp->bp + $this->purchase->bp;
                $left_bp->save();
            }

            if(in_array($this->purchase->user_id, $right_queue))
            {
                $right_bp->bp = $right_bp->bp + $this->purchase->bp;
                $right_bp->save();
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
                                if($parent_user->level == 1)
                                {
                                    if($j < 1)
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
                                elseif($parent_user->level == 2)
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
