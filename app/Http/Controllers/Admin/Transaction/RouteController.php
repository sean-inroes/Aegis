<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function deposit()
    {
        $txs = Transaction::where('type', 0)->orderByDesc('id')->paginate(20);

        return view('manage.pages.transaction.deposit', compact('txs'));
    }

    public function withdraw()
    {
        $txs = Transaction::where('type', 1)->orderByDesc('id')->paginate(20);

        return view('manage.pages.transaction.withdraw', compact('txs'));
    }

    public function changestatus(Request $request)
    {
        $request->validate([
            "tx_id" => "required",
            "tx" => "required",
            "status" => "required"
        ]);

        $status = (int)$request->get('status');

        if($status == 1)
        {
            $tx = Transaction::find($request->get('tx_id'));
            $tx->status = 1;
            $tx->tx = $request->get('tx');
            $tx->save();

            return redirect()->back()->with('success', '승인하였습니다.');
        }
        elseif($status == 2)
        {
            $tx = Transaction::find($request->get('tx_id'));
            $tx->status = 2;
            $tx->tx = $request->get('tx');
            $tx->save();

            $user = User::find($tx->user_id);
            $user->point_1 = $user->point_1 + $tx->real_amount;
            $user->save();

            return redirect()->back()->with('success', '취소하였습니다.');
        }
        else
        {
            return redirect()->back()->with('error', '잘못된 값입니다.');
        }
    }
}
