<?php namespace HesperiaPlugins\Hoteles\Behaviors;

use Db;
use Carbon\Carbon;
class ReservacionesOperations extends \October\Rain\Extension\ExtensionBase
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

    public function restarInventarios(){
      $clase = get_class($this->parent);
      //echo($clase);
        if ($clase == "HesperiaPlugins\Hoteles\Models\Reservacion") {
          $this->procesoRestaInventarioReservacion($this->parent);
          //$this->parent->aprobar();
        }elseif ($clase ==  "HesperiaPlugins\Hoteles\Models\Compra") {
          $compra = $this->parent;
          foreach ($compra->reservaciones as $reservacion) {
          $this->procesoRestaInventario($reservacion);
          }

          $this->procesoRestaInventarioUpgrades($compra);
        }

    }
    public function procesoRestaInventarioUpgrades($compra){
      //`vista`.`upselling_id`, sum(vista.cantidad), `vista`.`fecha_disfrute`, `vista`.`tipo_inventario`

      //QUERY BUENOO
      /*
      select d.id as upselling_id, b.upgradable_id, b.upgradable_type, b.cantidad,
      b.fecha_disfrute, d.titulo, d.tipo_inventario, d.sumable
      from hesperiaplugins_hoteles_compra as a
      INNER join hesperiaplugins_hoteles_upgrades as b on
      (a.id = b.upgradable_id and b.upgradable_type = 'HesperiaPlugins\\Hoteles\\Models\\Compra')
      INNER join hesperiaplugins_hoteles_upselling as d on
      (b.upselling_id = d.id) where a.id = 30 GROUP by fecha_disfrute, upselling_id
      */
      //FIN QUERY BUENOO


      $ups_detalles = Db::table("hesperiaplugins_hoteles_compra as a")
      ->select("d.id as upselling_id", "b.upgradable_id", "b.upgradable_type",
       "b.cantidad", "b.fecha_disfrute", "d.titulo", "d.tipo_inventario", "d.sumable")
      ->join("hesperiaplugins_hoteles_upgrades as b", function($join){
        $join->on("a.id", "=", "b.upgradable_id")
        ->where("b.upgradable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\Compra");
      })->join("hesperiaplugins_hoteles_upselling as d", "b.upselling_id", "=", "d.id")
      ->where("a.id", "=", $compra->id)->get();

      foreach ($ups_detalles as $key => $value) {
          if ($value->tipo_inventario == 1) { //POR FECHAS
            if($value->sumable == 0){ // EN LAS COMPRAS SOLO SE PROCESAN LOS QUE NO SON SUMABLES
              $fecha = new Carbon($value->fecha_disfrute);
              DB::table("hesperiaplugins_hoteles_upselling as a")
              ->join("hesperiaplugins_hoteles_calendario as b", function($join){
                $join->on("a.id", "=", "b.calendarizable_id")
                ->where("b.calendarizable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\Upselling");

            })
            ->join("hesperiaplugins_hoteles_fecha_calendario as c", "b.id", "=", "c.calendario_id")
            ->where('c.fecha', '=', $fecha->format("Y-m-d"))
            ->where('a.id', '=', $value->upselling_id)
            ->decrement('c.disponible', $value->cantidad);
            }
        }else{
          DB::table("hesperiaplugins_hoteles_upselling as a")
          ->where("a.id", "=", $value->upselling_id)
          ->decrement("disponible", $value->cantidad);
        }
      }

    }


    public function procesoRestaInventarioReservacion($parent){
      $reservacion = $parent;
      //PASO 1, BUSCAR A CUALES HABITACIONES/FECHAS SE VAN A RESTAR
      $habitaciones = DB::table('hesperiaplugins_hoteles_detalle_reservacion')
      ->select(DB::raw('count(*) as cantidad, habitacion_id'))
      ->where("reservacion_id", "=", $reservacion->id)
      ->groupBy('habitacion_id')->get();

      $begin = new Carbon($reservacion->checkin);
      $end = new Carbon($reservacion->checkout);
      $end->subDay();
      //PASO 2, RESTAR FECHAS
      foreach ($habitaciones as $habitacion) {

        DB::table('hesperiaplugins_hoteles_fechas')
        ->where("habitacion_id", "=" , $habitacion->habitacion_id)
        ->whereBetween('fecha', [$begin->format("Y-m-d"), $end->format("Y-m-d")])
        ->decrement('disponible', $habitacion->cantidad);
        //PASO 3, SI LAS HABITACIONES TIENE DESCUENTO DE PROMOCIÓN SE DEBE RESTAR DEL INVENTARIO DE ESA PROMO.
        if ($reservacion->origen->id == 1) {
          DB::table("hesperiaplugins_hoteles_descuento_habitacion as a")
          ->join("hesperiaplugins_hoteles_descuentos as b", "a.descuento_id", "=", "b.id")
          ->where('b.fecha_desde', '<=', $begin->format("Y-m-d"))
          ->where('b.fecha_hasta', '>=', $end->format("Y-m-d"))
          ->where('b.ind_activo', '=', 1)
          ->where('a.habitacion_id', '=', $habitacion->habitacion_id)
          ->decrement('cantidad', $habitacion->cantidad);
        }

      }
      //PROCESO EN ELOQUENT CASI LISTO, LOS QUERYS PRINCIPALES FUNCIONAN
      /*$ups_paq = Db::table("hesperiaplugins_hoteles_upgrades as a")
      ->select("d.id as upselling_id",  "a.upgradable_id", "a.upgradable_type",
      "a.cantidad as cant", "a.fecha_disfrute", "d.titulo", "d.tipo_inventario", "d.sumable")

      ->join("hesperiaplugins_hoteles_detalle_reservacion as b", function($join){
        $join->on("a.upgradable_id", "=", "b.id")
        ->where("a.upgradable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\DetalleReservacion");
      })->join("hesperiaplugins_hoteles_upselling as d", "a.upselling_id", "=", "d.id")
      ->where("b.reservacion_id", "=", $reservacion->id)
      ->groupBy("fecha_disfrute");

      $ups_detalles = Db::table("hesperiaplugins_hoteles_reservacion as a")
      ->select("d.id as upselling_id", "b.upgradable_id", "b.upgradable_type",
       "b.cantidad as cant", "b.fecha_disfrute", "d.titulo", "d.tipo_inventario", "d.sumable")
      ->join("hesperiaplugins_hoteles_upgrades as b", function($join){
        $join->on("a.id", "=", "b.upgradable_id")
        ->where("b.upgradable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\Reservacion");
      })->join("hesperiaplugins_hoteles_upselling as d", "b.upselling_id", "=", "d.id")
      ->where("a.id", "=", $reservacion->id)
      ->union($ups_paq)
      ->groupBy("fecha_disfrute");

      $res = Db::table(Db::raw("({$ups_detalles->toRawSql()})as sub"))->get();
      trace_log($res);*/

    //QUERY BUENOO!
    /*
    select vista.upselling_id, sum(vista.cantidad), vista.fecha_disfrute, vista.tipo_inventario from (select d.id as upselling_id, b.upgradable_id, b.upgradable_type, b.cantidad, b.fecha_disfrute, d.titulo, d.tipo_inventario, d.sumable  from hesperiaplugins_hoteles_reservacion as a
INNER join hesperiaplugins_hoteles_upgrades as b on (a.id = b.upgradable_id and b.upgradable_type = "HesperiaPlugins\\Hoteles\\Models\\Reservacion")
inner join hesperiaplugins_hoteles_upselling as d on (b.upselling_id = d.id)
where a.id = 3303
UNION
select d.id as upselling_id,  a.upgradable_id, a.upgradable_type, a.cantidad, a.fecha_disfrute, d.titulo, d.tipo_inventario, d.sumable from hesperiaplugins_hoteles_upgrades as a
inner join hesperiaplugins_hoteles_detalle_reservacion as b on (a.upgradable_id = b.id and a.upgradable_type =
"HesperiaPlugins\\Hoteles\\Models\\DetalleReservacion")
inner join hesperiaplugins_hoteles_upselling as d on (a.upselling_id = d.id)
where b.reservacion_id = 3303)as vista GROUP by fecha_disfrute, upselling_id
    */
    //FIN QUERY BUENOO!

    $res = Db::table(Db::raw("
    (select d.id as upselling_id, b.upgradable_id, b.upgradable_type, b.cantidad,
    b.fecha_disfrute, d.titulo, d.tipo_inventario, d.sumable
    from hesperiaplugins_hoteles_reservacion as a
    INNER join hesperiaplugins_hoteles_upgrades as b on
    (a.id = b.upgradable_id and b.upgradable_type = 'HesperiaPlugins\\\Hoteles\\\Models\\\Reservacion')
    INNER join hesperiaplugins_hoteles_upselling as d on
    (b.upselling_id = d.id) where a.id = ".$reservacion->id."
    UNION
    select d.id as upselling_id,  a.upgradable_id, a.upgradable_type, a.cantidad, a.fecha_disfrute,
    d.titulo, d.tipo_inventario, d.sumable from hesperiaplugins_hoteles_upgrades as a
    INNER JOIN hesperiaplugins_hoteles_detalle_reservacion as b on
    (a.upgradable_id = b.id and a.upgradable_type = 'HesperiaPlugins\\\Hoteles\\\Models\\\DetalleReservacion')
    INNER JOIN hesperiaplugins_hoteles_upselling as d on (a.upselling_id = d.id)
    where b.reservacion_id = ".$reservacion->id.") as vista GROUP by fecha_disfrute, upselling_id, tipo_inventario, sumable"))
    ->select("vista.upselling_id", Db::raw("sum(vista.cantidad) as cantidad"), "vista.fecha_disfrute",
     "vista.tipo_inventario", "vista.sumable")->get();

     foreach ($res as $key => $value) {
       if ($value->tipo_inventario == 1) { //POR FECHAS

         if($value->sumable == 1){ // SI ES SUMABLE
           $begin = new Carbon($reservacion->checkin);
           $end = new Carbon($reservacion->checkout);

           $fecha = new Carbon($value->fecha_disfrute);
           DB::table("hesperiaplugins_hoteles_upselling as a")
           ->join("hesperiaplugins_hoteles_calendario as b", function($join){
             $join->on("a.id", "=", "b.calendarizable_id")
             ->where("b.calendarizable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\Upselling");

         })
         ->join("hesperiaplugins_hoteles_fecha_calendario as c", "b.id", "=", "c.calendario_id")
         ->where('c.fecha', '=>', $begin->format("Y-m-d"))
         ->where('c.fecha', '<', $end->format("Y-m-d"))
         ->where('a.id', '=', $value->upselling_id)
         ->decrement('c.disponible', $value->cantidad);
         }else{
           $fecha = new Carbon($value->fecha_disfrute);
           DB::table("hesperiaplugins_hoteles_upselling as a")
           ->join("hesperiaplugins_hoteles_calendario as b", function($join){
             $join->on("a.id", "=", "b.calendarizable_id")
             ->where("b.calendarizable_type", "=", "HesperiaPlugins\\Hoteles\\Models\\Upselling");

         })
         ->join("hesperiaplugins_hoteles_fecha_calendario as c", "b.id", "=", "c.calendario_id")
         ->where('c.fecha', '=', $fecha->format("Y-m-d"))
         ->where('a.id', '=', $value->upselling_id)
         ->decrement('c.disponible', $value->cantidad);
         }
     }else{
       DB::table("hesperiaplugins_hoteles_upselling as a")
       ->where("a.id", "=", $value->upselling_id)
       ->decrement("disponible", $value->cantidad);
     }
   }
 }
}
?>
