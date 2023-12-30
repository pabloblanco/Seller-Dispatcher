<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    use HasFactory;

    protected $table = 'islim_schedules';

    protected $fillable = [
        'client_dni',
        'users_email',
        'reg_email',
        'date_schedules',
        'obs',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Schedules
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Schedules;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para consultar las citas registradas
     * @param Array $filters
     * 
     * @return App\Models\Schedules
    */
    public static function getSchedules($filters = []){
    	$data = self::getConnect('R')
    				  ->select(
    				  	'islim_schedules.client_dni',
						'islim_schedules.date_schedules',
						'islim_schedules.obs',
						'islim_clients.name',
						'islim_clients.last_name',
						'islim_clients.email'
    				  )
    				  ->join(
    				  	'islim_clients',
    				  	'islim_clients.dni',
    				  	'islim_schedules.client_dni'
    				  )
    				  ->where('status', 'A');

    	if(!empty($filters['user'])){
    		$data->where('users_email', $filters['user']);
    	}

    	if(!empty($filters['dateB']) && !empty($filters['dateE'])){
    		$data->where([
    			['date_schedules', '>=', $filters['dateB']],
    			['date_schedules', '<=', $filters['dateE']]
    		]);
    	}

    	return $data->get();
    }

    /**
     * Metodo para consultar la cita activa de un cliente dado su dni
     * @param String $dni
     * 
     * @return App\Models\Schedules
    */
    public static function getActiveScheduleByClient($dni){
    	return self::getConnect('R')
    				->where([
    					['status', 'A'],
    					['client_dni', $dni],
    					['date_schedules', '>', date('Y-m-d H:i:s')]
    				])
    				->first();
    }

    /**
     * Metodo para consultar citas con los datos del cliente y del vendedo que tiene 
     * asignada la cita
     * @param Array $filters
     * 
     * @return App\Models\Schedules
    */
    public static function getListScedule($filters = []){
    	$data = self::getConnect('R')
    				->select(
    					'islim_schedules.id',
    					'islim_schedules.client_dni',
						'islim_schedules.date_schedules',
						'islim_schedules.obs',
    					'islim_users.name as name_seller',
    					'islim_users.last_name as last_name_seller',
    					'islim_clients.name as cname',
						'islim_clients.last_name as clast_name',
						'islim_clients.email',
						'islim_clients.phone_home',
						'islim_clients.address'
    				)
    				->join(
    					'islim_users',
    					'islim_users.email', 
    					'islim_schedules.users_email'
    				)
    				->join(
    					'islim_clients',
    				  	'islim_clients.dni',
    				  	'islim_schedules.client_dni'
    				)
    				->where('islim_schedules.status', 'A');

    	if(!empty($filters['dateB']) && !empty($filters['dateE'])){
    		$data->where([
    				['islim_schedules.date_schedules', '>=', $filters['dateB']],
    				['islim_schedules.date_schedules', '<=', $filters['dateE']]
    			   ]);
    	}

    	if(!empty($filters['me']) && (empty($filters['sellers']) || !count($filters['sellers']))){
    		$data->where('islim_schedules.users_email', $filters['me']);
    	}

    	if(!empty($filters['sellers']) && count($filters['sellers'])){
    		$data->whereIn('islim_schedules.users_email', $filters['sellers']);
    	}

    	return $data->orderBy('date_schedules', 'ASC')
    				->get();
    }

    /**
     * Metodo para consultar cita dado su id
     * @param Array $filters
     * 
     * @return App\Models\Schedules
    */
    public static function getScheduleById($id){
    	return self::getConnect('R')
    				->where('id', $id)
    				->first();
    }
}
