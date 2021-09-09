<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Package::create([
            'user_id' => 1,
            'price' => 500,
            'bp' => 1,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 1000,
            'bp' => 2,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 2000,
            'bp' => 4,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 5000,
            'bp' => 10,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 8000,
            'bp' => 16,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 16000,
            'bp' => 32,
            'status' => 1
        ]);

        Package::create([
            'user_id' => 1,
            'price' => 24000,
            'bp' => 48,
            'status' => 1
        ]);
    }
}
