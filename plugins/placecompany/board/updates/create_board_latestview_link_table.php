<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardLatestViewLinkTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_latestview_link', function($table)
        {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('latestview_id');
            $table->unsignedBigInteger('board_id');

            $table->unique(['latestview_id', 'board_id'], 'latestview_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_latestview_link');
    }
}
