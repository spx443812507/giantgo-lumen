<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id');
            $table->string('attribute_code');
            $table->string('attribute_model')->nullable();
            $table->string('backend_model')->nullable();
            $table->string('backend_type');
            $table->string('backend_table')->nullable();
            $table->string('frontend_model')->nullable();
            $table->string('frontend_input');
            $table->string('frontend_label');
            $table->string('frontend_class')->nullable();
            $table->boolean('is_required');
            $table->boolean('is_user_defined');
            $table->boolean('is_unique');
            $table->string('default')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attributes');
    }
}
