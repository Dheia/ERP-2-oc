<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAbtestTable extends Migration
{
    public function up()
    {
        Schema::create('indikator_popup_abtest', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('campaign', 4)->default(0);
            $table->text('result');
            $table->string('theme_a', 3)->default(1);
            $table->string('theme_b', 3)->default(1);
            $table->string('code_a', 40);
            $table->string('code_b', 40);
            $table->string('status', 1)->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_popup_abtest');
    }
}
