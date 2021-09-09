<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Board::create([
            'user_id' => 1,
            'name' => '이벤트',
            'url' => 'event',
            'type' => 0,
            'reply' => 1,
            'parameter'=> 1,
            'category'=> 0,
            'single'=> 1,
            'lock' => 1
        ]);

        Board::create([
            'user_id' => 1,
            'name' => '공지사항',
            'url' => 'notice',
            'type' => 1,
            'reply' => 1,
            'parameter'=> 1,
            'category' => 0,
            'single'=> 1,
            'lock' => 1,
        ]);

        Board::create([
            'user_id' => 1,
            'name' => '1대1 문의',
            'url' => 'question',
            'type' => 1,
            'reply' => 1,
            'parameter'=> 0,
            'category' => 0,
            'single'=> 1,
            'lock' => 1,
        ]);
    }
}
