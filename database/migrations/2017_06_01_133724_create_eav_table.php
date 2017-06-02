<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEavTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eav_entity_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entity_type_code');
            $table->string('entity_model');
            $table->string('attribute_model');
            $table->string('entity_table');
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
        Schema::drop('eav_entity_type');
    }
}
