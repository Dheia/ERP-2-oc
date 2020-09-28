<?php namespace Placecompany\Erp\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlacecompanyErpSignService extends Migration
{
    public function up() { Schema::create('placecompany_erp_sign_service', function($table) { $table->engine = 'InnoDB'; $table->increments('id')->unsigned(); $table->integer('sign_id'); $table->integer('sign_seller_id'); $table->integer('service_id'); $table->timestamp('created_at')->nullable(); $table->timestamp('updated_at')->nullable(); }); } public function down() { Schema::dropIfExists('placecompany_erp_sign_service'); }
}
