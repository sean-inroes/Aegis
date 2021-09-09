<?php

namespace App\Http\Controllers\Admin\Wallet;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EthereumController;
use App\Http\Controllers\GethController;
use App\Models\EthereumSetting;
use App\Models\EthereumWallet;
use App\Models\EthereumWalletTx;
use App\Models\Transaction;
use App\Models\TransactionSetting;
use App\Models\User;
use EthereumRPC\EthereumRPC;
use FurqanSiddiqui\Ethereum\ERC20\ERC20;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Web3\Web3;
use Web3p\EthereumTx\Transaction as Transactions;


class UrlController extends Controller
{
    public function index()
    {
        $wallets = EthereumWallet::paginate(20);
        $txsetting = TransactionSetting::first();
        $fee = $txsetting->withdraw_fee;

        return view('manage.pages.wallet.index', compact('wallets', 'fee'));
    }

    public function show($id)
    {
        $wallet = EthereumWallet::find($id);
        //return dechex((int)$geth->eth_blockNumber());eth_subscribe
        //return $geth->eth_subscribe();
        //return $geth->eth_getBlockByNumber("0x".dechex((int)$geth->eth_blockNumber()), true);
        //$newWallet = EthereumController::getNewWallet();
        return view('manage.pages.wallet.show', compact('wallet'));
    }

    public function edit()
    {
        $geth = new GethController();
    }

    public function store()
    {

    }

    public function update()
    {

    }

    public function gasprice_eth($id)
    {
        $wallet = EthereumWallet::find($id);

        $gaslimit = 21000;

        $request = APIController::balance($wallet->address);
        $balance = (double)$request->balance;

        $client = new Client();
        $request = $client->request('get', "https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey=8XVDEBJ2V3XGYR3TRYIB1S2R74MCF33C4J");
        $response = json_decode($request->getBody());

        return view('manage.pages.wallet.gasprice_eth', compact('id', 'balance', 'response', 'gaslimit'));
    }

    public function gasprice_token($id)
    {
        $wallet = EthereumWallet::find($id);

        $request = APIController::balance($wallet->address);
        $balance = (double)$request->balance;

        $request = APIController::decimals();
        $token_decimal = (double)$request->decimals;
        $std = pow(10, $token_decimal);

        $request = APIController::tokenBalance($wallet->address);
        $token_balance = (double)$request->balance / $std;

        $request = APIController::tokenGasPrice($wallet->address, strtolower(env("ETH_WITHDRAW_ADDR")), $token_balance);
        $gaslimit = (double)$request->gasAmount;

        $client = new Client();
        $request = $client->request('get', "https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey=8XVDEBJ2V3XGYR3TRYIB1S2R74MCF33C4J");
        $response = json_decode($request->getBody());

        return view('manage.pages.wallet.gasprice_token', compact('id', 'balance', 'token_balance', 'response', 'gaslimit'));
    }

    public function withdraw_eth($id, Request $requests)
    {
        $requests->validate([
            'gasprice' => 'required'
        ]);

        $wallet = EthereumWallet::find($id);

        $request = APIController::balance($wallet->address);
        $balance = (double)$request->balance;

        $gasprice = (double)$requests->get('gasprice');

        $transfer_fee = $gasprice * 21000 / 1000000000;

        if($transfer_fee >= $balance)
        {
            return redirect()->back()->with('error', '보유 ETH가 수수료보다 작습니다.');
        }

        $amount = $balance - $transfer_fee;
        $amount = rtrim(sprintf('%f', $amount),'0');

        $request = APIController::transfer($wallet->address, strtolower(env("ETH_WITHDRAW_ADDR")), substr($wallet->private_key, 2), $amount, $gasprice);

        if($request->txHash)
        {
            EthereumWalletTx::create([
                'wallet_id' => $wallet->id,
                'hash' => $request->txHash
            ]);

            return redirect()->back()->with('success', 'ETH를 전송하였습니다.');
        }
        else
        {
            return redirect()->back()->with('error', 'ETH를 전송하지못했습니다.');
        }
    }

    public function withdraw_token($id, Request $requests)
    {
        $requests->validate([
            'gasprice' => 'required'
        ]);

        $wallet = EthereumWallet::find($id);

        $request = APIController::balance($wallet->address);
        $balance = (double)$request->balance;

        $request = APIController::decimals();
        $token_decimal = (double)$request->decimals;
        $std = pow(10, $token_decimal);

        $request = APIController::tokenBalance($wallet->address);
        $token_balance = (double)$request->balance / $std;

        if(0 == $token_balance)
        {
            return redirect()->back()->with('error', '보유 토큰이 없습니다.');
        }

        $request = APIController::tokenGasPrice($wallet->address, strtolower(env("ETH_WITHDRAW_ADDR")), $token_balance);
        $gaslimit = (double)$request->gasAmount;

        $gasprice = (double)$requests->get('gasprice');

        $transfer_fee = $gasprice * $gaslimit / 1000000000;

        if($transfer_fee > $balance)
        {
            return redirect()->back()->with('error', '수수료가 부족합니다.');
        }

        $request = APIController::tokenTransfer($wallet->address, strtolower(env("ETH_WITHDRAW_ADDR")), substr($wallet->private_key, 2), $token_balance, $gasprice);

        if($request->txHash)
        {
            EthereumWalletTx::create([
                'wallet_id' => $wallet->id,
                'hash' => $request->txHash
            ]);

            return redirect()->back()->with('success', '토큰을 전송하였습니다.');
        }
        else
        {
            return redirect()->back()->with('error', '토큰을 전송하지못했습니다.');
        }
    }
}
