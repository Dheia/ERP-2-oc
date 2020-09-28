<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAbtestReportsTable extends Migration
{
    public function up()
    {
        Schema::create('indikator_popup_abtest_reports', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('day');
            $table->string('abtest', 4)->default(0);
            $table->unsignedInteger('view_a');
            $table->unsignedInteger('view_b');
            $table->unsignedInteger('action_a');
            $table->unsignedInteger('action_b');
            $table->unsignedInteger('code_a');
            $table->unsignedInteger('code_b');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_popup_abtest_reports');
    }
}
