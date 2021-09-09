<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnpaidRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unpaid_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases');
            $table->double('price')->nullable();
            $table->double('paid')->nullable();
            $table->double('unpaid')->nullable();
            $table->double('fee')->nullable();
            $table->unsignedInteger('type')->nullable();
            $table->unsignedInteger('status')->nullable();
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
        Schema::dropIfExists('unpaid_rewards');
    }
}
