<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'islim_policies';

    protected $fillable = [
        'id',
        'roles_id',
        'name',
        'code',
        'type',
        'description',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Policy
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Policy;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener el valor de la politica asociada a un usuario dado su código
     * @param String $typeCon
     * 
     * @return App\Models\Policy
    */
    public static function getUserPolicy($user, $policy){
    	return self::getConnect('R')
    				->select(
    					'islim_policies.code', 
    					'islim_user_roles.value'
    				)
    				->join(
    					'islim_user_roles',
			            'islim_policies.id',
			            'islim_user_roles.policies_id'
    				)
    				->where([
    					['islim_user_roles.user_email', $user],
    					['islim_policies.code', $policy],
            			['islim_policies.status', 'A'],
                        ['islim_user_roles.status', 'A']
    				])
    				->first();
    }
}
