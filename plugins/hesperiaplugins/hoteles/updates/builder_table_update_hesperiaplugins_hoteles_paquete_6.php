<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaquete6 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->integer('porcentaje')->default(0)->change();
            $table->integer('ind_activo')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->integer('porcentaje')->default(null)->change();
            $table->integer('ind_activo')->default(null)->change();
        });
    }
}
