<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardMediaTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_media', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('media_group', 127);
            $table->string('file_path', 127);
            $table->string('file_name', 127);
            $table->UnsignedbigInteger('file_size');
            $table->Unsignedinteger('download_count');
            $table->longText('metadata');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_media');
    }
}
