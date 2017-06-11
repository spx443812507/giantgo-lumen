<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_entity', function (Blueprint $table) {
            $table->increments('entity_id');
            $table->integer('entity_type_id');
            $table->integer('attribute_set_id');
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->string('password');
            $table->boolean('verified_email');
            $table->boolean('verified_mobile');
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active');
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
        Schema::drop('customer_entity');
    }
}
