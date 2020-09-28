<?php namespace Placecompany\Erp\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlacecompanyErpSign extends Migration
{
    public function up() { Schema::create('placecompany_erp_sign', function($table) { $table->engine = 'InnoDB'; $table->increments('id')->unsigned(); $table->string('customer_company_id', 20); $table->integer('user_id')->unsigned(); $table->dateTime('contract_date'); $table->smallInteger('period'); $table->integer('payment'); $table->integer('customer_id'); $table->timestamp('created_at')->nullable(); $table->timestamp('updated_at')->nullable(); $table->string('sign_status', 2); }); } public function down() { Schema::dropIfExists('placecompany_erp_sign'); }
}
