<?php

namespace App\Http\Controllers\User\Mypage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EthereumController;
use App\Http\Controllers\GroupDataController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\MailController;
use App\Jobs\RewardJob;
use App\Models\AosSetting;
use App\Models\EthereumWallet;
use App\Models\Group;
use App\Models\GroupJoin;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseActiveLog;
use App\Models\User;
use App\Models\UserLogJoin;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use App\Models\UserLogAuth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MypageController extends Controller
{
    public function myAccount()
    {
        $user = GroupDataController::searchItem(Auth::id());

        return view('user.pages.mypage.myAccount', compact('user'));
    }

    public function createAccount()
    {
        $packages = Package::get();

        $orgs = Organization::get();

        $queue = [];
        $available = [];
        foreach($orgs as $org)
        {
            if($org->user_id == Auth::id())
            {
                array_push($queue, $org->user_id);
                array_push($available, 0);
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
                    array_push($available, $org->parent_id);
                }
            }
        }

        $all_items = array_count_values($queue);
        $select_items = array_count_values($available);

        $result = [];
        $increase = 0;
        foreach($all_items as $index1 => $all_item)
        {
            $index = $index1;
            $num = 2;
            foreach($select_items as $index2 => $select_item)
            {
                if($index == $index2)
                {
                    $num = $num - $select_item;
                }
            }

            if($num == 1)
            {
                foreach($orgs as $org)
                {
                    if($org->id == $index)
                    {
                        $se_org = Organization::where('parent_id', $index)->first();
                        $increase++;
                        if($se_org->team == 0)
                        {
                            array_push($result, [
                                'id' => $increase,
                                'team' => 1,
                                'parent_id' => $index,
                                'username' => $org->user->username
                            ]);
                        }
                        else
                        {
                            array_push($result, [
                                'id' => $increase,
                                'team' => 0,
                                'parent_id' => $index,
                                'username' => $org->user->username
                            ]);
                        }
                    }
                }
            }
            elseif($num == 2)
            {
                foreach($orgs as $org)
                {
                    if($org->id == $index)
                    {
                        $increase++;
                        array_push($result, [
                            'id' => $increase,
                            'team' => 0,
                            'parent_id' => $index,
                            'username' => $org->user->username
                        ]);

                        $increase++;
                        array_push($result, [
                            'id' => $increase,
                            'team' => 1,
                            'parent_id' => $index,
                            'username' => $org->user->username
                        ]);
                    }
                }
            }
        }

        return view('user.pages.mypage.createAccount', compact('packages', 'result'));
    }

    public function requestTx(Request $request)
    {
        $my_purchase = Purchase::where('user_id', Auth::id())->where('status', 1)->orderby('id', 'desc')->first();

        if($my_purchase == null)
        {
            return redirect()->route('user.dashboard.package.buy')->with('error', '먼저 패키지를 구매해주십시오.');
        }

        $request->validate([
            "username" => "required|unique:users,username",
            "password" => "required|min:8",
            "confirm_password" => "required|same:password",
            "email" => "required|email",
            "auth_code" => "required",
            "phone" => "required",
            "package" => "required",
            "org" => "required",
            "parent_id" => "required",
            "team" => "required",
        ], [
            'required' => '필수 항목입니다.',
            'unique' => '중복된 회원명입니다.',
            'min' => '최소 8자리입니다.',
            'same' => '같지 않습니다.',
            'email' => '이메일 형식이 아닙니다.',
        ]);

        $referer = User::find(Auth::id());
        $referer_group = Group::find($referer->group_id);

        $package = Package::find($request->get('package'));

        if($package == null)
        {
            return redirect()->back()->with('error', '올바르지 않은 패키지입니다.');
        }

        $auth = UserLogAuth::where('user_id', Auth::id())->where('type', 0)->orderByDesc('id')->first();

        if($auth == null)
        {
            return redirect()->back()->with('error', '이메일 인증코드를 입력해주십시오.');
        }

        $auth_code = $request->get('auth_code');

        if($auth->code == $auth_code)
        {
            if($auth->status == 0)
            {
                $auth->status = 1;
                $auth->save();
            }
            else
            {
                return redirect()->back()->with('error', '잘못된 인증 정보입니다.');
            }
        }
        else
        {
            return redirect()->back()->with('error', '이메일 인증코드를 다시 확인해주십시오.');
        }

        $available = $referer->point_1;
        $amount = $package->price;
        $amount_bp = $package->bp;

        /*
        if($referer->point_1 < $package->price)
        {
            return redirect()->back()->with('error', '보유 포인트가 부족합니다.');
        }

        $referer->point_1 = $referer->point_1 - $package->price;
        $referer->save();
        */

        $referer_group->member = $referer_group->member + 1;
        $referer_group->save();

        $count = UserLogJoin::where('recommend_id', $referer->id)->count();

        if($count < 3)
        {
            $type = 1;
            $fee = 5;
        }
        else
        {
            $type = 2;
            $fee = 10;
        }

        $user_group = Group::create([
            'parent_id' => $referer_group->id,
            'label' => $referer_group->label,
            'layer' => $referer_group->layer + 1,
            'type' => $type,
            'fee' => $fee,
            'member' => 0,
            'revenue' => 0
        ]);

        $random = Str::random(12);

        $user = User::create([
            "username" => $request->get('username'),
            'nickname' => $request->get('username'),
            "password" => bcrypt($request->get('password')),
            "email" => $request->get('email'),
            "phone" => $request->get('phone'),
            "level" => 0,
            "status" => 1,
            "group_id" => $user_group->id,
            "referer_code" => $random
        ]);

        $user->assignRole("회원");

        GroupJoin::create([
            'group_id' => $user_group->id,
            'user_id' => $user->id
        ]);

        UserLogJoin::create([
            'recommend_id' => $referer->id,
            'user_id' => $user->id,
            'label' => 0,
            'come' => UserLogJoin::where('recommend_id', $referer->id)->count() + 1
        ]);

        UserLogLevel::create([
            'user_id' => $user->id,
            'level' => 1
        ]);

        Organization::create([
            'parent_id' => $request->get('parent_id'),
            'user_id' => $user->id,
            'team' => $request->get('team'),
            'revenue' => 0
        ]);

        $newWallet = EthereumController::getNewWallet();
        EthereumWallet::create([
            'user_id' => $user->id,
            'address' => $newWallet['address'],
            'private_key' => $newWallet['private_key'],
            'balance' => 0,
            'token_balance' => 0
        ]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'paid' => $amount,
            'bp' => $amount_bp,
            'status' => 0
        ]);

        return redirect()->back()->with('success', '계정을 생성하였습니다. 이메일을 확인해주세요.');
    }

    public function authEmail(Request $request)
    {
        $request->validate([
            "email" => "required|email"
        ]);

        $rand_num = sprintf('%06d',rand(000000,999999));

        $auth = UserLogAuth::create([
            'user_id' => Auth::id(),
            'code' => $rand_num,
            'type' => 0,
            'status' => 0
        ]);

        MailController::sendMail([
            'target_email' => array(
                array(
                    'name' => 'MEMBER',
                    'email' => $request->get('email')
                )
            ),
            'subject' => 'AEGIS 인증 코드',
            'content' => "AEGIS 인증 코드는 {$auth->code} 입니다."
        ]);
    }

    public function requestAccount(Request $request)
    {
        $my_purchase = Purchase::where('user_id', Auth::id())->orderby('id', 'desc')->first();

        if($my_purchase == null)
        {
            return redirect()->route('user.dashboard.package.buy')->with('error', '먼저 패키지를 구매해주십시오.');
        }

        $request->validate([
            "username" => "required|unique:users,username",
            "password" => "required|min:8",
            "email" => "required|email",
            "phone" => "required",
            "package" => "required",
            "org" => "required",
            "parent_id" => "required",
            "team" => "required",
        ]);

        $referer = User::find(Auth::id());
        $referer_group = Group::find($referer->group_id);

        $package = Package::find($request->get('package'));

        if($package == null)
        {
            return redirect()->back()->with('error', '올바르지 않은 패키지입니다.');
        }

        $available = $referer->point_1;
        $amount = $package->price;
        $amount_bp = $package->bp;

        if($referer->point_1 < $package->price)
        {
            return redirect()->back()->with('error', '보유 포인트가 부족합니다.');
        }

        $referer->point_1 = $referer->point_1 - $package->price;
        $referer->save();

        $referer_group->member = $referer_group->member + 1;
        $referer_group->save();

        $count = UserLogJoin::where('recommend_id', $referer->id)->count();

        if($count < 3)
        {
            $type = 1;
            $fee = 5;
        }
        else
        {
            $type = 2;
            $fee = 10;
        }

        $user_group = Group::create([
            'parent_id' => $referer_group->id,
            'label' => $referer_group->label,
            'layer' => $referer_group->layer + 1,
            'type' => $type,
            'fee' => $fee,
            'member' => 0,
            'revenue' => $amount
        ]);

        $user = User::create([
            "username" => $request->get('username'),
            'nickname' => $request->get('username'),
            "password" => bcrypt($request->get('password')),
            "email" => $request->get('email'),
            "phone" => $request->get('phone'),
            "level" => 1,
            "status" => 1,
            "group_id" => $user_group->id,
            "referer_code" => $request->get('username')
        ]);

        $user->assignRole("회원");

        GroupJoin::create([
            'group_id' => $user_group->id,
            'user_id' => $user->id
        ]);

        UserLogJoin::create([
            'recommend_id' => $referer->id,
            'user_id' => $user->id,
            'label' => 0,
            'come' => UserLogJoin::where('recommend_id', $referer->id)->count() + 1
        ]);

        UserLogLevel::create([
            'user_id' => $user->id,
            'level' => 1
        ]);

        Organization::create([
            'parent_id' => $request->get('parent_id'),
            'user_id' => $user->id,
            'team' => $request->get('team'),
            'revenue' => $amount
        ]);

        if($referer->level == 1)
        {
            if($count == 4)
            {
                $referer->level = 2;
                $referer->save();

                UserLogLevel::create([
                    'user_id' => $user->id,
                    'level' => 2
                ]);
            }
        }

        $newWallet = EthereumController::getNewWallet();
        EthereumWallet::create([
            'user_id' => $user->id,
            'address' => $newWallet['address'],
            'private_key' => $newWallet['private_key'],
            'balance' => 0,
            'token_balance' => 0
        ]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'paid' => $amount,
            'bp' => $amount_bp,
            'status' => 1
        ]);

        UserLogPoint::create([
            'user_id' => $referer->id,
            'purchase_id' => $purchase->id,
            'before' => $available,
            'amount' => $amount,
            'after' => $referer->point_1,
            'log' => "$referer->username 님이 패키지 구매로 $amount 차감되었습니다.",
            'type' => 0,
            'status' => 1
        ]);

        $user->point_2 = $user->point_2 + $package->bp;
        $user->save();

        UserLogPoint::create([
            'user_id' => $user->id,
            'purchase_id' => $purchase->id,
            'before' => 0,
            'amount' => $package->bp,
            'after' => $user->point_2,
            'log' => "$user->username 님의 패키지 구매로 BP $package->bp 적립되었습니다.",
            'type' => 1,
            'status' => 1
        ]);

        //RewardController::start($purchase);
        RewardJob::dispatch($purchase);
        //RewardJob::dispatch($purchase)->delay(now()->addMinutes(10));

        return redirect()->back()->with('success', '계정을 생성하였습니다. 이메일을 확인해주세요.');
    }

    public function myTeam()
    {
        $team_a = User::from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', Auth::user()->group_id)->where('type', 1)->get();
        $team_b = User::from('users as a')->leftJoin('groups as b', 'a.group_id', '=', 'b.id')->where('b.parent_id', Auth::user()->group_id)->where('type', 2)->get();

        return view('user.pages.mypage.team', compact('team_a', 'team_b'));
    }

    public function myOrganization(Request $request)
    {
        $arr = [];
        $queue = [];
        $available = [];
        $for_level = $request->has('level') ? $request->get('level') : 3;
        $orgs = Organization::orderBy('team', 'asc')->get();

        foreach($orgs as $org)
        {
            if($org->user_id == Auth::id())
            {
                array_push($arr, [
                    'id' => $org->user_id,
                    'title' => $org->user->username,
                    'name' => $org->user->name,
                    'team' => "OWN TEAM",
                    'level' => $org->user->level_label->label,
                    'package' => $org->user->package == null ? 0 : number_format($org->user->package->package->price),
                    'total_revenue' => 0,
                    'package2' => $org->user->package == null ? 0 : number_format($org->user->package->package->price),
                    'total_revenue2' => 0,
                    'sub_revenue' => 0,
                    'revenue' => $org->revenue,
                    'date' => Carbon::parse($org->created_at)->format('Y-m-d H:i'),
                    'img' => asset('assets/img/dashboard/rocket-icon.png')
                ]);
                array_push($queue, $org->user_id);
                array_push($available, 0);
            }
        }

        for($i = 0 ; $i < count($queue) ; $i++)
        {
            $index = $queue[$i];

            foreach($orgs as $org)
            {
                if($org->parent_id == $index)
                {
                    array_push($arr, [
                        'id' => $org->user_id,
                        'pid' => $org->parent_id,
                        'title' => $org->user->username,
                        'name' => $org->user->name,
                        'team' => $org->team == 0 ? "LEFT TEAM" : "RIGHT TEAM",
                        'level' => $org->user->level_label->label,
                        'package' => $org->user->package == null ? 0 : number_format($org->user->package->package->price),
                        'total_revenue' => 0,
                        'package2' => $org->user->package == null ? 0 : number_format($org->user->package->package->price),
                        'total_revenue2' => 0,
                        'sub_revenue' => 0,
                        'revenue' => $org->revenue,
                        'date' => Carbon::parse($org->created_at)->format('Y-m-d H:i'),
                        'img' => asset('assets/img/dashboard/rocket-icon.png')
                    ]);
                    array_push($queue, $org->user_id);
                    array_push($available, $org->parent_id);
                }
            }

            if($i >= $for_level)
            {
                break;
            }
        }


        $arr = array_reverse($arr);
        for($i = 0; $i < count($arr); $i++)
        {
            $queue = [];
            array_push($queue, $arr[$i]['id']);
            for($j = 0 ; $j < count($queue) ; $j++)
            {
                $index = $queue[$j];

                foreach($orgs as $org)
                {
                    if($org->parent_id == $index)
                    {
                        $arr[$i]['total_revenue'] += $org->revenue;
                        array_push($queue, $org->user_id);
                    }
                }
            }
        }

        for($i = 0; $i < count($arr); $i++)
        {
            $arr[$i]['total_revenue'] += $arr[$i]['revenue'];
            $arr[$i]['total_revenue'] = number_format($arr[$i]['total_revenue']);
        }

        $arr = json_encode(array_reverse($arr));

        return view('user.pages.mypage.organization', compact('arr', 'for_level'));
    }

    public function requestOrg(Request $request)
    {
        $inputs = $request->all();
        $parents = $inputs['parent_id'];
        $users = $inputs['user_id'];

        $organizations = Organization::get();

        $queue = [];
        foreach($parents as $index => $parent)
        {
            array_push($queue, $parent);
        }
        $arrs = array_count_values($queue);
        foreach($arrs as $arr)
        {
            if($arr > 2)
            {
                return redirect()->route('user.dashboard.mypage.myOrganization')->with('error', '레그는 3개 이상될 수 없습니다.');
            }
        }

        $queue = [];
        foreach($organizations as $org)
        {
            foreach($users as $index => $user)
            {
                if($user == $org->user_id)
                {
                    array_push($queue, $index);
                }
            }
        }

        for($i = 0 ; $i < count($users); $i++)
        {
            $bool = false;
            foreach($queue as $que)
            {
                if($que == $i)
                {
                    $bool = true;
                    break;
                }
            }

            if(!$bool)
            {
                $parent = $parents[$i];
                $user = $users[$i];

                Organization::create([
                    'parent_id' => $parent,
                    'user_id' => $user
                ]);

                $user = User::find($user);
                $user->status = 1;
                $user->save();
            }
        }

        return redirect()->route('user.dashboard.mypage.myOrganization');
    }

    public function setWithdrawPassword(Request $request)
    {
        $request->validate([
            "set_withdraw" => "required",
            'set_withdraw_confirm' => "required|same:set_withdraw"
        ]);

        $user = User::find(Auth::id());

        $user->withdraw_password = bcrypt($request->get('set_withdraw'));
        $user->save();

        return redirect()->back()->with('success', '출금 비밀번호를 설정하였습니다.');
    }

    public function changeEmail(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            'auth_code' => "required"
        ]);

        $auth = UserLogAuth::where('user_id', Auth::id())->where('type', 1)->orderByDesc('id')->first();

        if($auth == null)
        {
            return redirect()->back()->with('error', '이메일 인증코드를 입력해주십시오.');
        }

        $auth_code = $request->get('auth_code');

        if($auth->code == $auth_code)
        {
            if($auth->status == 0)
            {
                $user = User::find(Auth::id());
                $user->email = $request->get('email');
                $user->save();

                $auth->status = 1;
                $auth->save();

                return redirect()->back()->with('success', '이메일을 변경하였습니다.');
            }
            else
            {
                return redirect()->back()->with('error', '잘못된 인증 정보입니다.');
            }
        }
        else
        {
            return redirect()->back()->with('error', '이메일 인증코드를 다시 확인해주십시오.');
        }
    }

    public function changeAuthEmail(Request $request)
    {
        $request->validate([
            "email" => "required|email"
        ]);

        $rand_num = sprintf('%06d',rand(000000,999999));

        $auth = UserLogAuth::create([
            'user_id' => Auth::id(),
            'code' => $rand_num,
            'type' => 1,
            'status' => 0
        ]);

        MailController::sendMail([
            'target_email' => array(
                array(
                    'name' => 'MEMBER',
                    'email' => $request->get('email')
                )
            ),
            'subject' => 'AEGIS 인증 코드',
            'content' => "AEGIS 인증 코드는 {$auth->code} 입니다."
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            "current_password" => "required",
            "change_password" => "required|min:8",
            "change_password_confirm" => "required|same:change_password"
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->get('current_password'), $user->password)) {
            return redirect()->back()->with('error', '현재 비밀번호가 맞지 않습니다.');
        }

        $user->password = bcrypt($request->get('change_password'));
        $user->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')->with('success', '비밀번호를 변경했습니다. 변경한 비밀번호로 로그인해주세요.');
    }

    public function changeWithdrawPassword(Request $request)
    {
        $request->validate([
            "current_withdraw" => "required",
            "change_withdraw" => "required",
            "change_withdraw_confirm" => "required|same:change_withdraw"
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->get('current_withdraw'), $user->withdraw_password)) {
            return redirect()->back()->with('error', '현재 출금 비밀번호가 맞지 않습니다.');
        }

        $user->withdraw_password = bcrypt($request->get('change_withdraw'));
        $user->save();

        return redirect()->back()->with('success', '출금 비밀번호를 변경하였습니다.');
    }

    public function transfer()
    {
        return view('user.pages.mypage.transfer');
    }

    public function transfer_request(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'point' => 'required|numeric|regex:/^\d*(\.\d{1,2})?$/',
            'password' => 'required'
        ]);

        $user_check = $this->transfer_check($request->get('username'));

        if($user_check->original['result'])
        {
            if(Auth::user()->withdraw_password == null)
            {
                return redirect()->back()->withInput()->with('error', '먼저 출금 비밀번호를 설정해주십시오.');
            }

            if (!Hash::check($request->get('password'), Auth::user()->withdraw_password))
            {
                return redirect()->back()->withInput()->with('error', '현재 출금 비밀번호가 맞지 않습니다.');
            }

            $user = User::find(Auth::id());

            if((float)$request->get('point') > $user->point_1)
            {
                return redirect()->back()->withInput()->with('error', "전송하려는 포인트가 현재 보유 포인트보다 많습니다.");
            }

            $user->point_1 = $user->point_1 - (float)$request->get('point');
            $user->save();

            $transfer = User::where('username', $request->get('username'))->first();
            $transfer->point_1 = $transfer->point_1 + (float)$request->get('point');
            $transfer->save();

            $purchase = Purchase::where('user_id', $transfer->id)->orderby('id', 'desc')->first();
            if($purchase != null) {
                $this->executePurchase($purchase);
            }

            return redirect()->back()->with('success', "전송했습니다.");
        }
        else
        {
            return redirect()->back()->withInput()->with('error', "전송할 수 없습니다.");
        }
    }

    public function transfer_check($username)
    {
        $groups = Group::get();

        $accept_group_top = [];
        $accept_group_bottom = [];

        $user_group = Group::find(Auth::user()->group_id);
        array_push($accept_group_top, $user_group->parent_id);
        array_push($accept_group_bottom, $user_group->id);

        for($i = 0; $i < count($accept_group_top); $i++)
        {
            $item = $accept_group_top[$i];

            //top
            foreach($groups as $group)
            {
                if($group->id == $item)
                {
                    array_push($accept_group_top, $group->parent_id);
                }
            }
        }


        for($i = 0; $i < count($accept_group_bottom); $i++)
        {
            $item = $accept_group_bottom[$i];

            //bottom
            foreach($groups as $group)
            {
                if($group->parent_id == $item)
                {
                    array_push($accept_group_bottom, $group->id);
                }
            }
        }

        $accept_group = array_merge($accept_group_top, $accept_group_bottom);

        $find_user = User::where('username', $username)->first();

        if($find_user == null)
        {
            return response()->json([
                'result' => false,
                'message' => '존재하지 않는 회원입니다.'
            ]);
        }

        if(!in_array($find_user->group_id, $accept_group))
        {
            return response()->json([
                'result' => false,
                'message' => '전송할 수 없습니다.'
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => '전송할 수 있습니다.'
        ]);
    }

    public function executePurchase(Purchase $purchase)
    {
        if($purchase->status == 0)
        {
            $user = User::find($purchase->user_id);

            $target_amount = $purchase->paid / 100 * 85;
            $target_aos = $purchase->paid / 100 * 15;

            $aos = AosSetting::first();

            $target_aos = ceil($target_aos / $aos->price * 100) / 100;

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
                //RewardJob::dispatch($purchase);
            }
        }
    }
}
