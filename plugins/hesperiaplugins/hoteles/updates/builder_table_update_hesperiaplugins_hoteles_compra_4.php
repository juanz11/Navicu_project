<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCompra4 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->decimal('total', 10, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->decimal('total', 10, 0)->change();
        });
    }
}
