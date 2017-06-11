<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEavAttributeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eav_attribute_group', function (Blueprint $table) {
            $table->increments('attribute_group_id');
            $table->integer('attribute_set_id');
            $table->string('attribute_group_name');
            $table->integer('sort_order');
            $table->integer('attribute_id');
            $table->integer('default_id');
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
        Schema::drop('eav_attribute_group');
    }
}
