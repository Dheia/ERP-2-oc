<?php namespace Placecompany\Erp\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlacecompanyErpCustomerCompany extends Migration
{
    public function up() { Schema::create('placecompany_erp_customer_company', function($table) { $table->engine = 'InnoDB'; $table->increments('id')->unsigned(); $table->text('company_name'); $table->text('business_number'); $table->text('fax_number'); $table->smallInteger('zip_code'); $table->text('address'); $table->text('address_sub'); $table->text('company_tel'); $table->timestamp('created_at')->nullable(); $table->timestamp('updated_at')->nullable(); }); } public function down() { Schema::dropIfExists('placecompany_erp_customer_company'); }
}
