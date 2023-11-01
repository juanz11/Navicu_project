<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use HesperiaPlugins\Hoteles\Models\Reservacion;
use HesperiaPlugins\Hoteles\Models\DetalleReservacion;
use Crypt;
use Input;
use Storage;
use Illuminate\Contracts\Encryption\DecryptException;

class PreCheckinHandler extends ComponentBase{

    public $reserva;

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
          'name'=> 'Pre-chekin handler',
          'description' => 'funciones para hacer pre-checkin'
        ];
      }

    public function onRun(){

        if ($this->param('reserva_id')) {
            try {
                $decrypted = Crypt::decrypt($this->param('reserva_id'));
            }
            catch (DecryptException $ex) {
                $decrypted = null;
            }
        }

        $reserva = Reservacion::find($decrypted);
        if($reserva->info_adicional != null ){
            $this->reserva = null;
        }else{
            $this->reserva = $reserva;
        }
        

        //trace_log($this->reserva->id);
    }


    public function onSave(){
       //$file = Input::file("archivo2");
       $data = Input::all();
      
       if ($this->param('reserva_id')) {
            try {
                $decrypted = Crypt::decrypt($this->param('reserva_id'));
            }
            catch (DecryptException $ex) {
                $decrypted = null;
            }
        }
        $reserva = Reservacion::find($decrypted);
        $directory = "media/documentos/".$reserva->id;

       Storage::makeDirectory($directory);

       // trace_log($data["info_adicional"]);

       $reserva->info_adicional = [$data["info_adicional"]];

       $reserva->save();
       
       $arDetallesId = array();

       foreach ($data as $key => $value) {
            $index = explode("-", $key);
            if($index[0]== "d" ){
                if (!isset($arDetallesId[$index[2]])) {
                    $arDetallesId[$index[2]] = array();
                }
                 
            }
       }
       
       foreach ($data as $key => $value) {
           
           $index = explode("-", $key);
         

           if($index[0]== "d" ){
           
           // $detalle = DetalleReservacion::find($index[2]);
            
            if (isset($data[$key]["huespedes"])) {

                $arHuespedes = $data[$key]["huespedes"];

                if (isset($arHuespedes["documento"])) {

                    $file = $arHuespedes["documento"];
                    $filePath = Storage::put($directory,
                    $file);
                    $fileName = str_replace("media","", $filePath);
                    
                    $data[$key]["huespedes"]["documento"] = $fileName;

                    
                } 
                
                if (isset($arDetallesId[$index[2]]["huespedes"])) {
                   // trace_log("push".$index[2]);
                    array_push($arDetallesId[$index[2]]["huespedes"], $data[$key]["huespedes"]);
                }else{
                    //trace_log("not-push".$index[2]);
                    $arDetallesId[$index[2]]["huespedes"] = array($data[$key]["huespedes"]);
                }
            }

            if (isset($data[$key]["info_adicional"])) {
                $arDetallesId[$index[2]]["info_adicional"] =  [$data[$key]["info_adicional"]]; 
            }
           }
           
       }
       //trace_log($arDetallesId);

       foreach ($arDetallesId as $key => $value) {
        $detalle = DetalleReservacion::find($key);
        if(isset($value["info_adicional"])){
            //$detalle->info_adicional = $value["info_adicional"];
        }
        
        $detalle->huespedes = $value["huespedes"];
        $detalle->save();
       }
       //trace_log($arDetallesId);
       
    }
}