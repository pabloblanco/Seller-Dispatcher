<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeposit extends Model
{
    use HasFactory;

    protected $table = 'islim_user_deposit_id';
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\UserDeposit
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new UserDeposit;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }

        return null;
    }

    /**
     * Metodo para obtener el codigo de deposito de un usuario dado
     * @param String $email
     * 
     * @return App\Models\UserDeposit
    */
    public static function getCodDeposit($email){
    	return self::getConnect('R')
    				->select('id_deposit')
    				->where([
    					['status', 'A'],
    					['email', $email]
    				])
    				->first();
    }

     public static function BankUser($email){
        return UserDeposit::getConnect('R')
                ->select(
                    'islim_user_deposit_id.id_deposit',
                    'islim_users.name',
                    'islim_users.last_name'
                )
                ->join(
                    'islim_users',
                    'islim_users.email',
                    'islim_user_deposit_id.email'
                )
                ->where([
                    ['islim_user_deposit_id.email', $email],
                    ['islim_user_deposit_id.status', 'A']
                ])
                ->first();
    }
}
