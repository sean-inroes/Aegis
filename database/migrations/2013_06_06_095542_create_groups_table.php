<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('groups');
            $table->unsignedInteger('label')->default(0);
            $table->unsignedInteger('layer')->default(0);
            $table->unsignedInteger('type')->default(0);
            $table->unsignedInteger('fee')->default(0);
            $table->unsignedInteger('member')->default(0);
            $table->double('revenue')->default(0);
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
        Schema::dropIfExists('groups');
    }
}
