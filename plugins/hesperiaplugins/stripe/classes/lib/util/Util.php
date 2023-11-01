<?php
namespace HesperiaPlugins\Stripe\Classes\lib\Util;

use HesperiaPlugins\Stripe\Classes\lib\StripeObject;

abstract class Util
{
    private static $isMbstringAvailable = null;
    private static $isHashEqualsAvailable = null;

    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }

      // TODO: generally incorrect, but it's correct given Stripe's response
        foreach (array_keys($array) as $k) {
            if (!is_numeric($k)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively converts the PHP Stripe object to an array.
     *
     * @param array $values The PHP Stripe object to convert.
     * @return array
     */
    public static function convertStripeObjectToArray($values)
    {
        $results = array();
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof StripeObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertStripeObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the Stripe API to the corresponding PHP object.
     *
     * @param array $resp The response from the Stripe API.
     * @param array $opts
     * @return StripeObject|array
     */
    public static function convertToStripeObject($resp, $opts)
    {
        $types = array(
            'account' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Account',
            'alipay_account' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\AlipayAccount',
            'apple_pay_domain' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\ApplePayDomain',
            'bank_account' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\BankAccount',
            'balance_transaction' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\BalanceTransaction',
            'card' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Card',
            'charge' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Charge',
            'country_spec' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\CountrySpec',
            'coupon' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Coupon',
            'customer' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Customer',
            'dispute' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Dispute',
            'ephemeral_key' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\EphemeralKey',
            'list' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Collection',
            'login_link' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\LoginLink',
            'invoice' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Invoice',
            'invoiceitem' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\InvoiceItem',
            'event' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Event',
            'file_upload' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\FileUpload',
            'token' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Token',
            'transfer' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Transfer',
            'transfer_reversal' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\TransferReversal',
            'order' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Order',
            'order_return' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\OrderReturn',
            'payout' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Payout',
            'plan' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Plan',
            'product' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Product',
            'recipient' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Recipient',
            'recipient_transfer' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\RecipientTransfer',
            'refund' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Refund',
            'sku' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\SKU',
            'source' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Source',
            'subscription' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\Subscription',
            'subscription_item' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\SubscriptionItem',
            'three_d_secure' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\ThreeDSecure',
            'fee_refund' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\ApplicationFeeRefund',
            'bitcoin_receiver' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\BitcoinReceiver',
            'bitcoin_transaction' => 'HesperiaPlugins\\Stripe\\Classes\\lib\\BitcoinTransaction',
        );
        if (self::isList($resp)) {
            $mapped = array();
            foreach ($resp as $i) {
                array_push($mapped, self::convertToStripeObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']])) {
                $class = $types[$resp['object']];
            } else {
                $class = 'HesperiaPlugins\Stripe\Classes\lib\StripeObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            if (!self::$isMbstringAvailable) {
                trigger_error("It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@stripe.com if you have any questions.", E_USER_WARNING);
            }
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    public static function secureCompare($a, $b)
    {
        if (self::$isHashEqualsAvailable === null) {
            self::$isHashEqualsAvailable = function_exists('hash_equals');
        }

        if (self::$isHashEqualsAvailable) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }

    /**
     * @param array $arr A map of param keys to values.
     * @param string|null $prefix
     *
     * @return string A querystring, essentially.
     */
    public static function urlEncode($arr, $prefix = null)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v)) {
                continue;
            }

            if ($prefix) {
                if ($k !== null && (!is_int($k) || is_array($v))) {
                    $k = $prefix."[".$k."]";
                } else {
                    $k = $prefix."[]";
                }
            }

            if (is_array($v)) {
                $enc = self::urlEncode($v, $k);
                if ($enc) {
                    $r[] = $enc;
                }
            } else {
                $r[] = urlencode($k)."=".urlencode($v);
            }
        }

        return implode("&", $r);
    }
}
