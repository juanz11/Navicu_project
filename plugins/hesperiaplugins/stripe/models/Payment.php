<?php namespace hesperiaplugins\Stripe\Models;

use Model;

/**
 * Model
 */
class Payment extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_stripe_payments';

    public $jsonable = ['response'];
}