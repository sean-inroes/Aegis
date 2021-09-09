<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardArticleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_article_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards')->onDelete('cascade');
            $table->foreignId('board_article_id')->constrained('board_articles')->onDelete('cascade');
            $table->string('site')->nullable();
            $table->string('nickname')->nullable();
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('board_article_requests');
    }
}
