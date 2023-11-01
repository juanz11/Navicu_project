<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use Input;
use Db;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use HesperiaPlugins\Hoteles\Models\Impuesto;
use HesperiaPlugins\Hoteles\Models\Habitacion;
use HesperiaPlugins\Hoteles\Models\Hotel;
use HesperiaPlugins\Hoteles\Models\Reservacion;
use HesperiaPlugins\Hoteles\Models\DetalleReservacion;
use HesperiaPlugins\Hoteles\Models\Pago;
use HesperiaPlugins\Instapago\Models\Transaccion;
use HesperiaPlugins\Hoteles\Models\Upselling;
use HesperiaPlugins\Hoteles\Models\Paquete;
use HesperiaPlugins\Hoteles\Models\Upgrade;

use Session;
use Redirect;
use Flash;
use Mail;
use Validator;
use ValidationException;
use Carbon\Carbon;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Auth;
use System\Models\File;

class ResumenReserva extends ComponentBase{

  public $reserva_id;
  public $seleccion;

  public $implement = [
    'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
  ];


  public function defineProperties(){
    return [
      'reserva_id' => [
          'title'       => 'ID reservación',
          'description' => 'Si ya existe la reservación, se cargará desde url',
          'default'     => '{{ :reserva_id }}',
          'type'        => 'string'
      ]
    ];
  }

  public function componentDetails(){
    return [
      'name'=> 'Resumen Reservacion',
      'description' => 'Detalle de la reservación'
    ];
  }

  public function onRun(){
    //Session::forget('items');
    $this->addJs('assets/js/resumen_reserva.js');
    $this->addCss('assets/css/reservaciones.css');

    $data = Session::all();

    $propiedades = Session::get("propiedades");
    $seleccion = Session::get("seleccion");
    $seleccion_ups = Session::get("seleccion_upsellings_paq");
    $paquete_id = Session::get("paquete_id");

    Session::forget("items");

    //var_dump($data);
    if ($this->param('reserva_id')) {

      try {
          $decrypted = Crypt::decrypt($this->param('reserva_id'));
      }
      catch (DecryptException $ex) {
          $decrypted = null;
      }
      Session::forget("seleccion");
      Session::forget("propiedades");
      Session::forget("upsellings_paq");
      Session::forget("paquete_id");
      $reserva_id = $decrypted;
      $reservacion = Reservacion::find($reserva_id);
      //|| $reservacion->status_id == 3
      //var_dump($reservacion);
      if ($reservacion) {
        if ( $reservacion->status_id == 3 || ($reservacion->status_id == 2 && $reservacion->verificarVigencia())
        || $reservacion->status_id == 1 || $reservacion->status_id == 6 ||  $reservacion->status_id == 7 ) {
        $begin = new \DateTime($reservacion->checkin);
            $end = new \DateTime($reservacion->checkout);
            $propiedades = array(
              "hotel" => $reservacion->hotel_id,
              "checkin" => $begin->format("d-m-Y"),
              "checkout" => $end->format("d-m-Y"),
              "moneda" => $reservacion->moneda_id
            );
            $seleccion = $reservacion->detalles;
            $this->page["reservacion"] = $reservacion;
            $this->page["hotel"] = $hotel = Hotel::find($reservacion->hotel_id);
            $this->page["propiedades"] = $propiedades;
            $this->page["habitacion_obj"] = $habitacion = new Habitacion;
            $this->page["items"] = $items = $reservacion->getResumen();

        }elseif($reservacion->status_id == 2 && $reservacion->verificarVigencia()==false){
          $this->page["reserva_caducada"] = true;
        }
      }

    }else if(isset($seleccion) && isset($propiedades)){
      $this->page["hotel"] = $hotel = Hotel::find($propiedades["hotel"]);
      $this->page["propiedades"] = $propiedades;
      $this->page["seleccion"] = $seleccion;;
      $this->page["habitacion_obj"] = $habitacion = new Habitacion;
      $this->page["items"] = $items = $habitacion->getResumenReserva($seleccion, $propiedades);
      if ($seleccion_ups!=null && $paquete_id!=null) {
        $ups = new Upselling();
        $this->page["paquete"] = Paquete::select("titulo")->where("id", $paquete_id)->first();
        $this->page["seleccion_upsellings_paq"] = $seleccion_ups;
        $this->page["upsellings_paq"] = $ups_items = $ups->getResumenUpsellings($seleccion_ups, $paquete_id, $propiedades);
        Session::put("upsellings_paq", $ups_items);
      }


      //Session::put("items", $items);
      $this->cargarCodigosTelefonicos();
    }


  }

  public function cargarReservacion(){
    /*if ($this->property('reserva_id')) {
      $this->reserva_id = $this->property('reserva_id');
    }*/
  }

  public function cargarCodigosTelefonicos(){
    //next example will recieve all messages for specific conversation
    $service_url = 'https://restcountries.com/v2/all';
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {

        $info = curl_getinfo($curl);
        curl_close($curl);
        //die('error occured during curl exec. Additioanl info: ' . var_export($info));
        $this->page["paises"] = array('+58' => '+58' );
    }
    curl_close($curl);
    $decoded = json_decode($curl_response);
    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
        //die('error occured: ' . $decoded->response->errormessage);
       $this->page["paises"] = array('+58' => '+58' );

    }
    $this->page["paises"] = $decoded;
    $this->page["codigos_documentos"] = array('V' => 'V', 'J' => 'J', 'P' => 'P' );
}
  public function onSaveReserva(){
    $data = Input::get();
    $session = Session::all();

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

      if (isset($session["seleccion"]) && isset($session["propiedades"])) {
        $propiedades = $session["propiedades"];
        /*PASO 2, buscar usuario*/
        $user = User::where("email", $data["email"])->first();
        if (!$user) {
          /*NO EXISTE EL USUARIO, LO CREO Y LE PONGO GRUPO DE NO CONFIRMADOS*/
          $pass = uniqid();
          $group = UserGroup::find(3);

          $user = Auth::register([
            'name' => '',
            'email' => $data["email"],
            'password' => $pass,
            'password_confirmation' => $pass,
          ]);

          /*$user = User::create(['name' => '', 'surname' => '', 'password' => '$pass',
          'password_confirmation'=> '$pass','email' => $data["email"]]);*/
          $user->groups()->add($group);

          //return "no existe";
        }
        /*PASO 3, BUSCAR IMPUESTOS */
        $hotel = Hotel::find($propiedades["hotel"]);
        $impuestos = $hotel->getImpuestos($propiedades["moneda"]);
        /* PASO 4 calcular el total */
        $seleccion = $session["seleccion"];

        $hab = new Habitacion();

        $arrayDescuentos = array();

        $total = 0;
        //TOTAL EN ALOJAMIENTO
        foreach ($seleccion as $key => $value) {
          if(isset($value["upsellings"])){
            foreach( $value["upsellings"] as $key2 => $upselling){
              $total = $upselling["precio"]+$total;
            }
          }
          $total = $value["alojamiento"] + $total;
        }
        //TOTAL EN UPSELLINGS DE PAQUETES
        $paquete_id = null;
        if (isset($session["upsellings_paq"]) && isset($propiedades["paquete_id"])) {
          $upsellings_paq = $session["upsellings_paq"];
          foreach ($upsellings_paq as $key => $value) {
            $total = $value["precio"] + $total;
          }
          $paquete_id = $propiedades["paquete_id"];
        }
        /*PASO 6, crear reservacion*/
        $begin = new \DateTime($propiedades["checkin"]);
        $end = new \DateTime($propiedades["checkout"]);

        $fechaAhora = Carbon::now();

        $fechaVigencia = $fechaAhora->addDays(2);

        $reservacion = Reservacion::create([
          'huesped' => $data["nombre"],
          'checkin' => $begin->format("Y-m-d"),
          'checkout' => $end->format("Y-m-d"),
          'moneda_id' => $propiedades["moneda"],
          'status' => 2,
          'total' => $total,
          'identificacion' => $data["codigo_documento"].$data["documento"],
          'contacto' => $data["codigo_pais"].$data["telefono"],
          'comentarios' => $data["comentarios"],
          'hotel_id' => $hotel->id,
          'fecha_vigencia' => $fechaVigencia,
          'origen_id' => 1,
          'paquete_id' => $paquete_id,
          'pago_insite' => 1,
          'usuario_id' => "-1"
          ]);

        $reservacion->usuario()->add($user);

        if (count($seleccion) > 0 && $total >0 ) {
          $reservacion->save();
          
          /*PASO 7 insertar los detalles de la reserva*/
          $total=0; //TOTAL FINAL
          foreach ($seleccion as $key => $value) {
             $detalle = new DetalleReservacion([
               'habitacion_id' => $value["habitacion_id"],
               'ocupacion' => $value["ocupacion"],
               'precio' => $value["alojamiento"],
               'regimen_id' => $value["regimen_id"]
             ]);
             //echo "$totalPorHaB";
            $detalle = $reservacion->detalles()->add($detalle);
            $ultimo_detalle = $reservacion->detalles()->latest("id")->first();
            //$detalle_id = $detalle->id;
            $total = 0;

            /* PASO 7.1 Si alguna habitacion tiene descuento y no es paquete */

            if (!isset($upsellings_paq)) { //SI NO ES PAQUETE
              if (count($dctos = $value["descuentos"])>0) {
                //var_dump($value);
                foreach ($dctos as $key => $value2) {
                  Db::table('hesperiaplugins_hoteles_descuento_reserva')->insert(
                    ['descuento_id' => $value2["descuento_id"], 'porcentaje' => $value2["porcentaje"],
                  'detalle_id' => $ultimo_detalle->id, 'concepto' => $value2["concepto"]]
                  );

                  if ($value2["codigo_promocional"]!= null && $value2["codigo_promocional"]!="") {
                    Db::table('hesperiaplugins_hoteles_descuento_habitacion')
                    ->where("descuento_id", "=", $value2["descuento_id"])
                    ->where("habitacion_id", "=", $ultimo_detalle->habitacion_id)
                    ->decrement('cantidad', 1);
                  }
                }
              }
            }
            /*PASO 7.2 VERIFICAR SI LA HAB SELECCIONADA TIENE UPSELLINGS */
            if (isset($value["upsellings"])) {
              $upsellings = $value["upsellings"];
              foreach ($upsellings as $key => $ups_hab) {
                $upgrade = new Upgrade([
                  'precio' => $ups_hab["precio"],
                  'upgradable_type' => "HesperiaPlugins\Hoteles\Models\DetalleReservacion",
                  'upgradable_id' => $ultimo_detalle->id,
                  'upselling_id' => $key,
                  'moneda_id' => $propiedades["moneda"],
                  'cantidad' => $ups_hab["cantidad"]
                ]);

              $ultimo_detalle->upgrades()->add($upgrade);
              }
            }


          }
          //aqui guardamos los upsellings de los paquetes
          if (isset($upsellings_paq)) {
            foreach ($upsellings_paq as $key => $value) {
              $upgrade = new Upgrade([
                'precio' => $value["precio"],
                'upgradable_type' => "HesperiaPlugins\Hoteles\Models\Reservacion",
                'upgradable_id' => $reservacion->id,
                'upselling_id' => $value["id"],
                'moneda_id' => $propiedades["moneda"],
                'cantidad' => $value["cantidad"]
              ]);

            $reservacion->upgrades()->add($upgrade);
            }
          }
          /*PASO 8,Si tiene impuestos guardarlos*/
          if ($reservacion && $impuestos) {
            foreach ($impuestos as $key => $value) {
              Db::table('hesperiaplugins_hoteles_impuesto_reserva')->insert(
                ['impuesto_id' => $value->id, 'reserva_id' => $reservacion->id,
              'valor' => $value->valor]
              );
            }
          }
          /*PASO 9, verificar si el descuento tiene codigo y restar el numero de disponibles*/

          Mail::send("hesperiaplugins.hoteles::mail.pre_reserva",
            $reservacion->getResumen(), function($message) use ($user) {
            $message->to($user->email);

          });
          Session::forget(["seleccion", "disponibilidad"]);
          return Redirect::to("pago-de-reserva/".$reservacion->getIdEncriptado());
        }else{
          Flash::error("No hemos podido guardar su reservación, por favor inténtelo de nuevo");
          Session::forget(["seleccion", "disponibilidad"]);
        }
      }else{
        Flash::error("No hemos podido guardar su reservación, por favor inténtelo de nuevo");
      }

    }

  }

  public function onAddComprobante(){
    $data = Input::get();
    $reserva =  Reservacion::find($data["reserva_id"]);
    $file = Input::file("archivo2");
    //trace_log($file);

    if ($reserva->status_id==2) {
      $data["archivo"] = Input::file("archivo2");
      //VALIDACIONES
      // 1 tipo de datos
      $rules = [
        "localizador" => "min:4|max:20|required",
        "archivo" => "mimes:jpeg,pdf,png|max:2048|required"
      ];

      $messages = [
          'required' => ' :attribute es requerido.',
          'mimes' => 'Sólo son permitidos archivos en formato JPG o PDF',
          //'archivo.max' => 'El peso máximo permitido es 2MB'
      ];

      $validator = Validator::make($data, $rules, $messages);

      if ($validator->fails()) {
         // throw new ValidationException($validator);
        return false;
      }else{
        if ($reserva->moneda_id==1 || $reserva->moneda_id == 3) {
          $tipo_pago = 2;
        }else{
          $tipo_pago = 4;
        }
        $pago = Pago::create([
          "referencia" => $data["localizador"],
          "pagable_type" => "HesperiaPlugins\Hoteles\Models\Reservacion",
          "tipo_pago_id" => $tipo_pago,
          "pagable_id" => $reserva->id,
        ]);
        //$pago->save();
        $fileToUpload = new File;
        $fileToUpload->fromPost(Input::file("archivo2"));
        
        $fileToUpload->save();

        $pago->archivo()->add($fileToUpload);
        
        //$pago->archivo = Input::file("archivo2");
        //trace_log($pago);
        
        if ($pago->save()) {
          $reserva->status_id = 3;
          $reserva->save();
          Flash::success("Archivo cargado correctamente!");
          return Redirect::back();
        }else{
          Flash::error("Ha ocurrido un error, intente mas tarde");
          return Redirect::back();
        }
      }
    }else{
      return false;
    }

  }

  public function onPayInsite(){
    $data = Input::get();
    $reserva =  Reservacion::find($data["id"]);
    $reserva->status_id = 6;
    $reserva->save();
    try {
      $resumen = $reserva->getResumen();
      $hotel_email = Db::table('navicudev_emailnotificacion_')->get();
      Mail::send("hesperiaplugins.hoteles::mail.pago_insite",
      $resumen, function($message) use ($reserva, $resumen,$hotel_email) {
     
      $emails = $hotel_email;
      foreach ($emails as $email) {
      //var_dump($email["email"]);
      $message->to($email->email);
      }
      $message->to($reserva->usuario->email, $name=null);
      //$message->subject("Registro de solicitud de reserva en Hesperia");
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
