<?php namespace HesperiaPlugins\Hoteles\Behaviors;

use \NumberFormatter;
Use Carbon\Carbon;

class UtilityFunctions extends \October\Rain\Extension\ExtensionBase
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function sayHello()
    {
        echo "Hello from " . get_class($this->parent);
    }

    public function cambiarFormatoPrecio($precio, $moneda){

      if($moneda == 3 || $moneda == 1){
        $decs = 0;
      }else{
        $decs = 2;
      }
        $valor = number_format($precio, $decs ,",", "." );
      return $valor;
    }

    public function getCodigosTelefonicos(){
      $service_url = 'https://restcountries.com/v2/all';
      $curl = curl_init($service_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $curl_response = curl_exec($curl);
      if ($curl_response === false) {
          $info = curl_getinfo($curl);
          curl_close($curl);
          //die('error occured during curl exec. Additioanl info: ' . var_export($info));
          return array('+58' => '+58' );
      }
      curl_close($curl);
      $decoded = json_decode($curl_response);
      if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
          //die('error occured: ' . $decoded->response->errormessage);
         return array('+58' => '+58' );

      }else{
        return $decoded;
      }
    }

    public function getCodigosDocumentos(){
      $codigos = array('V' => 'V', 'J' => 'J', 'P' => 'P' );
      return $codigos;
    }

    public function cambiarFormatoFecha($fecha){
      $fecha_retorno = $fecha;
      try {
        $date = new Carbon ($fecha);
        $fecha_retorno = $date->format("d-m-Y");
      } catch (\Exception $e) {
        trace_log($e->getMessage());

      }

      return $fecha_retorno;

    }

}
?>
