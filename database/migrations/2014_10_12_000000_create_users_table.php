<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('nickname')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('birthday')->nullable();
            $table->tinyInteger('level')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('password');
            $table->string('withdraw_password')->nullable();
            $table->double('point_1')->default(0);
            $table->double('point_2')->default(0);
            $table->double('point_3')->default(0);
            $table->double('point_4')->default(0);
            $table->string('referer_code')->unique();
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
