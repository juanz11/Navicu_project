<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCompra3 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->integer('pago_insite')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->dropColumn('pago_insite');
        });
    }
}
