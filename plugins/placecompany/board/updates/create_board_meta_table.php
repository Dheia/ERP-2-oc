<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardMetaTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_meta', function($table)
        {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('board_id');
            $table->string('key', 127);
            $table->longText('value')->default(NULL);

            $table->primary(['board_id', 'key'], 'meta_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_meta');
    }
}
