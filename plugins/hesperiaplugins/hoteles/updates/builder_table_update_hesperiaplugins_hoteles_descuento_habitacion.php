<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDescuentoHabitacion extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_descuento_habitacion', function($table)
        {
            $table->integer('cantidad')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_descuento_habitacion', function($table)
        {
            $table->dropColumn('cantidad');
        });
    }
}
