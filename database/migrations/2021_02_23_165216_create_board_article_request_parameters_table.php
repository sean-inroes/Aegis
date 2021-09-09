<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardArticleRequestParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_article_request_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('board_article_request_id');
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->string('label')->nullable();
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
        Schema::dropIfExists('board_article_request_parameters');
    }
}
