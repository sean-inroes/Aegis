<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardArticleParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_article_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_article_id')->constrained('board_articles')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->string('name')->nullable();
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
        Schema::dropIfExists('board_article_parameters');
    }
}
