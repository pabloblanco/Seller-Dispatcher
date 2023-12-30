<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInstallmentDetail extends Model
{
    use HasFactory;

    protected $table = 'islim_sales_installments_detail';

    protected $fillable = [
		'unique_transaction',
		'amount',
		'n_quote',
		'conciliation_status',
		'date_reg',
		'date_update',
		'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\SaleInstallmentDetail
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SaleInstallmentDetail;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener detalle de venta en abonos dado un usuario y los ids de las ventas en abono
     * @param String $user
     * @param Array $sales
     * 
     * @return App\Models\SaleInstallmentDetail
    */
    public static function getSalesDetailSeller($user, $sales = []){
        return self::getConnect('R')
                    ->select(
                        'islim_sales_installments_detail.id',
                        'islim_sales_installments_detail.amount'
                    )
                    ->join(
                        'islim_sales_installments',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->where([
                        ['islim_sales_installments.seller', $user],
                        ['islim_sales_installments_detail.conciliation_status', 'CV'],
                        ['islim_sales_installments_detail.status', 'A']
                    ])
                    ->whereIn('islim_sales_installments_detail.id', $sales)
                    ->whereIn('islim_sales_installments.status', ['P', 'F'])
                    ->get();
    }

    /**
     * Metodo para marcar como vendida la venta en abono
     * @param String $id
     * 
     * @return App\Models\SaleInstallmentDetail
    */
    public static function markDetailSaled($id){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->update([
                        'conciliation_status' => 'V', 
                        'date_update' => date('Y-m-d H:i:s')
                    ]);
    }

    /**
     * Metodo para actualizar detalle de venta en abono
     * @param String $id
     * 
     * @return App\Models\SaleInstallmentDetail
    */
    public static function updateRecptionStatus($id, $data = []){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->update($data);
    }
}
