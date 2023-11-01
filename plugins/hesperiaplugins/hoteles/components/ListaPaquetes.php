<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use Hesperiaplugins\Hoteles\Models\Paquete;

class ListaPaquetes extends ComponentBase{


	public function componentDetails(){
	    return [
	        'name' => 'Lista Paquetes',
	        'description' => 'Lista de Paquetes'
	    ];
	}

	public function defineProperties(){
	    return [
        	
	    ];
    }

    public function getList(){
    	
    	$paquetes = Paquete::active()->orderBy("created_at", "desc");

    	return $paquetes->get();
    }

	public function getTourByCodes($stringCodes){

		$arrayCodes = explode(",", $stringCodes);
    	$idsImploded = implode(',',$arrayCodes);
		

		$upsellings = Paquete::active()->whereIn("codigo", $arrayCodes)
    	->orderByRaw("FIND_IN_SET(id,'$idsImploded')")->get();

		return $upsellings;
	}

  
}