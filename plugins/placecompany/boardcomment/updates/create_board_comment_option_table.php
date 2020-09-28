<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Create_board_comment_option_table extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_comment_option', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('comment_id')->unsigned();
            $table->string('option_key', 127);
            $table->text('option_value', 127);

            $table->unique(['comment_id','option_key'], 'comment_id');
            $table->index('option_key');
        });

        Schema::table('placecompany_board_comment_option', function($table) {
            $table->foreign('comment_id')
                ->references('id')
                ->on('placecompany_board_comment')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_comment_option');
    }
}
