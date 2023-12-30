<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleInstallment extends Model
{
    use HasFactory;

    protected $table = 'islim_sales_installments';

	protected $fillable = [
		'seller',
		'coordinador',
		'quotes',
		'config_id',
		'unique_transaction',
		'lat',
		'lng',
		'pack_id',
		'type_pack',
		'service_id',
		'client_dni',
		'msisdn',
		'first_pay',
		'amount',
		'view_seller',
		'date_reg_alt',
		'date_reg',
		'date_update',
		'alert_exp',
		'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SaleInstallment;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para verificar si un dn no esta reservado o vendido en abonos
     * @param String $msisdn
     * 
     * @return App\Models\SaleInstallment
    */
    public static function isAvailable($msisdn = false){
        if($msisdn){
            return self::getConnect('R')
                        ->select('id')
                        ->where('msisdn', $msisdn)
                        ->whereIn('status', ['R', 'A', 'P', 'F'])
                        ->first();
        }

        return null;
    }

    /**
     * Metodo para obtener datos de ventas pendientes por pagos de un usuario
     * @param String $email
     * @param String $type
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getOnlyPendingSales($email, $type){
        $data = self::getConnect('R')
                    ->select(
                        'id',
                        'config_id',
                        'quotes',
                        'date_reg_alt'
                    )
                    ->where('status', 'P');

        if($type == 'vendor'){
            $data->where('islim_sales_installments.seller', $email);
        }else{
            $data->where('islim_sales_installments.coordinador', $email);
        }

        return $data->get();
    }

    /**
     * Metodo para obtener datos de ventas pendientes por pagos de un usuario
     * @param String $email
     * @param String $type
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getPendingSales($email, $type){
    	$data = self::getConnect('R')
    				->select(
    					'islim_sales_installments.id',
                        'islim_sales_installments.config_id',
                        'islim_sales_installments.quotes',
                        'islim_sales_installments.date_reg_alt',
                        'islim_sales_installments.seller',
                        'islim_clients.name as name_c',
                        'islim_clients.last_name as last_name_c',
                        'islim_users.name',
                        'islim_users.last_name'
    				)
    				->join(
    					'islim_clients',
                        'islim_clients.dni',
                        'islim_sales_installments.client_dni'
    				)
    				->join(
    					'islim_users',
                        'islim_users.email',
                        'islim_sales_installments.seller'
    				)
                    ->where('islim_sales_installments.status', 'P');

    	if($type == 'vendor'){
    		$data->where('islim_sales_installments.seller', $email);
    	}else{
    		$data->where('islim_sales_installments.coordinador', $email);
    	}

    	return $data->get();
    }

    /**
     * Metodo para obtener monto total y cantidad de ventas en abono de un usuario dado
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getAmountAndQty($user){
        return self::getConnect('R')
                    ->select(
                        DB::raw('SUM(islim_sales_installments_detail.amount) as total_mount'),
                        DB::raw('COUNT(islim_sales_installments_detail.unique_transaction) as total_sales')
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->where([
                        ['islim_sales_installments.seller', $user],
                        ['islim_sales_installments_detail.status', 'A']
                    ])
                    ->whereIn('islim_sales_installments.status', ['P','F'])
                    ->whereIn('islim_sales_installments_detail.conciliation_status', ['V','CV'])
                    ->first();
    }

    /**
     * Metodo para obtener las transacciones unicas de las ventas en abono echas por un usario
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getSalesDetailByUser($user){
        return self::getConnect('R')
                    ->select('islim_sales_installments.unique_transaction')
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->where('islim_sales_installments.seller', $user)
                    ->whereIn('islim_sales_installments.status', ['P','F'])
                    ->whereIn('islim_sales_installments_detail.conciliation_status', ['V','CV'])
                    ->get();
    }

    /**
     * Metodo para obtener monto total de ventas en abono aceptadas(Flujo de efectivo) por el superior del vendedor
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getAmountWS($user){
        return self::getConnect('R')
                    ->select(
                        DB::raw('SUM(islim_sales_installments_detail.amount) as total_mount')
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->where([
                        ['islim_sales_installments.coordinador', $user],
                        ['islim_sales_installments_detail.conciliation_status', 'C'],
                        ['islim_sales_installments_detail.status', 'A']
                    ])
                    ->whereIn('islim_sales_installments.status', ['P','F'])
                    ->first();
    }

    /**
     * Metodo para obtener ventas abiertas
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getOpenSales($users = [], $status =[]){
        return self::getConnect('R')
                    ->select(
                        'islim_sales_installments.quotes',
                        'islim_sales_installments.date_reg_alt',
                        'islim_sales_installments.first_pay',
                        'islim_sales_installments.amount',
                        'islim_sales_installments.msisdn',
                        'islim_config_installments.quotes as cq',
                        'islim_config_installments.days_quote',
                        'islim_users.name',
                        'islim_users.last_name',
                        'coord.name as name_coord',
                        'coord.last_name as last_coord'
                    )
                    ->join(
                        'islim_config_installments',
                        'islim_config_installments.id',
                        'islim_sales_installments.config_id'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_sales_installments.seller'
                    )
                    ->join(
                        'islim_users as coord',
                        'coord.email',
                        'islim_sales_installments.coordinador'
                    )
                    ->whereIn('islim_sales_installments.coordinador', $users)
                    ->whereIn('islim_sales_installments.status', $status)
                    ->get();
    }

    /**
     * Metodo para obtener solicitudes de ventas en abono
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getRequestSales($user){
        return self::getConnect('R')
                    ->select(
                        'islim_sales_installments.id',
                        'islim_sales_installments.amount',
                        'islim_sales_installments.msisdn',
                        'islim_sales_installments.first_pay',
                        'islim_sales_installments.date_reg',
                        'islim_clients.name as name_c',
                        'islim_clients.last_name as last_name_c',
                        'islim_users.name',
                        'islim_users.last_name',
                        'islim_packs.title as pack',
                        'islim_services.title as service',
                        'islim_inv_articles.title'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_sales_installments.client_dni'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_sales_installments.seller'
                    )
                    ->join(
                        'islim_packs', 
                        'islim_packs.id', 
                        'islim_sales_installments.pack_id'
                    )
                    ->join(
                        'islim_services',
                        'islim_services.id',
                        'islim_sales_installments.service_id'
                    )
                    ->join(
                        'islim_inv_arti_details',
                        'islim_inv_arti_details.msisdn',
                        'islim_sales_installments.msisdn'
                    )
                    ->join(
                        'islim_inv_articles',
                        'islim_inv_articles.id',
                        'islim_inv_arti_details.inv_article_id'
                    )
                    ->where([
                        ['islim_sales_installments.status', 'R'],
                        ['islim_sales_installments.coordinador', $user]
                    ])
                    ->orderBy('islim_sales_installments.date_reg', 'DESC')
                    ->get();
    }

    /**
     * Metodo para obtener solicitudes de ventas en abono pedientes
     * @param Array $filters
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getPendingSalesWf($filters = []){
        $data = self::getConnect('R')
                    ->select(
                        'islim_sales_installments.id',
                        'islim_sales_installments.config_id',
                        'islim_sales_installments.quotes',
                        'islim_sales_installments.amount',
                        'islim_sales_installments.msisdn',
                        'islim_sales_installments.first_pay',
                        'islim_sales_installments.date_reg_alt',
                        'islim_sales_installments.seller',
                        'islim_clients.name as name_c',
                        'islim_clients.last_name as last_name_c',
                        'islim_clients.address',
                        'islim_users.name',
                        'islim_users.last_name'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_sales_installments.client_dni'
                    )
                    ->join(
                        'islim_users', 
                        'islim_users.email', 
                        'islim_sales_installments.seller'
                    )
                    ->where('islim_sales_installments.status', 'P');

        if(!empty($filters['coord'])){
            $data->whereIn('islim_sales_installments.coordinador', $filters['coord']);
        }

        if(!empty($filters['seller'])){
            $data->where('islim_sales_installments.seller', $filters['seller']);
        }

        if(!empty($filters['detail'])){
            $data->where('islim_sales_installments.id', $filters['detail']);
        }

        if(!empty($filters['client_dni'])){
            $data->where('islim_clients.dni', $filters['client_dni']);
        }

        if(!empty($filters['client_email'])){
            $data->where('islim_users.email', $filters['client_email']);
        }

        return $data->orderBy('islim_sales_installments.date_update', 'ASC')
                    ->get();
    }

    /**
     * Metodo para verificar si una solicitud de venta en abono pertenece al usuario dado
     * @param String $id
     * @param String $coord
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getSalesRequest($id, $coord = false){
        $data = self::getConnect('R')
                    ->select(
                        'id'
                    )
                    ->where([
                        ['id', $id],
                        ['status', 'R']
                    ]);

        if($coord){
            $data->where('coordinador', $coord);
        }

        return $data->first();
    }

    /**
     * Metodo para actualizar estatus de venta en abono
     * @param String $id
     * @param String $status
     * 
     * @return App\Models\SaleInstallment
    */
    public static function updateSale($id, $status){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->update([
                        'status' => $status, 
                        'date_update' => date('Y-m-d H:i:s')
                    ]);
    }

    /**
     * Metodo para consultar datos de venta en abono activa dado un vendedor
     * @param String $id
     * @param String $seller
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getActiveSale($id, $seller = false){
        $data = self::getConnect('R')
                    ->where([
                        ['id', $id],
                        ['status', 'P']
                    ]);

        if(!empty($seller)){
            $data->where('seller', $seller);
        }

        return $data->first();
    }

    /**
     * Metodo para consultar datos de venta en abono activa aprobada dado un vendedor
     * @param String $id
     * @param String $seller
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getAprovedSale($id, $seller = false){
        $data = self::getConnect('R')
                    ->where([
                        ['id', $id],
                        ['status', 'A']
                    ]);

        if(!empty($seller)){
            $data->where('seller', $seller);
        }

        return $data->first();
    }

    /**
     * Metodo para consultar cantidad de ventas en abono filtradas 
     * por estatus y usuario
     * @param String $id
     * @param String $seller
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getCountSales($status, $seller = false, $coord = false){
        $data = self::getConnect('R')
                      ->select('id')
                      ->where('status', $status);

        if($seller){
            $data->where('seller', $seller);
        }

        if($coord){
            $data->where('coordinador', $coord);
        }

        return $data->count();
    }

    /**
     * Metodo para consultar ventas en abono de un vendedor dado
     * @param String $seller
     * @param String $filters
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getSalesforSeller($seller, $filters = []){
        $data = self::getConnect('R')
                    ->select(
                        'islim_sales_installments.id',
                        'islim_sales_installments.amount',
                        'islim_sales_installments.first_pay',
                        'islim_sales_installments.msisdn',
                        'islim_clients.name as name_c',
                        'islim_clients.last_name as last_name_c',
                        'islim_packs.title as pack',
                        'islim_config_installments.quotes',
                        'islim_config_installments.days_quote'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_sales_installments.client_dni'
                    )
                    ->join(
                        'islim_config_installments',
                        'islim_config_installments.id',
                        'islim_sales_installments.config_id'
                    )
                    ->join(
                        'islim_packs', 
                        'islim_packs.id', 
                        'islim_sales_installments.pack_id'
                    )
                    ->where('islim_sales_installments.seller', $seller);

        if(!empty($filters['status'])){
            $data->where('islim_sales_installments.status', $filters['status']);
        }

        if(!empty($filters['date'])){
            $data->where('islim_sales_installments.date_update', '>=', $filters['date']);
        }
                    

        return $data->orderBy('islim_sales_installments.date_update', 'DESC')
                    ->get();
    }

    /**
     * Metodo para rechazar una solicitud de venta en abono
     * @param String $id
     * 
     * @return App\Models\SaleInstallment
    */
    public static function denyRequest($id){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->update(['status' => 'T']);
    }

    /**
     * Metodo para Asignar primera cuota a venta en abono
     * @param String $id
     * 
     * @return App\Models\SaleInstallment
    */
    public static function setFirstQuote($id){
        $date = date('Y-m-d H:i:s');

        return self::getConnect('W')
                    ->where('id', $id)
                    ->update([
                        'quotes' => 1,
                        'date_reg_alt' => $date,
                        'date_update' => $date,
                        'status' => 'P'
                    ]);
    }

    /**
     * Metodo para obtener reporte de ventas en abono pendientes por cobrar dado un vendedor
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getPendingReportSales($user){
        return self::getConnect('R')
                    ->select(
                        'islim_sales_installments_detail.id',
                        'islim_sales_installments.unique_transaction',
                        'islim_sales_installments.msisdn',
                        'islim_sales_installments_detail.amount',
                        'islim_sales_installments_detail.n_quote',
                        'islim_clients.name',
                        'islim_clients.last_name',
                        'islim_packs.title as pack',
                        'islim_services.title as service',
                        'islim_inv_articles.title as product'
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->join(
                        'islim_sales',
                        'islim_sales.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_sales_installments.client_dni'
                    )
                    ->join(
                        'islim_packs',
                        'islim_packs.id',
                        'islim_sales.packs_id'
                    )
                    ->join(
                        'islim_services',
                        'islim_services.id',
                        'islim_sales.services_id'
                    )
                    ->join(
                        'islim_inv_arti_details',
                        'islim_inv_arti_details.id',
                        'islim_sales.inv_arti_details_id'
                    )
                    ->join(
                        'islim_inv_articles',
                        'islim_inv_articles.id',
                        'islim_inv_arti_details.inv_article_id'
                    )
                    ->where([
                        ['islim_sales_installments.seller', $user],
                        ['islim_sales_installments_detail.conciliation_status', 'CV'],
                        ['islim_sales_installments_detail.status', 'A'],
                        ['islim_sales.type', 'P']
                    ])
                    ->whereIn('islim_sales_installments.status', ['P', 'F'])
                    ->get(); 
    }

    /**
     * Metodo para obtener transacciones unicas de ventas en abono  dado un usuario
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getTransactionSales($user){
        return self::getConnect('R')
                    ->select('islim_sales_installments.unique_transaction')
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->where([
                        ['islim_sales_installments.seller', $user],
                        ['islim_sales_installments_detail.conciliation_status', '!=', 'P'],
                        ['islim_sales_installments_detail.status', 'A']
                    ])
                    ->whereIn('islim_sales_installments.status', ['P', 'F'])
                    ->get();  
    }

    /**
     * Metodo para retornar info de ventas en abono asociadas al dinero recibido por el coordinador o usuario que recibio el dinero
     * 
     * @param String $user
     * 
     * @return App\Models\SaleInstallment
    */
    public static function getSalesInfo($user){
        return self::getConnect('R')
                    ->select(
                        'islim_sales.date_reg',
                        'islim_sales_installments_detail.amount',
                        'islim_sales.msisdn',
                        'islim_sales.sale_type',
                        'islim_clients.name',
                        'islim_clients.last_name',
                        'islim_services.title as service',
                        'islim_packs.title as pack',
                        'islim_sales_installments_detail.n_quote'
                    )
                    ->join(
                        'islim_sales_installments_detail',
                        'islim_sales_installments_detail.unique_transaction',
                        'islim_sales_installments.unique_transaction'
                    )
                    ->join(
                        'islim_sales',
                        'islim_sales.unique_transaction',
                        'islim_sales_installments_detail.unique_transaction'
                    )
                    ->join(
                        'islim_client_netweys',
                        'islim_client_netweys.msisdn',
                        'islim_sales.msisdn'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_client_netweys.clients_dni'
                    )
                    ->join(
                        'islim_services',
                        'islim_services.id',
                        'islim_sales.services_id'
                    )
                    ->join(
                        'islim_packs',
                        'islim_packs.id',
                        'islim_sales.packs_id'
                    )
                    ->where([
                        ['islim_sales_installments.coordinador', $user],
                        ['islim_sales_installments_detail.conciliation_status', 'C'],
                        ['islim_sales_installments_detail.status', 'A'],
                        ['islim_sales.type', 'V']
                    ])
                    ->get();
    }
}
