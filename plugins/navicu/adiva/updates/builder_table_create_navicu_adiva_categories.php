<?php namespace Navicu\Adiva\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateNavicuAdivaCategories extends Migration
{
    public function up()
    {
        Schema::create('navicu_adiva_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 150);
            $table->string('slug', 150);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('navicu_adiva_categories');
    }
}
