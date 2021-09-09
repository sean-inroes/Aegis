<?php

namespace App\Http\Controllers\Admin\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GroupDataController;
use App\Http\Controllers\RewardController;
use App\Models\AosSetting;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseActiveLog;
use App\Models\User;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    //
    public function index(Request $request)
    {
        $purchases = Purchase::orderBy('created_at', 'desc')->paginate(20);
        return view('manage.pages.purchase.index', compact('purchases'));
    }

    public function show($id)
    {
        $purchase = Purchase::find($id);
        $logs = UserLogPoint::where('purchase_id', $id)->orderBy('id', 'asc')->get();

        $myreferers = User::where('group_id', $purchase->user->id)->get();

        return view('manage.pages.purchase.show', compact('purchase', 'logs', 'myreferers'));
    }

    public function create()
    {
        return view('manage.pages.package.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric',
        ], [
            'price.required' => "가격은 필수 항목입니다.",
            'price.numeric' => "가격은 숫자로 입력해주십시오."
        ]);

        $inputs = $request->all();

        $inputs['status'] = 1;

        return;

        $package = Package::create($inputs);

        return redirect()->route('admin.package.index');
    }

    public function edit($id)
    {
        $purchase = Purchase::find($id);

        if($purchase->status == 0)
        {
            $user = User::find($purchase->user_id);

            $target_amount = $purchase->paid / 100 * 85;
            $target_aos = $purchase->paid / 100 * 15;

            $aos = AosSetting::first();

            $target_aos = ceil($target_aos / $aos->price * 100) / 100;

            $package = Package::find($purchase->package_id);

            if($package == null)
            {
                return redirect()->back()->with('error', '올바르지 않은 패키지입니다.');
            }

            $group = Group::find($user->group_id);
            $group->revenue = $group->revenue + $purchase->paid;
            $group->save();

            $org = Organization::where('user_id', $user->id)->first();
            $org->revenue = $org->revenue + $purchase->paid;
            $org->save();

            $purchase->status = 1;
            $purchase->save();

            PurchaseActiveLog::create([
                'user_id' => $user->id,
                'purchase_id' => $purchase->id
            ]);

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

            $reward = new RewardController($purchase);
            $reward->start();
        }

        return redirect()->route('admin.purchase.show', ['purchase' => $id]);
    }

    public function update()
    {

    }

    public function destory()
    {

    }

    public function logdetail($id)
    {
        $purchase = Purchase::find($id);
        $users = GroupDataController::searchItemUpline($purchase);

        return $users;
    }
}
