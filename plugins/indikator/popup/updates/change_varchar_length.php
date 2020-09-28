<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class ChangeVarcharLength extends Migration
{
    public function up()
    {
        Schema::table('indikator_popup_campaigns', function($table)
        {
            $table->string('comment', 191)->change();
        });

        Schema::table('indikator_popup_themes', function($table)
        {
            $table->string('comment', 191)->change();
        });
    }

    public function down()
    {
        Schema::table('indikator_popup_campaigns', function($table)
        {
            $table->string('comment', 250)->change();
        });

        Schema::table('indikator_popup_themes', function($table)
        {
            $table->string('comment', 250)->change();
        });
    }
}
