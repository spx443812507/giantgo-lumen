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
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('closed_at')->nullable();
            $table->boolean('need_audit')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('password');
            $table->boolean('verified_email');
            $table->boolean('verified_mobile');
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create table for associating speaker to agenda (Many-to-Many)
        Schema::create('seminar_contact', function (Blueprint $table) {
            $table->integer('seminar_id')->unsigned();
            $table->integer('contact_id')->unsigned();

            $table->foreign('seminar_id')->references('id')->on('seminars')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['seminar_id', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('seminar_contact');
        Schema::drop('seminars');
        Schema::drop('contacts');
    }
}
