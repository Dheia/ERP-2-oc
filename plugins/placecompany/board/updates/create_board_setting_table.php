<?php namespace Placecompany\Board\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardSettingTable extends Migration
{
    public function up()
    {
        Schema::create('placecompany_board_setting', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('board_name', 127);
            $table->string('skin', 127);
            $table->string('use_comment', 5);
            $table->string('use_editor', 20);
            $table->string('permission_read', 127);
            $table->string('permission_write', 127);
            $table->text('admin_user');
            $table->string('use_category', 5);
            $table->text('category1_list');
            $table->text('category2_list');
            $table->smallInteger('page_rpp');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('placecompany_board_setting');
    }
}
