<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardOptionTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_option', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('content_id');
            $table->string('option_key', 127);
            $table->longText('option_value');

            $table->index('content_id');
            $table->index('option_key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_option');
    }
}
