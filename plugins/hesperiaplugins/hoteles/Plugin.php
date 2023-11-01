<?php namespace HesperiaPlugins\Hoteles;

use System\Classes\PluginBase;
use HesperiaPlugins\Hoteles\Models\Fecha as FechaModel;
use HesperiaPlugins\Hoteles\Controllers\Fecha as FechaController;
use backend\Models\User;
use Carbon\Carbon;

class Plugin extends PluginBase
{
    public function registerComponents(){
      return [
        'HesperiaPlugins\Hoteles\Components\CajaReservas' => 'CajaReservas',
        'HesperiaPlugins\Hoteles\Components\ResumenReserva' => 'ResumenReserva',
        'HesperiaPlugins\Hoteles\Components\Paquete' => 'Paquete',
        'HesperiaPlugins\Hoteles\Components\Upselling' => 'Upselling',
        'HesperiaPlugins\Hoteles\Components\Compra' => 'Compra',
        'HesperiaPlugins\Hoteles\Components\ListaUpselling' => 'ListaUpselling',
        'HesperiaPlugins\Hoteles\Components\ListaPaquetes' => 'ListaPaquetes',
        'HesperiaPlugins\Hoteles\Components\PreCheckinHandler' => 'PrechekinHandler',
      ];
    }

    public function registerSettings(){

    }

    public function registerFormWidgets(){

        return [
            'HesperiaPlugins\Hoteles\FormWidgets\Detalles' =>
            [
                'label' => 'Visor Seleccion',
                'code' => 'detalles'
            ]
        ];
    }

    public function registerListColumnTypes(){
        return [
            // A local method, i.e $this->evalUppercaseListColumn()
            'tipo_ocupacion' => [$this, 'evaluarTipoOcupacion'],
            'monto_formateado' =>[$this, 'cambiarFormatoPrecio'],
            'agente' => [$this, 'getFullAgenteName'],
            'ultimo_registro' => [$this, 'ultimoRegistro'],
            'sum' => [$this, 'revenue'],
            'sum_upgrade' => [$this, 'sumUpgrades'],
            'cotizable_hotel' => [$this, 'cotizableHotel'],
            'cotizable_nombre' => [$this, 'cotizableNombre'],
            'detalle_cotizacion' => [$this,'detalleCotizacion'],
        ];
    }

    public function evaluarTipoOcupacion($value, $column, $record)
    {
        $temp = explode("-",$value);
        return $temp[0]." Adultos - ".$temp[1]." Niños";
    }

    public function cambiarFormatoPrecio($value, $column, $record)
    {
      $valor = number_format($value, 2 ,",", "." );
      //return $valor.$record->moneda;
      return $valor;
      
    }

    public function getFullAgenteName($value, $column, $record){
         $fullname = $value["first_name"]." ".$value["last_name"];
      return $fullname;
    }

    public function ultimoRegistro($value, $column, $record){
        $data = current($value);
        return Carbon::parse($data)->format('d/m/Y');
    }

    public function revenue($value, $column, $record){
        $suma_bolivar = 0;
        $suma_dolar = 0;
        foreach ($record->revenue as $key => $valor){

            if($valor->moneda_id == 1){
                $suma_bolivar = $valor->total + $suma_bolivar;
            }else if($record->moneda_id == 2){
                $suma_dolar = $valor->total + $suma_dolar;
            }
        }
        return ("VEF: ". number_format($suma_bolivar, 0 ,",", "." )." <br> "."USD: ".number_format($suma_dolar, 0 ,",", "." ));
    }

    public function sumUpgrades($value, $column, $record){
      $total_upsellings = 0;
      foreach ($value as $key => $val) {
        $total_upsellings = $total_upsellings + $val->precio;
      }
      return number_format($total_upsellings, 0 ,",", "." );
    }

    public function cotizableHotel($value, $column, $record){

        if($record->cotizable_type == "HesperiaPlugins\Hoteles\Models\Reservacion" && $record->cotizable){

            return $record->cotizable->hotel->nombre;
        }

        if($record->cotizable_type == "HesperiaPlugins\Hoteles\Models\Compra"){

            return $record->cotizable->getItemPrimary();
        }
    }

    public function cotizableNombre($value, $column, $record){

        if($record->cotizable_type == "HesperiaPlugins\Hoteles\Models\Reservacion" && $record->cotizable){

            return $record->cotizable->huesped;
        }

        if($record->cotizable_type == "HesperiaPlugins\Hoteles\Models\Compra"){

            return $record->cotizable->nombre_cliente;
        }
    }

    public function boot(){
        User::extend(function ($model){
        $model->hasMany['cotizaciones_count'] = ['Hesperiaplugins\Hoteles\Models\Cotizacion', 'table' => 'hesperiaplugins_hoteles_cotizacion', 'key' => 'cotizable_id','count' => true];

        $model->hasMany['cotizaciones'] = ['Hesperiaplugins\Hoteles\Models\Cotizacion', 'table' => 'hesperiaplugins_hoteles_cotizacion', 'key' => 'cotizable_id'];

        $model->hasMany['cotizaciones_completas'] = ['Hesperiaplugins\Hoteles\Models\Cotizacion', 'table' => 'hesperiaplugins_hoteles_cotizacion', 'key' => 'cotizable_id', 'scope' => 'isComplete', 'count'=> true];

        $model->hasMany['ultima_fecha_cotizacion'] = ['Hesperiaplugins\Hoteles\Models\Cotizacion', 'table' => 'hesperiaplugins_hoteles_cotizacion', 'key' => 'cotizable_id','scope' => 'ultimaFechaCotizacion'];

        $model->hasMany['revenue'] = ['Hesperiaplugins\Hoteles\Models\Cotizacion', 'table' => 'hesperiaplugins_hoteles_cotizacion', 'key' => 'cotizable_id', 'scope' => 'isComplete'];
        });
    }

    public function registerPageSnippets(){
        return [
           '\HesperiaPlugins\Hoteles\Components\ListaUpselling' => 'ListaUpsellings',
           '\HesperiaPlugins\Hoteles\Components\ListaPaquetes' => 'ListaPaquetes'
        ];
    }
    /*public function registerMailTemplates(){
      return [
          'hesperiaplugins.hoteles::mail.pre_reserva' => 'formato de email que se envía una vez se hace click en "ver datos bancarios"',
          'hesperiaplugins.hoteles::mail.aprobacion_reserva'  => 'formato de correo para la aprobación de una reservación desde el panel administrativo'
      ];
    }*/
}
