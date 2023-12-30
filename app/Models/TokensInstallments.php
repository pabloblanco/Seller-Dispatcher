<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokensInstallments extends Model
{
    use HasFactory;

    protected $table = 'islim_tokens_installments';

	protected $fillable = [
		'tokens_cron', 
		'tokens_assigned',
		'tokens_available',
		'assigned_user',
		'process_user',
		'config_id',
		'date_reg',
		'date_update',
		'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\TokensInstallments
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new TokensInstallments;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para consultar tokens asignados y tokens disponibles para un grupo de
     * usuarios y un estatus dado
     * @param Array $users
     * @param Array $status
     * 
     * @return App\Models\TokensInstallments
    */
    public static function getTokenbyUsers($users = [], $status = []){
    	return self::getConnect('R')
    				->select('tokens_assigned','tokens_available')
    				->whereIn('status', $status)
    				->whereIn('assigned_user', $users)
    				->get();
    }

    /**
     * Metodo para consultar tokens tokens disponibles de un usuario
     * @param Array $user
     * 
     * @return App\Models\TokensInstallments
    */
    public static function getTokensByUser($user){
        return self::getConnect('R')
                    ->select('id','tokens_available')
                    ->where([
                        ['assigned_user', $user],
                        ['status', 'A'],
                        ['tokens_available', '>', 0]
                    ])
                    ->first();
    }

    /**
     * Metodo para actualizar los tokens disponibles de un usuario
     * @param Array $user
     * 
     * @return App\Models\TokensInstallments
    */
    public static function updateToken($token, $id = false, $user = false){
        if($id || $user){
            if($id){
                $wh = [['id', $id]];
            }else{
                $wh = [['assigned_user', $user]];
            }

            return self::getConnect('W')
                        ->where($wh)
                        ->update(['tokens_available' => $token]);
        }
    }
}
