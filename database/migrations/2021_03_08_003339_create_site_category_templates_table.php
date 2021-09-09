<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteCategoryTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_category_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('type')->default(0)->comment('0 => quill editor, 1 => dropzone');
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
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
        Schema::dropIfExists('site_category_templates');
    }
}
