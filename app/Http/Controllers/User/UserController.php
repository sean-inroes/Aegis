<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RewardController;
use App\Jobs\RewardJob;
use App\Models\AosSetting;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseActiveLog;
use App\Models\User;
use App\Models\UserLogJoin;
use App\Models\UserLogPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function deposit(Request $request)
    {
        $inputs = $request->all();
        $user = User::find($inputs['id']);

        $user->point_1 = (int)$user->point_1 + (int)$inputs['deposit'];
        $user->save();

        return redirect()->back();
    }

    public function purchase(Request $request)
    {
        $inputs = $request->all();
        $package = Package::find($inputs['package_id']);
        $user = User::find($inputs['user_id']);

        $before_pur = Purchase::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        if($before_pur == null)
        {
            $amount = $package->price;
        }
        else
        {
            $amount = $package->price - $before_pur->package->price;
        }

        if($before_pur == null)
        {
            $amount_bp = $package->bp;
        }
        else
        {
            $amount_bp = $package->bp - $before_pur->package->bp;
        }

        $available = $user->point_1;
        $available_bp = $user->point_2;
        $available_aos = $user->point_3;

        $target_amount = $amount / 100 * 85;
        $target_aos = $amount / 100 * 15;

        $aos = AosSetting::first();

        $target_aos = ceil($target_aos / $aos->price * 100) / 100;

        if(env('APP_ENV') == 'production')
        {
            if($available < $target_amount || $available_aos < $target_aos)
            {
                return redirect()->back()->with('error', '사용 가능한 포인트가 모자랍니다.')->with('amount_tether', $target_amount)->with('amount_aos', $target_aos);
            }
        }

        if($user->level == 0)
        {
            $user->level = 1;
        }

        if(env('APP_ENV') == 'production')
        {
            $user->point_1 = $user->point_1 - $target_amount;
            $user->point_2 = $user->point_2 + $amount_bp;
            $user->point_3 = $user->point_3 - $target_aos;
            $user->save();
        }

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'paid' => $amount,
            'bp' => $amount_bp,
            'status' => 1
        ]);

        PurchaseActiveLog::create([
            'user_id' => $user->id,
            'purchase_id' => $purchase->id
        ]);

        $user_group = Group::find($user->group_id);
        $user_group->revenue = $user_group->revenue + $amount;
        $user_group->save();

        $org = Organization::where('user_id', $user->id)->first();
        $org->revenue = $org->revenue + $amount;
        $org->save();

        if(env('APP_ENV') == 'production')
        {
            UserLogPoint::create([
                'user_id' => $user->id,
                'purchase_id' => $purchase->id,
                'before' => $available,
                'amount' => $target_amount,
                'after' => $user->point_1,
                'log' => "$user->username 님이 패키지 구매로 $target_amount 차감되었습니다.",
                'type' => 0,
                'status' => 1
            ]);

            UserLogPoint::create([
                'user_id' => $user->id,
                'purchase_id' => $purchase->id,
                'before' => $available_bp,
                'amount' => $amount_bp,
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
                'log' => "$user->username 님이 패키지 구매로 AOS $target_aos 차감되었습니다.",
                'type' => 8,
                'status' => 1
            ]);
        }

        $reward = new RewardController($purchase);
        $reward->start();

        return redirect()->back()->with('success', '패키지를 구매하였습니다.');
    }
}
