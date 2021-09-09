<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')->nullable()->constrained('rewards');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases');
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
        Schema::dropIfExists('reward_units');
    }
}
