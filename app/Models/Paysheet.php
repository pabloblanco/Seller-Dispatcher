<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paysheet extends Model
{
    use HasFactory;

    protected $table = 'islim_paysheet';

	protected $fillable = [
		'id',
		'rfc',
		'cert_number',
		'serie',
		'folio',
		'name_file',
		'url_download',
		'date_nom',
		'date_reg',
		'type',
		'rel_type',
		'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Paysheet
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Paysheet;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener comprobante de un vendedor
     * @param String $typeCon
     * 
     * @return App\Models\Paysheet
    */
    public static function getPaySheets($dni, $limit = false){
    	$data = self::getConnect('R')
    				->select(
    					'name_file',
    					'date_nom',
    					'status',
    					'type',
    					'rel_type'
    				)
    				->where('rfc', $dni)
    				->whereIn('status', ['N', 'R'])
    				->orderBy('date_nom', 'DESC')
    				->groupBy('rel_type');

    	if($limit){
    		$data->limit($limit);
    	}

    	return $data->get();
    }

    /**
     * Metodo para obtener comprobantes de pago de un vendedor dado un tipo de comprobante
     * @param String $relType
     * @param String $type
     * @param String $dni
     * 
     * @return App\Models\Paysheet
    */
    public static function getPaySheetByRelTypeAndUser($relType, $type, $dni){
    	return self::getConnect('R')
    				->select('name_file', 'type')
    				->where([
    					['rel_type', $relType],
    					['rfc', $dni],
    					['type', '!=', $type]
    				])
    				->whereIn('status', ['N', 'R'])
    				->first();
    }

    /**
     * Metodo para obtener la url de descarga de un comprobante de un vendedor dado
     * su usuario y tipo de comprobante
     * @param String $relType
     * @param String $type
     * @param String $dni
     * 
     * @return App\Models\Paysheet
    */
    public static function getUrlByTypeAndUser($name, $user, $type){
    	return self::getConnect('W')
    				->select('id', 'url_download')
    				->where([
    					['name_file', $name],
    					['rfc', $user],
    					['type', $type]
    				])
    				->whereIn('status', ['N', 'R'])
    				->first();       
    }
}
