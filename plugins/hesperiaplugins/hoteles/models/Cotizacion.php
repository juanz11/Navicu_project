<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
use Carbon\Carbon;
use Backend\Models\User;
/**
 * Model
 */
class Cotizacion extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_cotizacion';

    public $morphTo = [
        'cotizable' => [],
        'usable'=>[]
    ];

    protected $fillable = [
        'usable_id',
        'usable_type',
        'cotizable_id',
        'cotizable_type'
    ];

    public function getHotelOptions($value, $formData)
    {
         $hotel = Db::table('hesperiaplugins_hoteles_hotel as a')->lists('a.nombre', 'a.id');
         array_unshift($hotel, 'Todos');
        return $hotel;
    }

    public function getMonedaOptions($value, $formData){
        $moneda = Db::table('hesperiaplugins_hoteles_moneda as m')->orderBy('m.id', 'desc')->lists('m.moneda', 'm.id');
        return $moneda;
    }

    public function buscarAgentes($form){

        $hotel ="";
        $inicio = "";
        $fin = "";
        $moneda = "3";

        if($form != null){
            $inicio = Carbon::parse($form["desde"])->toDateString();
            $fin = Carbon::parse($form["hasta"])->toDateString();

            $moneda = $form["moneda"];

            if ($form["hotel"] > 0) {

                $hotel = $form["hotel"];
            }
        }

        $query = DB::table("backend_users as u")
        ->join("hesperiaplugins_hoteles_cotizacion as c", "c.usable_id","u.id")
        ->leftJoin("hesperiaplugins_hoteles_reservacion as r", function ($join) use ($moneda){
            $join->on("r.id","=","c.cotizable_id")
            ->where("r.moneda_id", $moneda);
        })
        ->leftJoin("hesperiaplugins_hoteles_compra as cp", function ($join) use ($moneda){
            $join->on("cp.id","c.cotizable_id")
            ->where("cp.moneda_id", $moneda);
        })
        ->leftJoin(DB::raw("(select count(*) as numHab ,d.reservacion_id
                             from hesperiaplugins_hoteles_detalle_reservacion as d
                             group by d.reservacion_id) as dt"), "dt.reservacion_id","r.id")
        ->leftJoin(DB::raw("(select distinct upg.upgradable_id as upgradable, ups.hotel_id as hotel_id
        from hesperiaplugins_hoteles_upgrades as upg inner join
        hesperiaplugins_hoteles_upselling as ups on upg.upselling_id = ups.id) as us"),
        "us.upgradable", "cp.id")
        ->selectRaw("u.id, CONCAT(first_name,' ',last_name) AS nombre,
                         count(c.id) as enviadas,
                         sum((case when (r.status_id = 1) then 1 else 0 end)
                         + (case when (cp.status_id = 1) then 1 else 0 end))as completas,
                         sum((case when (r.status_id = 1) then coalesce(datediff(r.checkout, r.checkin), 0) else 0 end)
                        * (case when (r.status_id = 1) then dt.numHab else 0 end)) as roomNight,
                        sum((case when (r.status_id = 1) then coalesce(r.total, 0) else 0 end) 
                        + (case when (cp.status_id = 1) then coalesce(cp.total, 0) else 0 end)) as revenue")
        
        ->when($hotel, function($query) use ($hotel){
            $query->whereRaw("(r.hotel_id = $hotel or us.hotel_id = $hotel)");
        })
        ->when($inicio, function($query) use ($inicio, $fin){

            $query->whereBetween(DB::raw("date(c.created_at)"), [$inicio, $fin]);

        })->whereRaw("(r.moneda_id = $moneda or cp.moneda_id = $moneda)")->groupBy("u.id")->get();

        return $query;
    }
}
