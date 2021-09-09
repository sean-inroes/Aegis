<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->text('in_description')->nullable();
            $table->text('out_description')->nullable();
            $table->string('url')->nullable();
            $table->string('code')->nullable();
            $table->string('in_thumbnail')->nullable();
            $table->string('out_thumbnail')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedInteger('sticker')->default(0);
            $table->unsignedInteger('order')->nullable();
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('sites');
    }
}
