<?php namespace Indikator\Popup\Updates;

use October\Rain\Database\Updates\Migration;
use DbDongle;

class UpdateTimestampsNullable extends Migration
{
    public function up()
    {
        DbDongle::disableStrictMode();

        DbDongle::convertTimestamps('indikator_popup_campaigns');
        DbDongle::convertTimestamps('indikator_popup_abtest');
        DbDongle::convertTimestamps('indikator_popup_themes');
        DbDongle::convertTimestamps('indikator_popup_reports');
        DbDongle::convertTimestamps('indikator_popup_abtest_reports');
    }

    public function down()
    {
        // ...
    }
}
