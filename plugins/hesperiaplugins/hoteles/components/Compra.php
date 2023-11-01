<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use Flash;
use Session;
use Redirect;
use Input;
use Carbon\Carbon;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use HesperiaPlugins\Hoteles\Models\Compra as CompraModel;
use HesperiaPlugins\Hoteles\Models\Upselling as UpsellingModel;
use HesperiaPlugins\Hoteles\Models\Upgrade;
use HesperiaPlugins\Hoteles\Models\Pago;
use HesperiaPlugins\Hoteles\Models\Hotel;

use Mail;
use Validator;
use ValidationException;
use Crypt;
use Auth;
use Illuminate\Contracts\Encryption\DecryptException;
class Compra extends ComponentBase{

  public $compra;

  public $implement = [
        'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
    ];

    public function defineProperties(){
      return [
        'id' => [
          'title' => 'Identificador',
          'description' => 'Valor Indentificador',
          'type' => 'string',
          'default' => '{{ :id }}'],
        'formato' => [
            'title'       => 'Formato',
            'type'        => 'Forma de leer el valor identificado',
            'default'     => 'normal',
            'placeholder' => 'Seleccione',
            'options'     => ['normal'=>'Normal', 'encriptado'=>'Encriptado', 'ambos' => "Ambos"]
          ]
      ];
    }

    public function componentDetails(){
      return [
        'name'=> 'Compra',
        'description' => 'Vista individual de una compra'
      ];
    }
    public function onRun(){
      $this->addJs('assets/js/compra.js');
    }
    public function onRender(){
        if (empty($this->compra)) {
          $this->compra = $this->page["compra"] = $this->cargarCompra();
            //$this->monedas = $this->page['monedas'] = $this->cargarMonedas();
            //$this->upselling = $this->page['upselling'] = $this->cargarUpselling();
        }
    }

    public function cargarCompra(){
      $session = Session::all();
      $compra = null;
      if ($this->param('id')) {

        try {
            $decrypted = Crypt::decrypt($this->param('id'));
        }
        catch (DecryptException $ex) {
            $decrypted = null;
        }
        return CompraModel::find($decrypted);

      }else if (isset($session["compra_individual"])) {
        $item = $session["compra_individual"];

        if (count($item) >0 && isset($item[0]["id-ups"])) {
          //SI ES UN UPSELLING
          $compra = $this->cargarUpselling($item[0]);
          $this->page["paises"] = $this->getCodigosTelefonicos();
          $this->page["codigos_documentos"] = $this->getCodigosDocumentos();
        }

      }else if(isset($session["compra_multiple"])){
        $compra = array();
        $items = $session["compra_multiple"];

        foreach ($items as $value) {
          $item = $this->cargarUpselling($value);

          array_push($item, $compra);
        }
      }
      return $compra;
    }

    public function cargarUpselling($item){
      $compra = array();
      $disponible = false;
      $upselling = UpsellingModel::find($item["id-ups"]);
      $hoy = new Carbon;
      $propiedades["checkin"] = $hoy;
      $propiedades["checkout"] = $hoy;
      $propiedades["moneda"] = $item["moneda"];
      $propiedades["hotel"] = $upselling->hotel_id;
      $precios = $upselling->getPreciosMultiMoneda($propiedades);
      foreach ($precios as $key => $precio) {
        $precios[$key]["precio"] = $precio["precio"]*$item["cantidad"];
        $precios[$key]["precio_neto"] = $precio["precio_neto"]*$item["cantidad"];
      }
      foreach ($precios as  $precio) {
        if ($precio["moneda_id"] == $item["moneda"] && $precio["precio"]>0) {
          $titulo = ($item["cantidad"] > 1 ? $upselling->titulo." X".$item["cantidad"] : $upselling->titulo);
          $compra["titulo"] = $titulo;
          $compra["descripcion"] = $upselling->descripcion;
          $compra["cantidad"] = $item["cantidad"];
          $compra["precio"] = $precio;
          if (isset($item["fecha_disfrute"])) {
            $compra["fecha_disfrute"] = $item["fecha_disfrute"];
          }

        }
      }
      $compra["imagen"] = $upselling->imagen;
      return $compra;
    }

    public function onSaveCompra(){
      $data = Input::get();
      $session = Session::all();

      /*PASO 1 VALIDAR DATOS POR INPUTS */
      if (!isset($data["terminos"])) {
        $data["terminos"] = 0;
      }
      $messages = [
            'required' => ' :attribute es requerido.',
            'email' => 'Formato de email incorrecto',
            'terminos.accepted' => 'Debe aceptar los términos y condiciones'
        ];
      // V- J- P-
      $validator = Validator::make(
        [
        'nombre' => $data["nombre"],
        'email' => $data["email"],
        'documento' => $data["documento"],
        'telefono' => $data["telefono"],
        'terminos' => $data["terminos"]
        ],
        [
          'nombre' => 'required|min:5|max:100',
          'email' => 'required|email',
          'documento' => 'required|min:6|max:50',
          'telefono' => 'required',
          'terminos' => 'accepted',
        ],
        $messages
      );
      if ($validator->fails()) {
         throw new ValidationException($validator);
       }else{
         /*if (isset($session["items"])) {*/
           //$propiedades = $session["propiedades"];
           //PASO 2, buscar usuario
           $user = User::where("email", $data["email"])->first();
           if (!$user) {
             //NO EXISTE EL USUARIO, LO CREO Y LE PONGO GRUPO DE NO CONFIRMADOS
             $pass = uniqid();
             $group = UserGroup::find(3);
             $user = Auth::register([
              'name' => '',
              'email' => $data["email"],
              'password' => $pass,
              'password_confirmation' => $pass,
            ]);

             $user->groups()->add($group);

            
           }
           $flag = false;

           //PASO 1 VERIFICAR USUARIO
           //PASO 2 VERIFICO TIPO DE COMPRA
           $compra = new CompraModel();
           if (isset($session["compra_individual"])) {
             $item = $session["compra_individual"][0];

             if ($item["cantidad"]>0 && $item["precio"]>0) {
               $compra->nombre_cliente = $data["nombre"];
               $compra->identificacion = $data["codigo_documento"].$data["documento"];
               $compra->usuario_id = $user->id;
               $compra->comentario = $data["comentarios"];
               $compra->status_id = 2;
               $compra->moneda_id = $item["moneda"];
               $compra->origen_id = 1;
               $compra->contacto = $data["codigo_pais"].$data["telefono"];
               $compra->total = $item["precio"];
               $compra->pago_insite = 1;

               $fechaAhora = Carbon::now();
               $fechaVigencia = $fechaAhora->addDays(2);
               $compra->fecha_vigencia = $fechaVigencia;
               $compra->save();
               $upgrade = new Upgrade();

               $upgrade->upgradable_id = $compra->id;
               $upgrade->upgradable_type = "HesperiaPlugins\Hoteles\Models\Compra";
               $upgrade->precio = $item["precio"];
               $upgrade->moneda_id = $item["moneda"];
               $upgrade->upselling_id = $item["id-ups"];
               $upgrade->cantidad = $item["cantidad"];
               if (isset($item["fecha_disfrute"])) {
                 $upgrade->fecha_disfrute = new Carbon($item["fecha_disfrute"]);
               }
               $upgrade->save();
               Session::forget("compra_individual");
               
               Mail::send("hesperiaplugins.hoteles::mail.pre_compra",
                 $compra->getResumen(), function($message) use ($compra) {
                 $message->to($compra->usuario->email);

               });

               return Redirect::to("pagos/".$compra->getIdEncriptado());
             }
           }

         /*}*/  // FIN IF ITEMS

         /**/

       }
      /*if (!isset($data["terminos"])) {
        $data["terminos"] = 0;
      }*/
      //var_dump($data);
    //  echo "hola";
      //return "hola";
    }

    public function onAddComprobante(){
      $data = Input::get();
      $file = Input::file("archivo2");
      $data["comprobante"] = $file;
      $compra =  CompraModel::find($data["compra_id"]);
      if ($compra->status_id==2) {
        $rules = [
          "localizador" => "min:4|max:20|required",
          "comprobante" => "mimes:jpeg,pdf,png|max:2048|required"
        ];
        $messages = [
            'required' => ' :attribute es requerido.',
            'localizador.required' => 'Número de Transferencia es requerido',
            'mimes' => 'Sólo son permitidos archivos en formato JPG o PDF',
            'comprobante.max' => 'El peso máximo permitido es 2MB'
        ];
        $validator = Validator::make($data, $rules, $messages);
      //  Flash::error("Falló");
        if ($validator->fails()) {
          //Flash::error("Falló");
          $messages = $validator->messages();
          $retorno = "";
          foreach ($messages->all('<li>:message</li>') as $message) {
            $retorno .= $message;
          }

          Flash::error($retorno);

        }else{
          if ($compra->moneda_id==1 || $compra->moneda_id == 3) {
            $tipo_pago = 2;
          }else{
            $tipo_pago = 4;
          }
          $pago = new Pago([
            "referencia" => $data["localizador"],
            "pagable_type" => "HesperiaPlugins\Hoteles\Models\Compra",
            "tipo_pago_id" => $tipo_pago,
            "pagable_id" => $compra->id,
            "archivo" => Input::file("archivo2")
          ]);
          if ($pago->save()) {
            $compra->status_id = 3;
            $compra->save();
            //Flash::success("Archivo cargado correctamente!");
            return Redirect::back();
          }else{
            Flash::error("Ha ocurrido un error, intente mas tarde");
          //  return Redirect::back();
          }
        }
      }else{
        Flash::error("No se pueden añadir comprobantes en esta Compra");
      }

    }

    public function onPayInsite(){
      $data = Input::get();
     
      $compra =  CompraModel::find($data["id"]);
      $hotel = Hotel::find($data["hotel"]);

      $compra->status_id = 6;
      $compra->save();

      
      try {
        $resumen = $compra->getResumen();
        
        Mail::send("hesperiaplugins.hoteles::mail.compra_pago_insite",
          $compra->getResumen(), function($message) use ($compra, $resumen, $hotel) {
          $message->to($compra->usuario->email);
          $hotel_email = Db::table('navicudev_emailnotificacion_')->get();
           $emails = $hotel_email;
         
          foreach ($emails as $email) {
            //var_dump($email["email"]);
            $message->to($email->email);
          }

        });
        return true;
      } catch (\Exception $e) {
        //Flash::error("No se ha podido enviar el correo de confirmación.".$e->getMessage());
        trace_log("error al enviar email a recepción".$e->getMessage());
        return Flash::error("No hemos podido efectuar esta acción, intenta nuevamente");
      }
  
    }
}
?>
