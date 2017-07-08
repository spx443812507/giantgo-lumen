<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id')->nullable();
            $table->integer('user_id');
            $table->string('title');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create table for associating speaker to agenda (Many-to-Many)
        Schema::create('agenda_speaker', function (Blueprint $table) {
            $table->integer('agenda_id')->unsigned();
            $table->integer('speaker_id')->unsigned();

            $table->foreign('agenda_id')->references('id')->on('agendas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('speaker_id')->references('id')->on('speakers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['agenda_id', 'speaker_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('agenda_speaker');
        Schema::drop('agendas');
    }
}
