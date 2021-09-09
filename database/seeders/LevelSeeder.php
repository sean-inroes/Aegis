<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Level::create([
            'level' => 0,
            'label' => "비회원"
        ]);

        Level::create([
            'level' => 1,
            'label' => "멤버"
        ]);

        Level::create([
            'level' => 2,
            'label' => "비기너"
        ]);

        Level::create([
            'level' => 3,
            'label' => "매니저"
        ]);

        Level::create([
            'level' => 4,
            'label' => "디렉터"
        ]);

        Level::create([
            'level' => 5,
            'label' => "어시스터"
        ]);

        Level::create([
            'level' => 6,
            'label' => "캡틴"
        ]);

        Level::create([
            'level' => 7,
            'label' => "마스터"
        ]);
    }
}
