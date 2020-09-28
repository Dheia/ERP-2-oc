<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardContentTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_content', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('parent_id')->default(NULL);
            $table->unsignedBigInteger('user_id')->default(NULL);
            $table->string('user_display', 127)->default(NULL);
            $table->string('title', 127);
            $table->longText('content');
            $table->unsignedInteger('view')->default(NULL);
            $table->unsignedInteger('comment')->default(NULL);
            $table->unsignedInteger('like')->default(NULL);
            $table->unsignedInteger('unlike')->default(NULL);
            $table->integer('vote')->default(NULL);
            $table->string('thumbnail_file', 127)->default(NULL);
            $table->string('thumbnail_name', 127)->default(NULL);
            $table->string('category1', 127)->default(NULL);
            $table->string('category2', 127)->default(NULL);
            $table->string('secret', 5)->default(NULL);
            $table->string('notice', 5)->default(NULL);
            $table->char('search', 1)->default(NULL);
            $table->string('status', 20)->default(NULL);
            $table->string('password', 127)->default(NULL);
            $table->timestamps();

            $table->index('board_id');
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('view');
            $table->index('vote');
            $table->index('category1');
            $table->index('category2');
            $table->index('notice');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_content');
    }
}
