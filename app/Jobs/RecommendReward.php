<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
use App\Models\TestValue;
use App\Models\UnpaidReward;
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

class RecommendReward implements ShouldQueue
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
}
