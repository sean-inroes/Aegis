<?php

namespace App\Http\Controllers\User\Wallet;

use App\Http\Controllers\Controller;
use App\Models\AosSetting;
use App\Models\EthereumWallet;
use App\Models\Purchase;
use App\Models\PurchaseDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    public function eth()
    {
        return view('user.pages.wallet.eth');
    }

    public function aos()
    {
        $aos = AosSetting::first();
        return view('user.pages.wallet.aos', compact('aos'));
    }

    public function unpaid()
    {
        $purchase = Purchase::where('user_id', Auth::id())->where('status', 0)->first();
        $purchases = PurchaseDeposit::where('purchase_id', $purchase->id)->get();
        $tether_p = Auth::user()->point_1;
        $aos_p = Auth::user()->point_3;

        $wallet = EthereumWallet::where('user_id', Auth::id())->first();

        $aos = AosSetting::first();

        $target_amount = $purchase->paid / 100 * 85;
        $target_aos = $purchase->paid / 100 * 15;
        $target_aos = ceil($target_aos / $aos->price * 100) / 100;

        return view('user.pages.wallet.unpaid', compact('purchase', 'tether_p', 'aos_p', 'wallet', 'aos', 'target_amount', 'target_aos'));
    }
}
