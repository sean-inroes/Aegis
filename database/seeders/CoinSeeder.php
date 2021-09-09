<?php

namespace Database\Seeders;

use App\Models\Coin;
use Illuminate\Database\Seeder;

class CoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Coin::create([
            'name' => 'Tether USD',
            'symbol' => 'USDT',
            'explorer_url' => 'https://etherscan.io/tx/'
        ]);

        Coin::create([
            'name' => 'AOS',
            'symbol' => 'AOS',
            'explorer_url' => 'http://block.aos.plus/#/transaction/'
        ]);
    }
}
