<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadband extends Model
{
    use HasFactory;

    protected $table = 'Islim_broadbands';

	protected $fillable = [
		'id',
        'broadband',
        'num_broad',
        'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Broadband
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Broadband;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
    	return null;
    }

    /**
     * Metodo para obtener data de los broadband menores o iguales a la velocidad dada
     * @param Integer $broad
     * 
     * @return App\Models\Broadband
    */
    public static function getBroadBand($broad = false){
    	if($broad){
    		return self::getConnect('R')
    					->where([
    						['num_broad', '<=', $broad],
    						['status', 'A']
    					])
    					->orderBy('num_broad', 'DESC')
    					->get();                 
    	}

    	return [];
    }
}
