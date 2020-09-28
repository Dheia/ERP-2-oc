<?php namespace Placecompany\Erp\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlacecompanyErpService extends Migration
{
    public function up() { Schema::create('placecompany_erp_service', function($table) { $table->engine = 'InnoDB'; $table->increments('id')->unsigned(); $table->text('service_name'); $table->integer('base_price')->default(0); $table->integer('min_down_payment')->default(0); $table->string('status', 2); }); } public function down() { Schema::dropIfExists('placecompany_erp_service'); }
}
