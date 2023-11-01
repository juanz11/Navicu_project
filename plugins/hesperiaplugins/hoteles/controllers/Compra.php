<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Input;
use Db;
use Validator;
use ValidationException;
use Flash;
use Redirect;
use Mail;
use HesperiaPlugins\Hoteles\Models\Compra as CompraModel;
use HesperiaPlugins\Hoteles\Models\Observacion;
class Compra extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController',
      'Backend\Behaviors\RelationController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item6');
    }

    public function onAprobar($id){
      $form = Input::get();
      $compra = CompraModel::find($id);
      $pago_insite = false;
      if(isset($form["insite"])){
        $pago_insite = $form["insite"];
      }

      $compra->restarInventarios(); //RESTA LA OCUPACION
      if ($compra->aprobar($pago_insite, false)) {
        Flash::success("Proceso Completado");

      }else{
        Flash::error("No se ha podido completar su solicitud");
      }
      return Redirect::refresh();

    }

    function onReenviarCotizacion($id){

      $compra = CompraModel::find($id);
      //trace_log($compra->getResumen());
      Mail::send("hesperiaplugins.hoteles::mail.pre_compra",
        $compra->getResumen(), function($message) use ($compra) {
        $message->to($compra->usuario->email);

      });
      Flash::success("Proceso Completado");
      
    }

    function onCambiarStatus($id){

      $form = Input::get();

      $data = array(
        "motivo" => $form["descripcion"],
      );

      $messages = [
          'required' => ' :attribute es requerido.',
      ];

      $validator = Validator::make(
          $data,
          [
            'motivo' => 'required',

          ],
          $messages
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }

      //$data = $form["Compra"];

      $compra = CompraModel::find($id);

      //$compra->status = $data["status"];

      $usuario = $this->user;

      //$compra->save();


      $observacion = Observacion::create(['descripcion' => $form["descripcion"], 'usable_type' => 'Backend\Models\User',
      'usable_id' => $usuario->id, 'observable_id'=> $compra->id, 'observable_type' => 'HesperiaPlugins\Hoteles\Models\Compra']);

      $observacion->save();

      Flash::success("Proceso Completado");

      return Redirect::refresh();

    }
}
