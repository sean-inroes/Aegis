<?php

namespace App\Http\Controllers\User\Account;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\TransactionSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class RouteController extends Controller
{
    public function withdraw()
    {
        $txsetting = TransactionSetting::first();
        $fee = $txsetting->withdraw_fee;
        return view('user.pages.account.withdraw', compact('fee'));
    }

    public function list(Request $request)
    {
        $start = $request->has('start') ? $request->get('start') : null;
        $end = $request->has('end') ? $request->get('end') : null;

        if($start == null || $end == null)
        {
            $now = Carbon::now();
            $start = $now->startOfWeek()->timestamp;
            $end = $now->endOfWeek()->timestamp;
        }

        $startdate = Carbon::createFromTimestamp($start)->format('Y-m-d');
        $enddate = Carbon::createFromTimestamp($end)->format('Y-m-d');

        $logs = Transaction::where('user_id', Auth::id())
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->orderByDesc('id')
            ->paginate(20);

        $agent = new Agent();
        if($agent->isMobile())
        {
            return view('user.pages.account.mobile-list', compact('logs', 'startdate', 'enddate'));
        }
        else
        {
            return view('user.pages.account.list', compact('logs', 'startdate', 'enddate'));
        }
    }

    public function requestWithdraw(Request $request)
    {
        $request->validate([
            "withdraw_account" => "required",
            "withdraw_amount" => "required",
            "withdraw_password" => "required"
        ]);

        if(Auth::user()->withdraw_password == null)
        {
            return redirect()->route('user.dashboard.mypage.myAccount')->with('error', '출금 비밀번호를 먼저 설정해주십시오.');
        }

        $available = Auth::user()->point_1;
        $request_amount = (double)$request->get('withdraw_amount');

        if($request_amount < 103)
        {
            return redirect()->back()->with('error', '출금 가능한 최소 보너스는 103 입니다.');
        }

        if($request_amount > $available)
        {
            return redirect()->back()->with('error', '보유 보너스보다 더 많은 금액을 출금 요청하셨습니다.');
        }

        if (!Hash::check($request->get('withdraw_password'), Auth::user()->withdraw_password))
        {
            return redirect()->back()->with('error', '현재 출금 비밀번호가 맞지 않습니다.');
        }

        if(!APIController::isAddress($request->get('withdraw_account')))
        {
            return redirect()->back()->with('error', 'ETH 규격에 맞지 않는 주소입니다.');
        }

        $txsetting = TransactionSetting::first();
        $fee = $txsetting->withdraw_fee;

        $request_fee = round($request_amount / 100 * $fee);
        $request_real = round($request_amount / 100 * (100 - $fee));

        $user = User::find(Auth::id());
        $user->point_1 = $user->point_1 - $request_amount;
        $user->save();

        Transaction::create([
            'user_id' => Auth::id(),
            'coin_id' => 1,
            'type' => 1,
            'amount' => $request_real,
            'fee' => $request_fee,
            'real_amount' => $request_amount,
            'to_addr' => $request->get('withdraw_account'),
            'status' => 0
        ]);

        return redirect()->back()->with('success', '출금 신청하였습니다.');
    }

    public function checkAddress(Request $request)
    {
        $result = APIController::isAddress($request->get('address'));
        return [
            'data' => $result,
            'message' => $result ? __('account.input.account.message2') : __('account.input.account.message3')
        ];
    }
}
