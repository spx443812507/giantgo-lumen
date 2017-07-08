<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeminarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seminars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id')->nullable();
            $table->integer('user_id');
            $table->string('title');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('closing_date')->nullable();
            $table->boolean('need_audit')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create table for associating speaker to agenda (Many-to-Many)
        Schema::create('seminar_user', function (Blueprint $table) {
            $table->integer('seminar_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('seminar_id')->references('id')->on('seminars')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['seminar_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('seminar_user');
        Schema::drop('seminars');
    }
}
