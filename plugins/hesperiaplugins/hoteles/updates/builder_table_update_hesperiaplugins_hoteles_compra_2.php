<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCompra2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->dateTime('fecha_vigencia');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->dropColumn('fecha_vigencia');
        });
    }
}
