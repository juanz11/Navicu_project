<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCompra extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->integer('status_id');
            $table->decimal('total', 10, 0);
            $table->integer('moneda_id');
            $table->string('nombre_cliente', 250);
            $table->integer('origen_id');
            $table->string('contacto', 100);
            $table->renameColumn('comprable_type', 'identificacion');
            $table->dropColumn('comprable_id');
            $table->dropColumn('pago_id');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->dropColumn('status_id');
            $table->dropColumn('total');
            $table->dropColumn('moneda_id');
            $table->dropColumn('nombre_cliente');
            $table->dropColumn('origen_id');
            $table->dropColumn('contacto');
            $table->renameColumn('identificacion', 'comprable_type');
            $table->integer('comprable_id');
            $table->integer('pago_id');
        });
    }
}
