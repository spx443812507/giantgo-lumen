<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_group', function (Blueprint $table) {
            $table->increments('id');
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
        Schema::drop('attribute_group');
    }
}
