<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesStatusLead extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_status_lead', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('status', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_status_lead');
    }
}
