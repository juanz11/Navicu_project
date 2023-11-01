<?php namespace navicudev\Emailnotificacion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateNavicudevEmailnotificacion extends Migration
{
    public function up()
    {
        Schema::create('navicudev_emailnotificacion_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('role')->nullable();
            $table->text('email');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('navicudev_emailnotificacion_');
    }
}
