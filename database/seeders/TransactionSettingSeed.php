<?php

namespace Database\Seeders;

use App\Models\TransactionSetting;
use Illuminate\Database\Seeder;

class TransactionSettingSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransactionSetting::create([
            'withdraw_fee' => 3
        ]);
    }
}
