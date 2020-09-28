<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('indikator_popup_campaigns', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 100);
            $table->string('comment', 250);
            $table->string('pages_all', 1)->default(1);
            $table->text('pages_selected');
            $table->string('integration', 1)->default(1);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('theme', 3)->default(0);
            $table->string('status', 1)->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_popup_campaigns');
    }
}
