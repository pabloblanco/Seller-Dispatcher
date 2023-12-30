<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDeposits extends Model
{
    use HasFactory;

    protected $table = 'islim_bank_deposits';

    protected $fillable = [
        'id_deposit',
        'email',
        'user_load',
        'amount',
        'bank',
        'cod_auth',
        'date_dep',
        'line',
        'user_delete',
        'date_delete',
        'user_process',
        'date_process',
        'date_reg',
        'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\BankDeposits
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new BankDeposits;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener listado de conciliasiones que pueden ser filtradas segun
     * el parametro $filters
     * @param Array $filters
     * 
     * @return App\Models\BankDeposits
    */
    public static function getConcilations($filters = []){
    	$data = self::getConnect('R')
    				 ->select(
						'islim_bank_deposits.amount',
                        'islim_bank_deposits.id',
						'islim_bank_deposits.id_deposit',
						'islim_bank_deposits.cod_auth',
						'islim_bank_deposits.date_process',
                        'islim_bank_deposits.reason_deposit',
						'islim_banks.name as bank',
						'islim_users.name as ope_name',
						'islim_users.last_name as ope_last_name'
    				 )
    				 ->leftJoin(
						'islim_banks',
						'islim_banks.id',
						'islim_bank_deposits.bank'
    				 )
    				 ->join(
						'islim_users',
						'islim_users.email',
						'islim_bank_deposits.user_process'
    				 )
    				 ->where('islim_bank_deposits.status', 'A');

    	if(count($filters)){
    		if(!empty($filters['user'])){
    			$data->where('islim_bank_deposits.email', $filters['user']);
    		}

    		if(!empty($filters['dateB']) && !empty($filters['dateE'])){
                $data->where([
                    ['islim_bank_deposits.date_process', '>=', $filters['dateB']],
                    ['islim_bank_deposits.date_process', '<=', $filters['dateE']]
                ]);
            }elseif(!empty($filters['dateB'])){
                $data->where('islim_bank_deposits.date_process', '>=', $filters['dateB']);
            }elseif(!empty($filters['dateE'])){
                $data->where('islim_bank_deposits.date_process', '<=', $filters['dateE']);
            }
    	}

    	return $data;
    }
}