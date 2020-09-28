<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Create_board_vote_table extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_vote', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('target_id');
            $table->string('target_type', 20);
            $table->string('target_vote', 20);
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 127);
            $table->timestamps();

            $table->index('target_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_vote');
    }
}
