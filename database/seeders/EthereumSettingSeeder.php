<?php

namespace Database\Seeders;

use App\Models\EthereumSetting;
use Illuminate\Database\Seeder;

class EthereumSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EthereumSetting::create([
            'last_blocks' => null
        ]);
    }
}
