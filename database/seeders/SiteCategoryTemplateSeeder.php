<?php

namespace Database\Seeders;

use App\Models\SiteCategoryTemplate;
use Illuminate\Database\Seeder;

class SiteCategoryTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteCategoryTemplate::create([
            'type' => 0,
            'name' => '이용 규정',
            'icon' => 'camera',
        ]);

        SiteCategoryTemplate::create([
            'type' => 0,
            'name' => '스포츠 규정',
            'icon' => 'sports',
        ]);

        SiteCategoryTemplate::create([
            'type' => 0,
            'name' => '미니게임 규정',
            'icon' => 'sports_esports',
        ]);

        SiteCategoryTemplate::create([
            'type' => 0,
            'name' => '카지노 규정',
            'icon' => 'casino',
        ]);

        SiteCategoryTemplate::create([
            'type' => 0,
            'name' => '사이트 이벤트',
            'icon' => 'event',
        ]);

        SiteCategoryTemplate::create([
            'type' => 1,
            'name' => '사이트 이미지',
            'icon' => 'image',
        ]);
    }
}
