<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesReservasLead extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_reservas_lead', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('reserva_id');
            $table->integer('user_id');
            $table->integer('status_lead');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_reservas_lead');
    }
}
