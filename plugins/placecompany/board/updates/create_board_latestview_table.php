<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardLatestViewTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_latestview', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 127);
            $table->string('skin', 127);
            $table->unsignedInteger('rpp');
            $table->string('sort', 20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_latestview');
    }
}
