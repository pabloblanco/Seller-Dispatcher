<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'islim_user_roles';

    protected $fillable = [
        'user_email', 
        'policies_id', 
        'roles_id', 
        'value', 
        'date_reg', 
        'status'
    ];

    protected $primaryKey = 'user_email';
    protected $keyType = 'string';

    public $incrementing = false;
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\UserRole
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new UserRole;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener las politicas activas asociadas a un usuario dado
     * @param String $email
     * 
     * @return App\Models\UserRole
    */
    public static function getRolPolicies($email, $exclude = false){
    	$data = self::getConnect('R')
    				->select(
    					'islim_policies.code',
    					'islim_policies.name',
                        'islim_policies.type'
    				)
    				->join(
    					'islim_policies',
		                'islim_user_roles.policies_id',
		                'islim_policies.id'
    				)
    				->where([
		                ['islim_user_roles.user_email', $email],
		                ['islim_user_roles.status', 'A'],
		                ['islim_user_roles.value', 1],
                    ]);
                    
        if($exclude){
            $data->where('islim_policies.exclude', 'N');
        }

		return $data->get(); 
    }
}
