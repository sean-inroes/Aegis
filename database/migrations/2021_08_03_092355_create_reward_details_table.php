<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')->nullable()->constrained('rewards');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->double('left_revenue')->default(0);
            $table->double('rihgt_revenue')->default(0);
            $table->double('current_left_bp')->default(0);
            $table->double('current_right_bp')->default(0);
            $table->double('after_left_bp')->default(0);
            $table->double('after_right_bp')->default(0);
            $table->unsignedInteger('status')->default(0);
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
        Schema::dropIfExists('reward_details');
    }
}
