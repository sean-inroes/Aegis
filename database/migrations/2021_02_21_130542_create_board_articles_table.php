<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('board_id')->constrained('boards')->onDelete('cascade');
            $table->foreignId('board_category_id')->nullable()->constrained('board_categories')->onDelete('cascade');
            $table->string('nickname')->nullable();
            $table->string('password')->nullable();
            $table->string('name')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->unsignedInteger('started_at')->nullable();
            $table->unsignedInteger('ended_at')->nullable();
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
        Schema::dropIfExists('board_articles');
    }
}
