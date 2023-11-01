<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesCalendario extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_calendario', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('calendarizable_id');
            $table->string('calendarizable_type', 150);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_calendario');
    }
}
