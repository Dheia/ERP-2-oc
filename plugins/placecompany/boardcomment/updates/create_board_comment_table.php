<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Create_board_comment_table extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_comment', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_display', 127)->nullable();
            $table->longText('content');
            $table->unsignedInteger('like')->nullable();
            $table->unsignedInteger('unlike')->nullable();
            $table->unsignedInteger('vote')->nullable();
            $table->string('status', 20)->nullable();
            $table->string('password', 127)->nullable();
            $table->timestamps();

            $table->index('content_id');
            $table->index('parent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_comment');
    }
}
