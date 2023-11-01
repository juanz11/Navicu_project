<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use HesperiaPlugins\Hoteles\Models\Upselling as UpsellingModel;
use HesperiaPlugins\Hoteles\Models\CategoriaUpselling;
use HesperiaPlugins\Hoteles\Models\Moneda;
use Flash;
use Session;
use Redirect;
use Carbon\Carbon;

class ListaUpselling extends ComponentBase{


  public function defineProperties(){

    return [];
  }

  public function componentDetails(){
    return [
      'name'=> 'Lista Upselling',
      'description' => 'Lista de upsellings'
    ];
  }


  public function getList(){
    	
    $upsellings = UpsellingModel::active()->categoriaUpselling([1])->orderBy("created_at", "desc");

    return $upsellings->get();
  }

	public function getUpsellingByCodes($stringCodes){
    

    $arrayCodes = explode(",", $stringCodes);
    $idsImploded = implode(',',$arrayCodes);

    $upsellings = UpsellingModel::whereIn("codigo", $arrayCodes)
    ->orderByRaw("FIND_IN_SET(id,'$idsImploded')");


    return $upsellings->get();
  }

}
