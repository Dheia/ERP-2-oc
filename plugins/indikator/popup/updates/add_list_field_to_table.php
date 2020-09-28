<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddListFieldToTable extends Migration
{
    public function up()
    {
        Schema::table('indikator_popup_campaigns', function($table)
        {
            $table->string('campaign_list', 3)->default(0);
        });
    }

    public function down()
    {
        Schema::table('indikator_popup_campaigns', function($table)
        {
            $table->dropColumn('campaign_list');
        });
    }
}
