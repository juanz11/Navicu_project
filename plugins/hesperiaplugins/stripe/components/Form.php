<?php namespace HesperiaPlugins\Stripe\Components;

use Cms\Classes\ComponentBase;
use Input;
use Crypt;
use \HesperiaPlugins\Stripe\classes\lib\Stripe;
use \HesperiaPlugins\Stripe\classes\lib\Error\Card;
use \HesperiaPlugins\Stripe\classes\lib\Charge;
use \HesperiaPlugins\Stripe\Models\Settings;
use \HesperiaPlugins\Stripe\Models\Payment;
use Illuminate\Contracts\Encryption\DecryptException;

class Form extends ComponentBase{

  public function defineProperties(){
    return [
      'mount' => [
        'title' => 'Mount',
        'description' => 'Mount',
        'type' => 'text',
      ],
      'description' => [
        'title' => 'Description',
        'description' => 'Description',
        'type' => 'string',
      ],
      'type' =>[
        'title' => 'Type',
        'description' => 'Type',
        'type' => 'string'
      ],
      'ref' => [
        'title' => 'Ref',
        'description' => 'Referencia',
        'type' => 'string'
      ]
    ];
  }

  public function componentDetails(){
    return [
      'name'=> 'Form',
      'description' => 'Form Stripe'
    ];
  }
  function onRun(){
      //"<script src='https://js.stripe.com/v3/'></script>",
      $this->addJs('assets/js/stripe-config.js');

  }
  public function onPayStripe(){
    
    $data = Input::get();
    $settings = Settings::instance();
    
    Stripe::setApiKey($settings->api_key);

    if ($settings->encrypt_mount) {
      try {
        $decrytMount = Crypt::decrypt($data["mt"]);
        $mount = str_replace(" ", "", $decrytMount);
      }
      catch (DecryptException $ex) {
          trace_log($ex);
      }
    }else{
      $mount = $data["mt"];
    }

    $newMount = $mount*100;
    $charge = array(
      "amount" => $newMount,
      "currency" => "usd",
      "description" => "Navicu.com ".$data["ref"],
      "source" => $data["token"],
      "metadata" => [
        "tarjetahabiente" => $data["owner"],
        "Referencia" => $data["ref"]
      ]
    );

    $payment = new Payment();
    $payment->description = "Navicu.com ".$data["ref"];
    $payment->amount = $mount;

    
    $retorno = null;
    try {
      $retorno = \HesperiaPlugins\Stripe\classes\lib\Charge::create($charge);
      
      $payment->message=$retorno->status;

    } catch (Card $e) {
      $array = json_decode(json_encode($e->jsonBody), true);
      //var_dump($array);
      $payment->message=$array["error"]["message"];

      $retorno = $array["error"];
    }
    
    $payment->response = $retorno;
    $payment->save();

    $retorno["reference"] = $payment->id;
    $retorno["object_id"] = $data["id"];
    $retorno["object_type"] = $data["type"];
    $response = json_encode($retorno);
    
    //trace_log($payment);
    //
    return $response;//RETORNO QUE SIEMPRE HEMOS USADO
    //var_dump($cnse;//RETORNO QUE SIEMPRE HEMOS USADO
    //var_dump($charge);
  }

  public function encrypt($value){
    $secret = Crypt::encrypt($value);
    return $secret;
  }
}
