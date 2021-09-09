<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UserSeeder::class);
        $this->call(BoardSeeder::class);
        $this->call(SiteCategoryTemplateSeeder::class);
        $this->call(PackageSeeder::class);
        $this->call(EthereumSettingSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(LevelSeeder::class);
        $this->call(TransactionSettingSeed::class);
        $this->call(AosSettingSeeder::class);
        $this->call(CoinSeeder::class);
    }
}
