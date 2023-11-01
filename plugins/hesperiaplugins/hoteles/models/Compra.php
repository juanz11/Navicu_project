<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Crypt;
use Carbon\Carbon;
use Mail;

/**
 * Model
 */
class Compra extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = [
      'HesperiaPlugins.Hoteles.Behaviors.ReservacionesOperations'
    ];

    protected $fillable = [
        'nombre_cliente',
        'identificacion',
        'usuario_id',
        'comentario',
        'status_id',
        'moneda_id',
        'origen_id',
        'contacto',
        'total',
        'fecha_vigencia',
        'pago_insite',
        'codigo'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_compra';

    public $belongsTo = [
        'status' => ['HesperiaPlugins\Hoteles\Models\StatusReserva', 'key' => 'status_id' ],
        'usuario' => ['RainLab\User\Models\User', 'key' => 'usuario_id' ],
        'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id'],
        'origen' => ['HesperiaPlugins\Hoteles\Models\OrigenReserva', 'key' => 'origen_id']
    ];

    public $morphMany = [
        'pagos' => ['HesperiaPlugins\Hoteles\Models\Pago', 'name' => 'pagable'],
        'observaciones' => ['HesperiaPlugins\Hoteles\Models\Observacion', 'name' => 'observable'],
        'upgrades' => ['HesperiaPlugins\Hoteles\Models\Upgrade', 'name' => 'upgradable'],
        'cotizaciones' => ['HesperiaPlugins\Hoteles\Models\Cotizacion', 'name' => 'cotizable']
    ];

    public $hasMany = [
        'reservaciones' => ['HesperiaPlugins\Hoteles\Models\Reservacion', 'key' => 'compra_id']
    ];

    public function getIdEncriptado(){
      $secret = Crypt::encrypt($this->id);
      return $secret;
    }


    public function getMontoEncriptado(){
      $retorno = 0;
      if (is_float($this->total)) {
        $retorno = number_format($this->total, 2);
      }else{
        $retorno = $total;
      }
      $secret = Crypt::encrypt($retorno);
      return $secret;
    }

    public function permiteTransferencias(){
      $array_upgrades = $this->upgrades;
      $diferencia = null;
      
      $fecha_actual = Carbon::today();
      foreach ($array_upgrades as $upgrade) {
       
        if ($upgrade->fecha_disfrute !== null) {
          $date = new Carbon($upgrade->fecha_disfrute);

          if ($diferencia == null) {
            $diferencia = $date->diffInDays($fecha_actual);
          }else{
            $aux_diff = $date->diffInDays($fecha_actual);
            if ($aux_diff < $diferencia) {
             $diferencia = $aux_diff; 
            } 
          }
        }
      }
      //trace_log($diferencia);
      if ($diferencia == null) {
        return true;
      }else{
        if ($diferencia >= 2) {
        return true;
        }else{
          return false;
        }
      }

    }

    public function permitePagoEnHotel(){
      if ($this->pago_insite == 1) {
        return true;
      }else{
        return false;
      }
    }
    public function aprobar($pago_insite = null, $arNotificaciones = null){
      $hotel = null;
      if ($this->reservaciones!==NULL && count($this->reservaciones)>0 ) {
        $primera_reserva = $this->reservaciones->first();
        $hotel = $primera_reserva->hotel;
      }else{
        $hotel = $this->upgrades[0]->upselling->hotel;
      }
      $compra = $this;
      $this->status_id = 1;
      $this->save();
      try {
        $resumen = $this->getResumen();
        $resumen["pago_insite"] = $pago_insite;

        Mail::send("hesperiaplugins.hoteles::mail.aprobacion_compra",
          $resumen, function($message) use ($compra, $hotel, $arNotificaciones) {
          $message->to($compra->usuario->email);

          if(!$arNotificaciones){
            $emails = $hotel->emails_notificacion;
            foreach ($emails as $email) {
              //var_dump($email["email"]);
              $message->bcc($email["email"], $name=null);
            }
          }else{
            foreach ($arNotificaciones as $email) {
            //var_dump($email["email"]);
            $message->bcc($email, $name=null);
            }
          }

        });
        return true;
      } catch (\Exception $e) {
        //Flash::error("No se ha podido enviar el correo de confirmación.".$e->getMessage());
        trace_log($e);
        return false;
      }
    }
/////////////////////////////////////////

    public function getResumen(){
      $reservaciones = array();
      $upsellings = array();
      foreach ($this->reservaciones as  $value) {
        array_push($reservaciones, $value->getResumen());
      }

      foreach ($this->upgrades as $upgrade) {
        $cantidad ="";
        if ($upgrade->cantidad > 1) {
          $cantidad.=" x".$upgrade->cantidad;
        }
        $fecha_disfrute = null;
        if ($upgrade->fecha_disfrute!==null ) {
          $fecha_aux = new Carbon($upgrade->fecha_disfrute);
          $fecha_disfrute = $fecha_aux->format("d-m-Y");
        }

        $ups=[
          "titulo" => $upgrade->upselling->titulo.$cantidad,
          "precio" => number_format($upgrade->precio, 2 ,",", "." )." ".$this->moneda->acronimo,
          "fecha_disfrute" => $fecha_disfrute,
          "hotel" => $upgrade->upselling->hotel
        ];
        array_push($upsellings, $ups);
      }
      $resumen = [
        "id" => $this->id,
        "nombre" => $this->nombre_cliente,
        "total" => number_format($this->total, 2 ,",", "." )." ".$this->moneda->acronimo,
        //"total" => $this->total." ".$this->moneda->acronimo,
        "pagos" => $this->pagos,
        "telefono" => $this->contacto,
        "pago_insite" => $this->pago_insite,
        "comentarios" => $this->comentarios,
        //"agente" => $owner,
        //"paquete" => $titulo_paquete,
        "id_encrypt" => $this->getIdEncriptado(),
        "reservaciones" =>$reservaciones,
        "upsellings" => $upsellings,
        "codigo" => $this->codigo
      ];
      //trace_log($resumen);
      return $resumen;
    }

    public function scopeIsVigente($query){
        $today = Carbon::now();

        return $query->where('status_id', '=', 2)
        ->where("fecha_vigencia", "<", $today);
    }

    public function getItemPrimary(){

      echo($this->upgrades->first()->upselling->hotel->nombre);
        
      
    }

    public function getArPrecios(){
      $arPrecios = array();
      $totalMarkup = 0;
      $percent = 100 + $this->porcentaje_markup;
      foreach ($this->reservaciones as  $reservacion) {
        $totalMarkup = $totalMarkup + $reservacion->total;
      }

      foreach ($this->upgrades as $upgrade) {
        $totalMarkup = $totalMarkup + $upgrade->precio;
      }

      $arPrecios["total_markup"] = $totalMarkup;
      $arPrecios["total_raw"] = $totalMarkup - ($totalMarkup/$percent) * $this->porcentaje_markup;

      return $arPrecios;
    }
}
