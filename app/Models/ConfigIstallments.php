<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigIstallments extends Model
{
    use HasFactory;

    protected $table = 'islim_config_installments';

	protected $fillable = [
		'percentage',
		'end_day',
		'week_sales',
		'days_quote',
		'quotes',
		'firts_pay',
		'user_reg',
		'm_permit_c',
		'm_permit_s',
		'date_reg',
		'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\ConfigIstallments
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ConfigIstallments;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener la configuración de pago en abonos activa
     * 
     * @return App\Models\ConfigIstallments
    */
    public static function getActiveConf(){
    	return self::getConnect('R')
    				 ->where('status', 'A')
                     ->orderBy('date_reg', 'DESC')
                     ->first();
    }

    /**
     * Metodo obtener una configuracion de pagos en abonos dado su id
     *
     * @param String $id
     * 
     * @return App\Models\ConfigIstallments
    */
    public static function getConfigById($id){
        return self::getConnect('R')
                    ->select('id', 'quotes', 'days_quote')
                    ->where('id', $id)
                    ->first();
    }
}
