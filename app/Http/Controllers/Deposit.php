<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Http\Controllers\GethController;
use App\Jobs\RewardJob;
use App\Models\EthereumSetting;
use App\Models\EthereumWallet;
use App\Models\EthereumWalletTx;
use App\Models\EtherscanTx;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\PurchaseDeposit;
use App\Models\TestValue;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogLevel;
use App\Models\UserLogPoint;
use GuzzleHttp\Client;
use EOSPHP\EOSClient;
use App\Models\AosSetting;
use App\Models\PurchaseActiveLog;
use Illuminate\Http\Request;

class Deposit extends Controller
{
    public function ethereum()
    {
        $setting = EthereumSetting::first();
        $geth = new GethController();

        if($setting->last_blocks == null)
        {
            $setting->last_blocks = $geth->eth_blockNumber();
            $setting->save();

            return;
        }

        $allwallets = EthereumWallet::get();
        $newBlock = (float)$geth->eth_blockNumber();

        for($i = $setting->last_blocks; $i < $newBlock; $i++)
        {
            $blockNum = $i + 1;
            $block = $geth->eth_getBlockByNumber("0x".dechex((int)$blockNum), true);
            //$block = $geth->eth_getBlockByNumber("0x".dechex((int)9033558), true);

            $txs = $block['transactions'];
            for($j = 0; $j < count($txs); $j++)
            {
                $tx = $txs[$j];

                if($tx['to'] == strtolower(env('ETH_CONTRACT_ADDR')))
                {
                    $inputdata = $tx['input'];
                    if(str_starts_with($inputdata, '0xa9059cbb'))
                    {
                        $address = substr($inputdata, 10, 64);

                        foreach($allwallets as $wallet)
                        {
                            //나가는거
                            if($tx['from'] == strtolower($wallet->address))
                            {
                                $amount = (double)$this->bchexdec(substr($inputdata, 74, 64));
                                $amount = $amount / pow(10, env('ETH_CONTRACT_DECIMAL'));

                                $txx = $geth->eth_getTransactionReceipt($tx['hash']);

                                if($txx['status'] == "0x1")
                                {
                                    $gasLimit = (double)$this->bchexdec(substr($txx['gasUsed'], 2));
                                    $gasPrice = (double)$this->bchexdec(substr($txx['effectiveGasPrice'], 2));

                                    $fee = $gasLimit * $gasPrice;
                                    $fee = $fee / pow(10, 18);

                                    $user = User::find($wallet->user_id);

                                    $wallet->balance = $wallet->balance - $fee;
                                    $wallet->token_balance = $wallet->token_balance - $amount;
                                    $wallet->save();

                                    $client = new Client();
                                    $client->request('GET', "https://api.telegram.org/bot1921486506:AAHmOlXkbGj01L6sRsDxFzwk68YVHMatsQ8/sendMessage?chat_id=-1001469638282&text={$user->username}님의 ETH 지갑에서 토큰 {$amount} 출금되었습니다.");
                                }
                            }

                            //들어오는거
                            if(strpos($address, substr($wallet->address, 2, 40)))
                            {
                                $amount = (double)$this->bchexdec(substr($inputdata, 74, 64));
                                $amount = $amount / pow(10, env('ETH_CONTRACT_DECIMAL'));

                                $txx = $geth->eth_getTransactionReceipt($tx['hash']);

                                if($txx['status'] == "0x1")
                                {
                                    $user = User::find($wallet->user_id);
                                    $user->point_1 = $user->point_1 + $amount;
                                    $user->save();

                                    $wallet->token_balance = $wallet->token_balance + $amount;
                                    $wallet->save();

                                    Transaction::create([
                                        'user_id' => $user->id,
                                        'coin_id' => 1,
                                        'type' => 0,
                                        'amount' => $amount,
                                        'tx' => $tx['hash'],
                                        'status' => 1
                                    ]);

                                    $client = new Client();
                                    $client->request('GET', "https://api.telegram.org/bot1921486506:AAHmOlXkbGj01L6sRsDxFzwk68YVHMatsQ8/sendMessage?chat_id=-1001469638282&text={$user->username}님의 ETH 지갑으로 토큰 {$amount} 입금되었습니다.");

                                    $purchase = Purchase::where('user_id', $user->id)->orderby('id', 'desc')->first();
                                    if($purchase != null) {
                                        $this->executePurchase($purchase);
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    foreach($allwallets as $wallet)
                    {
                        if($tx['input'] == "0x")
                        {
                            $gasLimit = (double)$this->bchexdec(substr($tx['gas'], 2));
                            $gasPrice = (double)$this->bchexdec(substr($tx['gasPrice'], 2));
                            $amount = (double)$this->bchexdec(substr($tx['value'], 2));

                            $fee = $gasLimit * $gasPrice;
                            $fee = $fee / pow(10, 18);
                            $amount = $amount / pow(10, 18);

                            if(strtolower($tx['from']) == strtolower($wallet->address))
                            {
                                $user = User::find($wallet->user_id);

                                $wallet->balance = $wallet->balance - $amount - $fee;
                                $wallet->save();

                                $client = new Client();
                                $client->request('GET', "https://api.telegram.org/bot1921486506:AAHmOlXkbGj01L6sRsDxFzwk68YVHMatsQ8/sendMessage?chat_id=-1001469638282&text={$user->username}님의 ETH 지갑에서 {$amount} 출금되었습니다.");
                            }

                            if(strtolower($tx['to']) == strtolower($wallet->address))
                            {
                                $user = User::find($wallet->user_id);

                                $wallet->balance = $wallet->balance + $amount;
                                $wallet->save();

                                $client = new Client();
                                $client->request('GET', "https://api.telegram.org/bot1921486506:AAHmOlXkbGj01L6sRsDxFzwk68YVHMatsQ8/sendMessage?chat_id=-1001469638282&text={$user->username}님의 ETH 지갑으로 {$amount} 입금되었습니다.");
                            }
                        }
                    }
                }

            }
            $setting->last_blocks = $blockNum;
            $setting->save();
        }
    }

    public function aos()
    {
        $client = new EOSClient('http://api.aos.plus:8888');

        $info = $client->chain()->getInfo();
        $setting = AosSetting::first();

        if($setting->last_blocks == null)
        {
            $setting->last_blocks = $info->headBlockNum();
            $setting->save();

            return;
        }

        $newBlock = $info->headBlockNum();

        for($i = $setting->last_blocks; $i < $newBlock; $i++)
        {
            $blockNum = $i + 1;
            $block = $client->chain()->getBlock($blockNum);

            for($j = 0; $j < count($block->transactions()); $j++)
            {
                $item = $block->transactions()[$j];
                for($k = 0; $k < count($item->trx->transaction->actions); $k++)
                {
                    $action = $item->trx->transaction->actions[$k];
                    if($action->name == "transfer")
                    {
                        if($action->data->to == env('AOS_WITHDRAW_ADDR'))
                        {
                            $user = User::where('referer_code', $action->data->memo)->first();

                            if($user != null)
                            {
                                $amount = (double)substr($action->data->quantity, 0, -4);

                                $user->point_3 = $user->point_3 + $amount;
                                $user->save();

                                Transaction::create([
                                    'user_id' => $user->id,
                                    'coin_id' => 2,
                                    'type' => 0,
                                    'amount' => $amount,
                                    'tx' => $item->trx->id,
                                    'status' => 1
                                ]);

                                $clients = new Client();
                                $clients->request('GET', "https://api.telegram.org/bot1921486506:AAHmOlXkbGj01L6sRsDxFzwk68YVHMatsQ8/sendMessage?chat_id=-1001469638282&text={$user->username}님의 AOS 지갑으로 {$amount} 입금되었습니다.");

                                $purchase = Purchase::where('user_id', $user->id)->orderby('id', 'desc')->first();
                                if($purchase != null) {
                                    $this->executePurchase($purchase);
                                }
                            }
                        }
                    }
                }
            }

            $setting->last_blocks = $blockNum;
            $setting->save();
        }
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

    private function wei2eth($wei)
    {
        return bcdiv($wei,1000000000000000000,18);
    }

    private function hex2str($hex) {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
    }

    private function bchexdec($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }
}
