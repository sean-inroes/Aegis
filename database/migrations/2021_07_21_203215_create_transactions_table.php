<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('coin_id')->nullable()->constrained('coins');
            $table->unsignedInteger('type')->comment('0 => 입금, 1 => 출금');
            $table->double('amount')->default(0);
            $table->double('fee')->default(0);
            $table->double('real_amount')->default(0);
            $table->string('to_addr')->nullable();
            $table->string('tx')->nullable();
            $table->unsignedInteger('status')->default(0)->comment('0 => 처리중, 1 => 처리완료, 2 => 처리실패');
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
        Schema::dropIfExists('transactions');
    }
}
