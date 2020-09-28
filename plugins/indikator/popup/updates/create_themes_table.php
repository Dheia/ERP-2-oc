<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateThemesTable extends Migration
{
    public function up()
    {
        Schema::create('indikator_popup_themes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 100);
            $table->string('comment', 250);
            $table->longtext('html');
            $table->longtext('css');
            $table->string('style', 1)->default(1);
            $table->string('animation', 1)->default(1);
            $table->string('status', 1)->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_popup_themes');
    }
}
