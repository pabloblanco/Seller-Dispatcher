<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesProm extends Model
{
    use HasFactory;

    protected $table = 'islim_services_prom';

	protected $fillable = [
        'service_id',
        'qty',
        'period_days',
        'max_time',
        'date_reg',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\ServicesProm
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ServicesProm;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener datos de un servicio promocional dado su id
     * @param String $id
     * 
     * @return App\Models\ServicesProm
    */
    public static function getPromByID($id = false){
    	if($id){
	    	return self::getConnect('R')
	    				->where('id', $id)
	    				->first();
    	}
    	
    	return null;
    }
}
