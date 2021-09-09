<?php

namespace App\Http\Controllers\User\Sign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EthereumController;
use App\Http\Controllers\MailController;
use App\Models\EthereumWallet;
use App\Models\User;
use App\Models\UserLogAuth;
use App\Models\UserLogJoin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SignController extends Controller
{
    public function login()
    {
        return view('user.pages.sign.login');
    }

    public function login_request(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $user = User::where('username', $request->get('username'))->first();
        if($user == null)
        {
            return redirect()->back()->with('error', '아이디 및 비밀번호가 일치하지 않습니다.');
        }

        if($user->status == 0)
        {
            return redirect()->back()->with('error', '아직 조직이 활성화되지 않았습니다.');
        }

        if (Auth::attempt($credentials, $request->get('remember_me'))) {
            $request->session()->regenerate();
            return redirect()->route('user.dashboard.index');
        }

        return redirect()->back()->with('error', '아이디 및 비밀번호가 일치하지 않습니다.');
    }

    public function join()
    {
        return view('user.pages.sign.join-step1');
    }

    public function joinstep2()
    {
        return view('user.pages.sign.join-step2');
    }

    public function joinstep3(Request $request)
    {
        $request->validate([
            "username" => "required|unique:users,username",
            "password" => "required|min:8",
            "email" => "required|email",
            "name" => "required",
            "phone" => "required",
        ]);

        return view('user.pages.sign.join-step3');
    }

    public function join_request(Request $request)
    {
        $request->validate([
            "username" => "required|unique:users,username",
            "password" => "required|min:8",
            "email" => "required|email",
            "name" => "required",
            "phone" => "required",
            "referer" => 'exists:users,referer_code'
        ]);

        $group_id = null;

        if($request->has('referer'))
        {
            $referer = $request->get('referer');
            $group_id = User::where('referer_code', $referer)->first();
        }

        //https://rinkeby.infura.io/v3/b5e57419bbfc480b88858b8fd0d701b0

        $user = User::create([
            "username" => $request->get('username'),
            'nickname' => $request->get('name'),
            "password" => bcrypt($request->get('password')),
            "email" => $request->get('email'),
            "name" => $request->get('name'),
            "phone" => $request->get('phone'),
            "group_id" => $group_id->id,
            "referer_code" => $request->get('username')
        ]);

        $user->assignRole("회원");

        $newWallet = EthereumController::getNewWallet();

        EthereumWallet::create([
            'user_id' => $user->id,
            'address' => $newWallet['address'],
            'private_key' => $newWallet['private_key'],
            'balance' => 0,
            'token_balance' => 0
        ]);

        UserLogJoin::create([
            'recommend_id' => $group_id->id,
            'user_id' => $user->id,
            'label' => 0,
            'come' => UserLogJoin::where('recommend_id', $group_id->id)->count() + 1
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('user.dashboard.index');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }

    public function resetpassword()
    {
        return view('user.pages.sign.reset-password');
    }

    public function resetpassword_request(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:users'
        ]);

        $user = User::where('username', $request->get('username'))->first();

        $newDateTime = Carbon::now()->subMinutes(5)->timestamp;
        $already = UserLogAuth::where('user_id', $user->id)->where('type', 2)->where('status', 0)->where('created_at', '>=', $newDateTime)->count();

        if($already == 0)
        {
            $rand_num = sprintf('%06d',rand(000000,999999));

            $auth = UserLogAuth::create([
                'user_id' => $user->id,
                'code' => $rand_num,
                'type' => 2,
                'status' => 0
            ]);

            MailController::sendMail([
                'target_email' => array(
                    array(
                        'name' => 'MEMBER',
                        'email' => $user->email
                    )
                ),
                'subject' => 'AEGIS 인증 코드',
                'content' => "AEGIS 인증 코드는 [{$auth->code}] 입니다."
            ]);
        }

        return view('user.pages.sign.reset-password-step2');
    }

    public function resetpassword_confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:user_log_auths'
        ]);

        $newDateTime = Carbon::now()->subMinutes(5)->timestamp;
        $auth = UserLogAuth::where('code', $request->get('code'))->where('created_at', '>=', $newDateTime)->first();

        if($auth == null)
        {
            return redirect()->back()->with('error', '유효한 인증 값이 없습니다');
        }

        return view('user.pages.sign.reset-password-step3', compact('auth'));
    }

    public function resetpassword_change(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'password' => 'required|same:password_confirm',
            'password_confirm' => 'required',
        ]);

        $user = User::find($request->get('user_id'));
        $user->password = bcrypt($request->get('password'));
        $user->save();

        return redirect()->route('user.login')->with('success', '비밀번호를 변경했습니다.');
    }
}
