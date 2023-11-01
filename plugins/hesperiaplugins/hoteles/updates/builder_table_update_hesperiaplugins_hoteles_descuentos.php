<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDescuentos extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->dropColumn('fecha_desde');
            $table->dropColumn('fecha_hasta');
        });
    }
}
