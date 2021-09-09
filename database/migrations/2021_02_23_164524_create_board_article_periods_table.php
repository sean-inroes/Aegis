<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardArticlePeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_article_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_article_id')->constrained('board_articles')->onDelete('cascade');
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('from')->nullable();
            $table->tinyInteger('to')->nullable();
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
        Schema::dropIfExists('board_article_periods');
    }
}
