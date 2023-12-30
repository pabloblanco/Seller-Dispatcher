<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PayInstallment extends Model
{
    use HasFactory;

    protected $table = 'islim_pay_installments';

	protected $fillable = [
		'sale_installment_detail',
		'amount',
		'id_report',
		'user_process',
		'reason',
		'view',
		'date_reg',
		'date_nom',
		'date_reg',
		'date_acept',
		'date_update',
		'alert_orange_send',
		'alert_red_send',
		'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\PayInstallment
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new PayInstallment;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener total de monto notificado en ventas en abono,
     * y datos de los vendedores que hicieron la notificación de entrega de efectivo 
     * al superior dado 
     * @param String $user
     * 
     * @return App\Models\PayInstallment
    */
    public static function getPayNotiBySup($user){
        return self::getConnect('R')
                    ->select(
                        DB::raw('SUM(islim_sales_installments_detail.amount) as amount'),
                        'islim_pay_installments.date_update',
                        'islim_users.email',
                        'islim_users.name',
                        'islim_users.last_name'
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.id',
                        'islim_pay_installments.sale_installment_detail'
                    )
                    ->join(
                        'islim_sales_installments',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_sales_installments.seller'
                    )
                    ->where([
                        ['islim_sales_installments_detail.conciliation_status', 'V'],
                        ['islim_sales_installments_detail.status', 'A'],
                        ['islim_sales_installments.coordinador', $user],
                        ['islim_pay_installments.status', 'V']
                    ])
                    ->whereIn('islim_sales_installments.status', ['P', 'F'])
                    ->groupBy('islim_pay_installments.id_report')
                    ->get();
    }

    /**
     * Metodo para obtener listado de pagos en abono cobrados y los datos de sus vendedores
     * @param Array $filters
     * 
     * @return App\Models\PayInstallment
    */
    public static function getInstallmentReception($filters = []){
        $data = self::getConnect('R')
                      ->select(
                        'islim_pay_installments.id_report',
                        'islim_sales_installments_detail.amount',
                        'islim_sales_installments_detail.n_quote',
                        'islim_pay_installments.date_update',
                        'islim_users.email',
                        'islim_users.name',
                        'islim_users.last_name',
                        'islim_sales_installments.msisdn'
                      )
                      ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.id',
                        'islim_pay_installments.sale_installment_detail'
                      )
                      ->join(
                        'islim_sales_installments',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                      )
                      ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_sales_installments.seller'
                      )
                      ->whereIn('islim_sales_installments.status', ['P', 'F'])
                      ->where([
                        ['islim_sales_installments_detail.conciliation_status', 'V'],
                        ['islim_sales_installments_detail.status', 'A'],
                        ['islim_pay_installments.status', 'V']
                      ]);

        if(count($filters)){
            if(!empty($filters['user'])){
                $data->where('islim_sales_installments.coordinador', $filters['user']);
            }

            if(!empty($filters['seller'])){
                $data->where('islim_sales_installments.seller', $filters['seller']);
            }
        }

        return $data->orderBy('islim_pay_installments.date_update', 'DESC')
                    ->get();
    }

    /**
     * Metodo para obtener listado de pagos en abono dado un id de reporte y un usuario
     * @param String $report
     * @param String $user
     * 
     * @return App\Models\PayInstallment
    */
    public static function getListReception($report, $user){
        return self::getConnect('R')
                    ->select(
                        'islim_pay_installments.id',
                        'islim_pay_installments.amount',
                        'islim_sales_installments.seller',
                        'islim_pay_installments.sale_installment_detail',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.id',
                        'islim_pay_installments.sale_installment_detail'
                    )
                    ->join(
                        'islim_sales_installments',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->where([
                        ['islim_pay_installments.status', 'V'],
                        ['islim_sales_installments_detail.status', 'A'],
                        ['islim_sales_installments_detail.conciliation_status', 'V'],
                        ['islim_pay_installments.id_report', $report],
                        ['islim_sales_installments.coordinador', $user]
                    ])
                    ->get();
    }

    /**
     * Metodo actualizar estatus de recepción de un pago en abono
     * @param String $id
     * @param Array $data
     * 
     * @return App\Models\PayInstallment
    */
    public static function updateRecptionStatus($id, $data = []){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->update($data);
    }

    /**
     * Metodo para obtener la notificación de entrega rechazadas por el superior dado su id
     * @param String $id
     * 
     * @return App\Models\PayInstallment
    */
    public static function getDenyNoti($id){
        return self::getConnect('R')
                    ->select('reason', 'status')
                    ->where([
                        ['sale_installment_detail', $id],
                        ['status', 'R']
                    ])
                    ->orderBy('id', 'DESC')
                    ->first();   
    }

    /**
     * Metodo para marcar como vista una notificación de entrega de efectivo
     * @param Array $ids
     * 
     * @return App\Models\PayInstallment
    */
    public static function setView($ids = []){
        return self::getConnect('W')
                    ->whereIn('id', $ids)
                    ->update(['view' => 'Y']);
    }

    /**
     * Metodo para obtener notificaciones rechazadas por el superior dado un vendedor
     * @param Array $ids
     * 
     * @return App\Models\PayInstallment
    */
    public static function getDenyNotiByUser($user){
        return self::getConnect('R')
                    ->select('islim_pay_installments.id')
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.id',
                        'islim_pay_installments.sale_installment_detail'
                    )
                    ->join(
                        'islim_sales_installments',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->where([
                        ['islim_sales_installments.seller', $user],
                        ['.islim_pay_installments.status', 'R'],
                        ['islim_pay_installments.view', 'N']
                    ])
                    ->get();
    }
}
