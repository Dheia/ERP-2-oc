<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardMediaRelationshipsTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_media_relationships', function($table)
        {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('media_id');

            $table->index('content_id', 'content_id_2');
            $table->index('comment_id');
            $table->index('media_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_media_relationships');
    }
}
