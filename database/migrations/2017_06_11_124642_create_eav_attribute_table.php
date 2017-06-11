<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEavAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eav_attribute', function (Blueprint $table) {
            $table->increments('attribute_id');
            $table->integer('entity_type_id');
            $table->string('attribute_code');
            $table->string('attribute_model');
            $table->string('backend_model');
            $table->string('backend_type');
            $table->string('backend_table');
            $table->string('frontend_model');
            $table->string('frontend_input');
            $table->string('frontend_label');
            $table->string('frontend_class');
            $table->boolean('is_required');
            $table->boolean('is_user_defined');
            $table->string('default_value');
            $table->boolean('is_unique');
            $table->string('note');
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
        Schema::drop('eav_attribute');
    }
}
