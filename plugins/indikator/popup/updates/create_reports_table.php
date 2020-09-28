<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('indikator_popup_reports', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('day');
            $table->string('campaign', 4)->default(0);
            $table->unsignedInteger('view');
            $table->unsignedInteger('action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_popup_reports');
    }
}
