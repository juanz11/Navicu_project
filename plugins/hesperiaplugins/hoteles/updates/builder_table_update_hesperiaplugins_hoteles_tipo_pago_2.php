<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesTipoPago2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_tipo_pago', function($table)
        {
            $table->integer('activo')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_tipo_pago', function($table)
        {
            $table->dropColumn('activo');
        });
    }
}
