<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
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

class LeverageReward implements ShouldQueue
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

        $referer = User::where('group_id', $user_group->parent_id)->first();

        $org_maching = Organization::where('user_id', $user->id)->first();
        $group_id = $org_maching->id;
        $group_arr = [];
        $next_group = false;
        while(true)
        {
            $recommend_group = Organization::find($group_id);
            $recommend_user = User::where('id', $recommend_group->user_id)->first();

            if($recommend_group->parent_id == null)
            {
                $next_group = true;
            }

            array_push($group_arr, $recommend_group->id);

            if($recommend_user->level == 3)
            {
                if(count($group_arr) == 1)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
                elseif(count($group_arr) == 2)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
                elseif(count($group_arr) == 3)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100 / 2;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
            }
            elseif($recommend_user->level == 4)
            {
                if(count($group_arr) == 1)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
                elseif(count($group_arr) == 2)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
                elseif(count($group_arr) == 3)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100 / 2;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
            }
            elseif($recommend_user->level == 5)
            {
                if(count($group_arr) == 1)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
                elseif(count($group_arr) == 2)
                {
                    $recommend_available = $referer->point_1;
                    $recommend_amount = $this->purchase->paid / 100;
                    $buyer = $user->username;

                    $referer->point_1 = $recommend_available + $recommend_amount;
                    $referer->save();

                    UserLogPoint::create([
                        'user_id' => $referer->id,
                        'purchase_id' => $this->purchase->id,
                        'before' => $recommend_available,
                        'amount' => $recommend_amount,
                        'after' => $recommend_available + $recommend_amount,
                        'log' => "$buyer 님의 패키지 구매로 {$referer->username} 님이 레버리지보너스 $ $recommend_amount 을 적립했습니다. ",
                        'type' => 7,
                        'status' => 1
                    ]);
                }
            }

            if($next_group)
            {
                break;
            }

            $group_id = $recommend_group->parent_id;
        }
    }
}
