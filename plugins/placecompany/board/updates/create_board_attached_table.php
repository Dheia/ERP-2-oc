<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Create_board_attached_table extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_attached', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('comment_id');
            $table->string('file_key', 127);
            $table->string('file_path', 127);
            $table->string('file_name', 127);
            $table->unsignedInteger('file_size');
            $table->unsignedInteger('download_count');
            $table->unsignedInteger('metadata');
            $table->timestamps();


            $table->index('content_id');
            $table->index('comment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_attached');
    }
}
