<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('type')->default(0)->comment('0 => 사진형, 1 => 기본형');
            $table->boolean('reply')->default(0)->comment('0 => 미사용, 1 => 사용');
            $table->boolean('parameter')->default(0)->comment('0 => 미사용, 1 => 사용');
            $table->boolean('category')->default(0)->comment('0 => 미사용, 1 => 사용');
            $table->boolean('single')->default(0)->comment('0 => 미사용, 1 => 사용');
            $table->boolean('write')->default(0)->comment('0 =>  글쓰기 비허용, 1 => 글쓰기 허용');
            $table->boolean('guest')->default(0)->comment('0 =>  비회원 비허용, 1 => 비회원 허용');
            $table->boolean('status')->default(1)->comment('0 => 미사용, 1 => 사용');
            $table->boolean('lock')->default(0)->comment('0 => 삭제가능, 1 => 삭제못함');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boards');
    }
}
