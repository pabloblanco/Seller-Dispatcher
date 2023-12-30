<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedSalesDetail extends Model
{
    use HasFactory;

    protected $table = 'islim_asigned_sale_details';

	protected $fillable = [
		'asigned_sale_id',
        'amount',
        'amount_text',
        'unique_transaction'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\AssignedSalesDetail
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new AssignedSalesDetail;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener el ultimo detalle de una asignación de venta 
     * dada una transacción unica
     *
     * @param String $unique
     * 
     * @return App\Models\AssignedSalesDetail
    */
    public static function getLastDetail($unique = false){
    	if($unique){
    		return self::getConnect('R')
    					->select('id', 'asigned_sale_id', 'amount')
    					->where('unique_transaction', $unique)
    					->orderBy('id', 'DESC')
    					->first();
    	}

    	return null;
    }

    /**
     * Metodo para obtener el dn de una venta dado un id del detalle de la asignación
     *
     * @param String $unique
     * 
     * @return App\Models\AssignedSalesDetail
    */
    public static function getDnBySale($sale){
        return self::getConnect('R')
                    ->distinct()
                    ->select('islim_sales.msisdn')
                    ->join(
                        'islim_sales',
                        'islim_sales.unique_transaction',
                        'islim_asigned_sale_details.unique_transaction'
                    )
                    ->where('islim_asigned_sale_details.asigned_sale_id', $sale)
                    ->get();
    }

    /**
     * Metodo para obtener el id de una asignación de venta dado una transacción unica
     *
     * @param String $unique
     * @param String $idsaleAssigne
     * 
     * @return App\Models\AssignedSalesDetail
    */
    public static function getAssigneSaleByuniq($unique, $idsaleAssigne = false){
        $data = self::getConnect('R')
                    ->select('islim_asigned_sales.id')
                    ->join(
                        'islim_asigned_sales',
                        'islim_asigned_sales.id',
                        'islim_asigned_sale_details.asigned_sale_id'
                    )
                    ->where('islim_asigned_sale_details.unique_transaction', $unique)
                    ->whereIn('islim_asigned_sales.status', ['I', 'V']);

        if($idsaleAssigne){
            $data->where('islim_asigned_sales.id', '!=', $idsaleAssigne);
        }

        return $data->get();
    }

    /**
     * Metodo para obtener la razon del rechazo de un reporte de entrega de efectivo
     * dada una transacción unica
     *
     * @param String $transaction
     * 
     * @return App\Models\AssignedSalesDetail
    */
    public static function getDenyNoti($transaction){
        return self::getConnect('R')
                    ->select(
                        'islim_asigned_sales.reason'
                    )
                    ->join(
                        'islim_asigned_sales',
                        'islim_asigned_sales.id',
                        'islim_asigned_sale_details.asigned_sale_id'
                    )
                    ->where([
                        ['unique_transaction', $transaction],
                        ['status', 'I']
                    ])
                    ->orderBy('islim_asigned_sale_details.id', 'DESC')
                    ->first(); 
    }
}
