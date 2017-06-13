<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_attribute', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id');
            $table->integer('attribute_set_id');
            $table->integer('attribute_group_id');
            $table->integer('attribute_id');
            $table->integer('sort_order');
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
        Schema::drop('entity_attribute');
    }
}
