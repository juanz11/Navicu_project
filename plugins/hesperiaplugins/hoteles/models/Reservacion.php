<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
use Carbon\Carbon;
use Crypt;
use Mail;
use BackendAuth;
use \October\Rain\Database\Traits\Validation;
/**
 * Model
 */
class Reservacion extends Model
{


    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    //public $timestamps = true;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_reservacion';

    protected $fillable = [
        'huesped',
        'checkin',
        'checkout',
        'total',
        'identificacion',
        'contacto',
        'comentarios',
        'moneda_id',
        'usuario_id',
        'status_id',
        'hotel_id',
        'fecha_vigencia',
        'origen_id',
        'paquete_id',
        'pago_insite',
        'codigo',
    ];

    protected $jsonable = ['info_adicional'];


    //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA

    public $belongsTo = [
        'status' => ['HesperiaPlugins\Hoteles\Models\StatusReserva', 'key' => 'status_id' ],
        'usuario' => ['RainLab\User\Models\User', 'key' => 'usuario_id' ],
        'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id'],
        'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id'],
        'origen' => ['HesperiaPlugins\Hoteles\Models\OrigenReserva', 'key' => 'origen_id'],
        'paquete' => ['HesperiaPlugins\Hoteles\Models\Paquete', 'key' => 'paquete_id'],
        'compra' => ['HesperiaPlugins\Hoteles\Models\Compra', 'key' => 'compra_id']
    ];

    public $hasMany = [
        'detalles' => ['HesperiaPlugins\Hoteles\Models\DetalleReservacion', 'key' => 'reservacion_id'],
        'detalle_count' => ['HesperiaPlugins\Hoteles\Models\DetalleReservacion', 'key' => 'reservacion_id', 'count' => true]
    ];

    public $morphMany = [
        'pagos' => ['HesperiaPlugins\Hoteles\Models\Pago', 'name' => 'pagable'],
        'observaciones' => ['HesperiaPlugins\Hoteles\Models\Observacion', 'name' => 'observable'],
        'upgrades' => ['HesperiaPlugins\Hoteles\Models\Upgrade', 'name' => 'upgradable'],
        'cotizaciones' => ['HesperiaPlugins\Hoteles\Models\Cotizacion', 'name' => 'cotizable']
    ];

    /*public $hasOne = [
      'cotizacion' => ['HesperiaPlugins\Hoteles\Models\Cotizacion', 'key' => 'cotizable_id',
      ]
    ];*/

    public $belongsToMany =[
      'impuestos' => [
        'HesperiaPlugins\Hoteles\Models\Impuesto',
        'table' => 'hesperiaplugins_hoteles_impuesto_reserva',
        'key' =>'reserva_id'
      ],
    ];

    public $implement = [
      'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions',
      'HesperiaPlugins.Hoteles.Behaviors.ReservacionesOperations',
    ];

    public function getResumen(){

      $begin = new \DateTime($this->checkin);
      $end = new \DateTime($this->checkout);

      $detalles = $this->detalles;
      $habitaciones = array();
      $i = 0;
      foreach ($detalles as $detalle) {
        $upsellings_hab = array();
        $j=0;
        foreach ($detalle->upgrades as $upgrade_hab) {
          $cantidad ="";
          if ($upgrade_hab->cantidad > 1) {
            $cantidad.=" x".$upgrade_hab->cantidad;
          }
          $fecha_disfrute = null;
          if ($upgrade_hab->fecha_disfrute!==null && $upgrade_hab->fecha_disfrute!="0000-00-00 00:00:00") {
            $fecha_aux = new Carbon($upgrade_hab->fecha_disfrute);
            $fecha_disfrute = $fecha_aux->format("d-m-Y");
          }
          $upsellings_hab[$j]=[
            "titulo" => $upgrade_hab->upselling->titulo.$cantidad,
            "precio" => number_format($upgrade_hab->precio, 2 ,",", "." )." ".$this->moneda->acronimo,
            "fecha_disfrute" => $fecha_disfrute,
          ];
          $j++;
        }
        $detalle_precio = $this->cambiarFormatoPrecio($detalle->precio, $this->moneda->id);
        $habitaciones[$i] = [
          "nombre" => $detalle->habitacion->nombre,
          "ocupacion" => $detalle->habitacion->getOcupacion($detalle->ocupacion),
          "regimen" => $detalle->regimen->nombre,
          "precio" => $detalle_precio." ".$this->moneda->acronimo,
          "descuentos" => $detalle->descuentos,
          "upgrades" => $upsellings_hab,

        ];
        $i++;
      }

      $i=0;
      $upsellings_paq = array();
      foreach ($this->upgrades as $upgrade) {
        $cantidad ="";
        if ($upgrade->cantidad > 1) {
          $cantidad.=" x".$upgrade->cantidad;
        }
        $fecha_disfrute = null;
        if ($upgrade->fecha_disfrute!==null && $upgrade->fecha_disfrute!="0000-00-00 00:00:00") {
          $fecha_aux = new Carbon($upgrade->fecha_disfrute);
          $fecha_disfrute = $fecha_aux->format("d-m-Y");
        }
        $upgrade_precio = $this->cambiarFormatoPrecio($upgrade->precio, $this->moneda->id);
        $upsellings_paq[$i]=[
          "titulo" => $upgrade->upselling->titulo.$cantidad,
          "precio" => $upgrade_precio." ".$this->moneda->acronimo,
          "fecha_disfrute" => $fecha_disfrute
        ];
        $i++;
      }
      //$pago = $this->pagos->first();
      $owner = null;
      if ($this->cotizaciones) {
        if (isset($this->cotizaciones[0])) {
          $owner = $this->cotizaciones[0]->usable;
        }

      }

      $titulo_paquete = null;
      if ($this->paquete) {
        $titulo_paquete = $this->paquete->titulo;
      }
      $resumen = [
        "id" => $this->id,
        "nombre" => $this->huesped,
        "hotel" => $this->hotel->nombre,
        "hotel_id" => $this->hotel->id,
        "checkin" => $begin->format("d-m-Y"),
        "checkout" => $end->format("d-m-Y"),
        "total" => $this->cambiarFormatoPrecio($this->total, $this->moneda->id)." ".$this->moneda->acronimo,
        "total_raw"=> $this->total,
        //"total" => $this->total." ".$this->moneda->acronimo,
        "habitaciones" => $habitaciones,
        "upsellings_paq" =>$upsellings_paq,
        "pagos" => $this->pagos,
        "telefono" => $this->contacto,
        "comentarios" => $this->comentarios,
        "agente" => $owner,
        "paquete" => $titulo_paquete,
        "id_encrypt" => $this->getIdEncriptado(),
        "codigo" => $this->codigo
      ];
      return $resumen;
    }

    public function scopeIsVigente($query){
        $today = Carbon::now();
        return $query->where('status_id', '=', 2)
        ->where("fecha_vigencia", "<", $today);
    }

    public function scopeVencidas($query){
        $today = Carbon::now();
        return $query->where("fecha_vigencia", "<", $today)->orWhere(Db::raw("DATEDIFF(CURDATE(), date(created_at))"), ">", 1);

    }
    public function scopeVigentes($query){
        $today = Carbon::now();
        return $query->where("fecha_vigencia", ">=", $today);
    }

    public function scopeMyCotizacion($query){
      $user = BackendAuth::getUser();
      return $query->join("hesperiaplugins_hoteles_cotizacion as b", "hesperiaplugins_hoteles_reservacion.id", "=", "b.cotizable_id" )
      ->join("backend_users as c", "b.usable_id", "=", "c.id" )
      ->where("b.usable_type", "=", "Backend\Models\User")
      ->where("c.id", "=", $user->id);
    }


    public function verificarVigencia(){
        $fecha_reserva = new Carbon($this->created_at);
        $fecha_actual = Carbon::now();
        $diferencia = $fecha_actual->diffInDays($fecha_reserva);
        //echo($fecha_actual->format("d-m-Y")."---".$fecha_reserva);
        if ($diferencia >= 2) {
          return false;
        }else{
          return true;
        }
        //return $diferencia;
    }

    public function permiteTransferencias(){
        $checkin = new Carbon($this->checkin);
        $fecha_actual = Carbon::today();
        $diferencia = $checkin->diffInDays($fecha_actual);
        //echo "$diferencia";
        if ($diferencia >= 3) {
          return true;
        }else{
          return false;
        }
    }

    public function permitePagoEnHotel(){
      $checkin = new Carbon($this->checkin);
      $fecha_actual = Carbon::today();
      $diferencia = $checkin->diffInDays($fecha_actual);
      if($this->origen_id == 1){
        if ($this->hotel_id == 1) {
          return false;
        }else{
          if ($diferencia <=15 && $this->pago_insite == 1) {
            return true;
          }else{
            return false;
          }
        }
        
      }else{
        if ($this->pago_insite == 1) {
          return true;
        }else{
          return false;
        }
      }
    }

    public function scopeIsEnVerificacion($query){
      return $query->where('status_id', '=', 3)->where("id", $this->id);
    }
    public function getMontoEncriptado(){
      $retorno = 0;
      if (is_float($this->total)) {
        $retorno = number_format($this->total, 2, '.', '');
      }else{
        $retorno =  $this->total;
      }
      $secret = Crypt::encrypt($retorno);
      return $secret;
    }

    public function getIdEncriptado(){
      $secret = Crypt::encrypt($this->id);
      return $secret;
    }

    public function aprobar($pago_insite = null, $arNotificaciones = null){
      $reservacion = $this;
      $this->status_id = 1;
      $this->save();
      //trace_log("tengo:", $pago_insite);
      try {

        $resumen = $this->getResumen();
        
        $resumen["pago_insite"] = $pago_insite;

        Mail::send("hesperiaplugins.hoteles::mail.aprobacion_reserva",
        $resumen, function($message) use ($reservacion, $resumen, $arNotificaciones) {

        $message->to($reservacion->usuario->email);

        //$transferencias = $resumen["pagos"]->where("tipo_pago_id", 2);

        /*foreach ($transferencias as $key => $value) {
        $message->attach($value->archivo->getPath());
        }*/
        $hotel_email = Db::table('navicudev_emailnotificacion_')->get();
        if(!$arNotificaciones){
          $emails = $hotel_email;
         
          foreach ($emails as $email) {
            //var_dump($email["email"]);
            $message->bcc($email->email);
          }
        }else{
         
          foreach ($arNotificaciones as $email) {
          //var_dump($email["email"]);
          $message->bcc($email, $name=null);
          }
        }
      
        
        //$message->subject("Registro de solicitud de reserva en Hesperia");
        });
        return true;
      } catch (\Exception $e) {
        //Flash::error("No se ha podido enviar el correo de confirmación.".$e->getMessage());
        trace_log("error al enviar email-->".$e->getMessage());
        return false;
      }
    }

    public function getAgente(){
      $agente = null;
      if (count($this->cotizaciones) > 0) {
         $agente = $this->cotizaciones[0]->usable;
      }
      return $agente;
      //return var_dump();
      //return $this->cotizacion->usable;
      //return $this->cotizacion->cotizable; <-- ORIGINAL
    }

    public function getHotelOptions($value, $formData){
      $hotel = Db::table('hesperiaplugins_hoteles_hotel as a')->lists('a.nombre', 'a.id');
      return $hotel;
    }

    public function getOrigenOptions($value, $formData){
         $origen = Db::table('hesperiaplugins_hoteles_origen_reserva as r')->lists('r.origen', 'r.id');
         array_unshift($origen, 'Todos');
        return $origen;
    }

    public function getMonedaOptions($value, $formData){
         $moneda = Db::table('hesperiaplugins_hoteles_moneda as m')->lists('m.moneda', 'm.id');
        return $moneda;
    }

    public function reportReservas($form){

      $inicio = Carbon::parse($form["desde"])->format('Y-m-d');
      $fin = Carbon::parse($form["hasta"])->format('Y-m-d');

      $hotel = "";
      $hotel2 = "";

      if($form["hotel"] != 0){
        $h = implode(',',$form["hotel"]);
        $hotel = "and b.hotel_id in (".$h.")";
        $hotel2 = "and a.hotel_id in (".$h.")";
      }

      $origen = "";
      $origen2 = "";
      if($form["origen"] != 0){
        $origen = "and b.origen_id = ".$form["origen"];
        $origen2 = "and a.origen_id = ".$form["origen"];
      }

      $moneda = "";
      $moneda2 = "";

      $moneda = "and b.moneda_id = ".$form["moneda"];
      $moneda2 = "and a.moneda_id = ".$form["moneda"];

      $fecha = "";
      $fecha2 = "";

      $fecha = "date(b.created_at) BETWEEN '".$inicio."' and '".$fin."'";
      $fecha2 = "date(a.created_at) BETWEEN '".$inicio."' and '".$fin."'";

     $query = DB::table(DB::raw("(select datediff(a.checkout, a.checkin) as noches,
        (select count(c.reservacion_id)
          from hesperiaplugins_hoteles_detalle_reservacion as c
          inner join hesperiaplugins_hoteles_reservacion as b on (b.id = c.reservacion_id)
          where $fecha $hotel $origen $moneda
          and b.status_id = 1 and c.reservacion_id = a.id) as numHab,
        (select SUM(b.total)
          from hesperiaplugins_hoteles_reservacion as b
          where $fecha $hotel $origen $moneda
          and b.status_id = 1 ) as total
      from hesperiaplugins_hoteles_reservacion as a
      where $fecha2 $hotel2 $origen2 $moneda2 and a.status_id = 1) as tmp"))
     ->selectRaw("sum(tmp.noches * tmp.numHab) as roomNight, tmp.total")->get();

     return $query;
    }

    public function getCantidadUpgradesHabs(){
      $total = 0;
      foreach ($this->detalles as $key => $value) {
        $total = count($value->upgrades)+$total;
      }
      return $total;
    }

    public function scopeUpgrades($query, $scope=null){

      if ($scope) {
        if (in_array("paquete", $scope)) {
          $query->whereNotNull('paquete_id');
        }
        if (in_array("ups_hab", $scope)) {
          $query->whereHas("detalles", function($query){
            $query->has("upgrades", '>', 0);
          });
        }
      }

      return $query;
    }

    public function scopeConfirmadas($query){
      return $query->where("status_id", 1);
    }

    public function scopeBetween($query, $dates){
      if($dates["desde"] != null && $dates["hasta"] != null){
        $begin = new Carbon($dates["desde"]);
        $end = new Carbon($dates["hasta"]);
        $query->whereBetween(DB::raw("DATE(created_at)"), [$begin->format("Y-m-d"), $end->format("Y-m-d")]);
      }
      return $query;
    }

    public function scopeMoneda($query, $dates){

      return $query->where('moneda_id', $dates["moneda"]);
    }
    
    public function getArPrecios(){
      $arPrecios = array();
      $totalMarkup = 0;
      $percent = 100 + $this->porcentaje_markup;
      foreach ($this->detalles as  $detalle) {
        $totalMarkup = $totalMarkup + $detalle->precio;
      }

      $arPrecios["total_markup"] = $totalMarkup;
      $arPrecios["total_raw"] = $totalMarkup - ($totalMarkup/$percent) * $this->porcentaje_markup;

      return $arPrecios;
    }
}
