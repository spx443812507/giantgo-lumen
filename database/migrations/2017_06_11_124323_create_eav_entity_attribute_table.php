<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEavEntityAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eav_entity_attribute', function (Blueprint $table) {
            $table->increments('entity_attribute_id');
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
        Schema::drop('eav_entity_attribute');
    }
}
