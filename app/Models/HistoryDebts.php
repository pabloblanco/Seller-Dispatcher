<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryDebts extends Model
{
	use HasFactory;

    protected $table = 'islim_history_debts';

	protected $fillable = [
        'user_email',
        'date',
        'init_debt',
        'init_debt_sellers',
        'ups_debt_day',
        'cash_received',
        'cash_delivered',
        'conciliate_banks_day',
        'conciliate_sales_day',
        'finish_debt',
        'finish_debt_sellers',
        'status',
        'date_reg',
        'date_modified'
    ];
    
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';


    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new self;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getHistoryDebts($filters = [])
    {

        $data = self::getConnect('R')
                    ->select(
                        'islim_history_debts.id',
                        'islim_history_debts.user_email',
                        'islim_history_debts.date',
                        'islim_history_debts.init_debt',
                        'islim_history_debts.init_debt_sellers',
                        'islim_history_debts.ups_debt_day',
                        'islim_history_debts.cash_received',
                        'islim_history_debts.cash_delivered',
                        'islim_history_debts.conciliate_banks_day',
                        'islim_history_debts.conciliate_sales_day',
                        'islim_history_debts.finish_debt',
                        'islim_history_debts.finish_debt_sellers',
                        'islim_profile_details.id_profile'
                    )
                    ->join('islim_profile_details','islim_profile_details.user_email','islim_history_debts.user_email')
                    ->where('islim_history_debts.status', 'A')
                    ->where('islim_profile_details.status','A')
                    ->whereIn('islim_profile_details.id_profile',[10,11,18,19]);

        if(count($filters)){
            if(!empty($filters['user'])){
                $data = $data->where('islim_history_debts.user_email', $filters['user']);
            }

            if(!empty($filters['dateB']) && !empty($filters['dateE'])){
                $data = $data->where([
                    ['islim_history_debts.date', '>=', $filters['dateB']],
                    ['islim_history_debts.date', '<=', $filters['dateE']]
                ]);
            }elseif(!empty($filters['dateB'])){
                $data = $data->where('islim_history_debts.date', '>=', $filters['dateB']);
            }elseif(!empty($filters['dateE'])){
                $data = $data->where('islim_history_debts.date', '<=', $filters['dateE']);
            }
        }

        return $data;
    }
}