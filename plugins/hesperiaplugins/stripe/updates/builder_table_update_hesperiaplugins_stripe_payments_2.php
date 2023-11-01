<?php namespace hesperiaplugins\Stripe\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsStripePayments2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_stripe_payments', function($table)
        {
            $table->text('response');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_stripe_payments', function($table)
        {
            $table->dropColumn('response');
        });
    }
}
