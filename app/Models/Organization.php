<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'islim_dts_organizations';

	protected $fillable = [
        'id',
        'rfc',
        'business_name',
        'address',
        'contact_name',
        'contact_email',
        'contact_address',
        'contact_phone',
        'type',
        'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Organization
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Organization;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para consultar organización dado su id
     * @param String $id
     * 
     * @return App\Models\Organization
    */
    public static function getOrg($id){
    	return self::getConnect('R')
    				->select(
    					'islim_dts_organizations.type',
                    	'islim_wh_org.id_wh'
    				)
    				->join(
    					'islim_wh_org',
	                    'islim_wh_org.id_org',
	                    'islim_dts_organizations.id'
    				)
    				->where([
    					['islim_dts_organizations.id', $id],
                    	['islim_dts_organizations.status', 'A']
    				])
    				->first();
    }
}
