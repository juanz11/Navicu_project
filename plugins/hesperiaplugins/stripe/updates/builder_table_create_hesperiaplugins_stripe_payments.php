<?php namespace hesperiaplugins\Stripe\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsStripePayments extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_stripe_payments', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('description', 250);
            $table->double('amount', 10, 2);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_stripe_payments');
    }
}
