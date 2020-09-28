<?php namespace Placecompany\Erp\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlacecompanyErpCustomer extends Migration
{
    public function up() { Schema::create('placecompany_erp_customer', function($table) { $table->engine = 'InnoDB'; $table->increments('id')->unsigned(); $table->string('name', 20); $table->string('email', 50); $table->string('phone_number', 20); $table->timestamp('created_at')->nullable(); $table->timestamp('updated_at')->nullable(); }); } public function down() { Schema::dropIfExists('placecompany_erp_customer'); }
}
