<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\Group;
use App\Models\Organization;
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
use Carbon\Carbon;

class RewardJob implements ShouldQueue
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
            RankReward::dispatch($this->purchase);
            RecommendReward::dispatch($this->purchase);
            BinaryReward::dispatch($this->purchase);
            //LeverageReward::dispatch($this->purchase);
        }

        $pur = Purchase::find($this->purchase->id);
        $pur->reward = 1;
        $pur->save();
    }
}
