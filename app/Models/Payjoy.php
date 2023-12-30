<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payjoy extends Model
{
    use HasFactory;

    protected $table = 'islim_payjoy';

	protected $fillable = [
		'dni',
        'msisdn',
        'iccid',
        'pack',
        'amount',
        'total_amount',
        'phone_payjoy',
        'customer_id',
        'customer_name',
        'monthly_cost',
        'weekly_cost',
        'finance_id',
        'months',
        'status',
        'date_reg',
        'date_process'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Payjoy
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Payjoy;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener financiamiento sin asociar de un dn dado
     * @param String $msisdn
     * 
     * @return App\Models\Payjoy
    */
    public static function getActiveFinancingBydn($msisdn = false){
    	if($msisdn){
    		return self::getConnect('R')
    					->select(
    						'id',
    						'months',
    						'amount',
    						'total_amount',
    						'monthly_cost',
    						'weekly_cost',
    						'phone_payjoy'
    					)
    					->where([
    						['msisdn', $msisdn],
    						['status', 'A']
    					])
    					->first();
    	}

    	return null;
    }

    /**
     * Metodo para obtener financiamiento sin asociar o asociado de un dn dado
     * @param String $msisdn
     * 
     * @return App\Models\Payjoy
    */
    public static function getFinancingBydn($msisdn = false){
    	if($msisdn){
    		return self::getConnect('R')
    					->select(
    						'id',
    						'months',
    						'amount',
    						'total_amount',
    						'monthly_cost',
    						'weekly_cost',
    						'phone_payjoy'
    					)
    					->where('msisdn', $msisdn)
    					->whereIn('status', ['A', 'P'])
    					->first();
    	}

    	return null;
    }

    /**
     * Metodo para obtener financiamiento sin asociar o asociado dada una referencia
     * @param String $reference
     * 
     * @return App\Models\Payjoy
    */
    public static function getActiveFinancingByReference($reference = false){
    	if($reference){
    		return self::getConnect('R')
    					->select(
    						'id',
    						'months',
    						'amount',
    						'total_amount',
    						'monthly_cost',
    						'weekly_cost',
    						'phone_payjoy'
    					)
    					->where('finance_id', $reference)
    					->whereIn('status', ['A', 'P'])
    					->first();
    	}

    	return null;
    }

    /**
     * Metodo para marcar como asociado un financiamiento
     * @param String $msisdn
     * @param String $pack
     * @param String $dni
     * 
     * @return App\Models\Payjoy
    */
    public static function marckAsProcess($msisdn, $pack = false, $dni = false){
    	return self::getConnect('W')
    				->where([['msisdn', $msisdn], ['status', 'A']])
    				->update([
    					'dni' => $dni ? $dni : null,
    					'pack' => $pack ? $pack : null,
    					'status' => 'P',
    					'date_process' => date('Y-m-d H:i:s')
    				]);
    }
}
