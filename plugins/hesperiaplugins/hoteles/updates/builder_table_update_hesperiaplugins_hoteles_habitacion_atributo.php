<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHabitacionAtributo extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_habitacion_atributo', function($table)
        {
            $table->integer('orden');
            $table->primary(['habitacion_id','atributo_id'], "habitacion_atributo_pk");
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_habitacion_atributo', function($table)
        {
            $table->dropPrimary(['habitacion_id','atributo_id']);
            $table->dropColumn('orden');
        });
    }
}
