<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesDatosBancarios extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_datos_bancarios', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('hotel_id');
            $table->string('banco', 255);
            $table->string('beneficiario', 255);
            $table->string('cuenta', 255);
            $table->string('email', 255);
            $table->string('rif', 255)->nullable();
            $table->string('swift', 255)->nullable();
            $table->string('nacional', 1)->nullable(false)->unsigned(false)->default(null);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_datos_bancarios');
    }
}
