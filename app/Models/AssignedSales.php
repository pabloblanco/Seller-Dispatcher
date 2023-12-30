<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssignedSales extends Model
{
    use HasFactory;

    protected $table = 'islim_asigned_sales';

	protected $fillable = [
		'id', 
        'parent_email', 
        'users_email',
        'user_process',
        'date_process', 
        'n_tranfer', 
        'bank_id', 
        'amount', 
        'amount_text',
        'date_accepted',
        'date_reject',
        'date_reg', 
        'date_dep', 
        'status',
        'alert_orange_send',
        'alert_red_send'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\AssignedSales
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new AssignedSales;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para consultar el monto total de las altas hechas por un usuario
     * Filtradas por tipo de venta Internet hogar(H), telefonía(T)
     * @param String $user
     * @param String $type
     * 
     * @return App\Models\AssignedSales
    */
    public static function getAmountAssignedSales($user, $type){
    	return self::getConnect('R')
    				->select(
    					DB::raw('SUM(islim_asigned_sale_details.amount) as total_due_assig')
    				)
    				->join(
    					'islim_asigned_sale_details',
                        'islim_asigned_sale_details.asigned_sale_id',
                        'islim_asigned_sales.id'
    				)
    				->join(
    					'islim_sales',
                        'islim_sales.unique_transaction',
                        'islim_asigned_sale_details.unique_transaction'
    				)
    				->where([
    					['islim_asigned_sales.parent_email', $user],
    					['islim_asigned_sales.status', 'P'],
    					['islim_sales.type', 'V'],
    					['islim_sales.status', '!=', 'T'],
    					['islim_sales.sale_type', $type]
    				])
    				->groupBy('islim_asigned_sales.parent_email')
    				->first();
    }

    /**
     * Metodo para consultar una venta asignada por medio de su id
     * @param Integer $id
     * 
     * @return App\Models\AssignedSales
    */
    public static function getSale($id = false){
        if($id){
            return self::getConnect('R')
                        ->select('id', 'amount')
                        ->where('id', $id)
                        ->first();
        }

        return false;
    }

    /**
     * Metodo para consultar ventas asignadas dado un usuario 
     * superior(Coordinador u otro tipo de usuario asociado a un vendedor)
     * 
     * @param String $user
     * @param Integer $limit
     * @param String $seller
     * 
     * @return App\Models\AssignedSales
    */
    public static function getSalesAssignedByUser($user, $limit = 0, $seller = false){
        $data = self::getConnect('R')
                      ->select(
                        'islim_asigned_sales.id',
                        'islim_asigned_sales.amount',
                        'islim_asigned_sales.date_reg',
                        'islim_users.email',
                        'islim_users.name',
                        'islim_users.last_name',
                        'islim_users.phone'
                      )
                      ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_asigned_sales.users_email'
                      )
                      ->where([
                        ['islim_asigned_sales.status', 'V'],
                        ['islim_asigned_sales.parent_email', $user]
                      ]);

        if($limit){
            $data->limit($limit);
        }

        if($seller){
            $data->where('islim_asigned_sales.users_email', $seller);
        }

        return $data->orderBy('islim_asigned_sales.date_reg', 'DESC')
                    ->get();
    }

    /**
     * Metodo para marcar como aceptado un reporte de entrega de efectivo
     * 
     * @param String $id
     * @param Integer $data
     * 
     * @return App\Models\AssignedSales
    */
    public static function aceptReceptionVU($id, $user, $data = []){
        $data = self::getConnect('W')
                    ->where([
                        ['status', 'V'],
                        ['id', $id],
                        ['parent_email', $user]
                    ])
                    ->update($data);
    }

    /**
     * Metodo para obtener las transacciones unicas de las ventas reportadas a un usuario del
     * nivel coordinador o mayor
     * 
     * @param String $id
     * @param String $user
     * 
     * @return App\Models\AssignedSales
    */
    public static function getSaleDataVU($id, $user){
        return self::getConnect('R')
                    ->select(
                        'islim_asigned_sale_details.unique_transaction'
                    )
                    ->join(
                        'islim_asigned_sale_details',
                        'islim_asigned_sale_details.asigned_sale_id',
                        'islim_asigned_sales.id'
                    )
                    ->where([
                        //['islim_asigned_sales.status', 'V'],
                        ['islim_asigned_sales.parent_email', $user],
                        ['islim_asigned_sales.id', $id]
                    ])
                    ->get();     
    }

    /**
     * Metodo para marcar como eliminados los reportes de entrega de efectivo dados 
     * sus ids
     * 
     * @param Array $ids
     * 
     * @return App\Models\AssignedSales
    */
    public static function deleteAssigns($ids = []){
        return self::getConnect('W')
                    ->whereIn('id', $ids)
                    ->update(['islim_asigned_sales.status' => 'T']);
    }

    /**
     * Metodo para obtener ventas asociadas a un usuario 
     * 
     * @param String $user
     * 
     * @return App\Models\AssignedSales
    */
    public static function getAssigneReportByUser($user){
        return self::getConnect('R')
                    ->select('islim_asigned_sale_details.unique_transaction')
                    ->join(
                        'islim_asigned_sale_details',
                        'islim_asigned_sale_details.asigned_sale_id',
                        'islim_asigned_sales.id'
                    )
                    ->where('islim_asigned_sales.users_email', $user)
                    ->whereIn('islim_asigned_sales.status', ['V', 'P', 'A'])
                    ->get();
    }

    /**
     * Metodo para obtener entregas de efectivo que no fueron aceptados 
     * por el usuario superior del vendedor y no han sido vistas por el vendedor
     * 
     * @param String $user
     * 
     * @return App\Models\AssignedSales
    */
    public static function getDenyNotiByUser($user){
        return self::getConnect('R')
                    ->select('id')
                    ->where([
                        ['users_email', $user],
                        ['status', 'I'],
                        ['view', 'N']
                    ]);
    }

    /**
     * Metodo para marcar las entregas de efectivo como vistas por un usuario dado
     * 
     * @param String $user
     * 
     * @return App\Models\AssignedSales
    */
    public static function setView($user){
        return self::getConnect('W')
                    ->where([
                        ['users_email', $user],
                        ['status', 'I'],
                        ['view', 'N']
                    ])
                    ->update(['view' => 'Y']);
    }

    /**
     * Metodo para retornar info de ventas asociadas al dinero recibido por el coordinador o usuario que recibio el dinero
     * 
     * @param String $user
     * 
     * @return App\Models\AssignedSales
    */
    public static function getSalesInfo($user){
        return self::getConnect('W')
                    ->select(
                        'islim_sales.date_reg',
                        'islim_sales.amount',
                        'islim_sales.msisdn',
                        'islim_sales.sale_type',
                        'islim_clients.name',
                        'islim_clients.last_name',
                        'islim_services.title as service',
                        'islim_packs.title as pack'
                    )
                    ->join(
                        'islim_asigned_sale_details',
                        'islim_asigned_sale_details.asigned_sale_id',
                        'islim_asigned_sales.id'
                    )
                    ->join(
                        'islim_sales',
                        'islim_sales.unique_transaction',
                        'islim_asigned_sale_details.unique_transaction'
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
                        ['islim_asigned_sales.status', 'P'],
                        ['islim_asigned_sales.parent_email', $user],
                        ['islim_sales.type', 'V']
                    ])
                    ->get();
    }

    public static function getAsignedSaleById($id){
        return self::getConnect('R')
                    ->select(
                        'users_email',
                        'parent_email',
                        'amount',
                        'status'
                    )
                    ->where('id', $id)
                    ->first();
    }
}
