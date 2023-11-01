<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesTipoPago extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_tipo_pago', function($table)
        {
            $table->string('codigo', 150)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_tipo_pago', function($table)
        {
            $table->dropColumn('codigo');
        });
    }
}
