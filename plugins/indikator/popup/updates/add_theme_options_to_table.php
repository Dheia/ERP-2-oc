<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddThemeOptionsToTable extends Migration
{
    public function up()
    {
        Schema::table('indikator_popup_themes', function($table)
        {
            $table->string('speed', 5)->default(400);
            $table->string('timing', 1)->default(0);
            $table->string('open', 5)->default(0);
        });
    }

    public function down()
    {
        Schema::table('indikator_popup_themes', function($table)
        {
            $table->dropColumn('speed');
            $table->dropColumn('timing');
            $table->dropColumn('open');
        });
    }
}
