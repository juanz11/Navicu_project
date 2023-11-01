<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCompra5 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->string('codigo', 25);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->dropColumn('codigo');
        });
    }
}
