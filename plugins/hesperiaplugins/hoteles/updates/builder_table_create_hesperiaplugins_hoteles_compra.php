<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesCompra extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_compra', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('comprable_id');
            $table->string('comprable_type', 150);
            $table->integer('usuario_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->text('comentario');
            $table->integer('pago_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_compra');
    }
}
