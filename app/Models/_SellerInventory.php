<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SellerInventory extends Model
{
    use HasFactory;

    protected $table = 'islim_inv_assignments';

	protected $fillable = [
		'users_email', 
        'inv_arti_details_id', 
        'obs',
        'status',
        'date_reg',
        'first_assignment',
        'date_orange',
        'date_red',
        'last_assigned_by',
        'last_assignment'
    ];
    
    protected $primaryKey = [
        'users_email',
        'inv_arti_details_id'
    ];

    public $incrementing = false;
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\SellerInventory
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SellerInventory;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para consultar articulos asignados a un usuario dado su tipo
     * @param String $user
     * @param String $type
     * 
     * @return App\Models\SellerInventory
    */
    public static function getArticsAssign($user = false, $type = 'H'){
        if($user){
            return self::getConnect('R')
                         ->select('islim_inv_assignments.inv_arti_details_id')
                         ->join(
                            'islim_inv_arti_details',
                            'islim_inv_arti_details.id',
                            'islim_inv_assignments.inv_arti_details_id'
                         )
                         ->join(
                            'islim_inv_articles',
                            'islim_inv_articles.id',
                            'islim_inv_arti_details.inv_article_id'
                         )
                         ->where([
                            ['islim_inv_assignments.users_email', $user],
                            ['islim_inv_assignments.status', 'A'],
                            ['islim_inv_articles.artic_type', $type]
                         ])
                         ->get();
                                            
        }

        return [];
    }

    /**
     * Metodo para consultar articulos asignados a un usuario
     * @param String $user
     * 
     * @return App\Models\SellerInventory
    */
    public static function getAllArticsAssign($user = false){
        if($user){
            return self::getConnect('R')
                         ->select(
                          'islim_inv_arti_details.msisdn',
                          'islim_inv_articles.title',
                          'islim_inv_articles.artic_type',
                          'islim_inv_arti_details.iccid',
                          'islim_inv_arti_details.imei',
                          'islim_inv_assignments.date_reg'
                         )
                         ->join(
                            'islim_inv_arti_details',
                            'islim_inv_arti_details.id',
                            'islim_inv_assignments.inv_arti_details_id'
                         )
                         ->join(
                            'islim_inv_articles',
                            'islim_inv_articles.id',
                            'islim_inv_arti_details.inv_article_id'
                         )
                         ->where([
                            ['islim_inv_assignments.users_email', $user],
                            ['islim_inv_assignments.status', 'A'],
                            ['islim_inv_arti_details.status', 'A']
                         ])
                         ->get();
                                            
        }

        return [];
    }

    /**
     * Metodo para eliminar asignación de articulo a un usuario dado
     * @param String $id
     * @param String $user
     * 
     * @return App\Models\SellerInventory
    */
    public static function cleanAssign($id = false, $user = false){
        if($id && $user){
            self::getConnect('W')
                  ->where([
                    ['inv_arti_details_id', $id],
                    ['users_email', '!=', $user]
                  ])
                  ->update([
                    'status' => 'T'
                  ]);
        }

        return false;
    }

    /**
     * Metodo para marcar como vendido un articulo
     * @param String $id
     * @param String $user
     * @param String $orgType
     * 
     * @return App\Models\SellerInventory
    */
    public static function markSale($id = false, $user = false, $orgType = 'N'){
        if($id && $user){
            $exist = self::getConnect('R')
                           ->select('users_email')
                           ->where([
                            ['users_email', $user],
                            ['inv_arti_details_id', $id]
                           ])
                           ->first();

            if(!empty($exist)){
                self::getConnect('W')
                    ->where([['inv_arti_details_id', $id], ['status', 'A']])
                    ->update(['status' => 'P']);

                return true;
            }elseif($orgType == 'R'){
                //Si no se le ha asignado el articulo se hace la asignacion y se marca como vendido
                self::getConnect('W')
                      ->insert([
                        'users_email' => $user,
                        'inv_arti_details_id' => $id,
                        'date_reg' => date("Y-m-d H:i:s"),
                        'status' => 'P',
                        'obs' => 'Auto asignado - Retail'
                    ]);

                return true;
            }                               
        }
        return false;
    }

    /**
     * Metodo para consultar info de articulo dado su dn y usuario que lo tiene asignado
     * @param String $id
     * @param String $user
     * @param String $orgType
     * 
     * @return App\Models\SellerInventory
    */
    public static function getArticByDnAndUser($msisdn = false, $user = false){
      if($msisdn && $user){
        return self::getConnect('R')
                     ->select(
                      'islim_inv_arti_details.id',
                      'islim_inv_arti_details.inv_article_id',
                      'islim_inv_arti_details.msisdn',
                      'islim_inv_arti_details.serial',
                      'islim_inv_arti_details.iccid',
                      'islim_inv_arti_details.imei',
                      'islim_inv_articles.title',
                      'islim_inv_articles.description',
                      'islim_inv_articles.artic_type'
                     )
                     ->join(
                      'islim_inv_arti_details',
                      'islim_inv_arti_details.id',
                      'islim_inv_assignments.inv_arti_details_id'
                     )
                     ->join(
                      'islim_inv_articles',
                      'islim_inv_articles.id',
                      'islim_inv_arti_details.inv_article_id'
                     )
                     ->where([
                      ['islim_inv_assignments.status', 'A'],
                      ['islim_inv_assignments.users_email', $user],
                      ['islim_inv_arti_details.status', 'A'],
                      ['islim_inv_arti_details.msisdn', $msisdn],
                      ['islim_inv_articles.status', 'A']
                     ])
                     ->first();                         
      }

      return null;
    }

    /**
     * Metodo para consultar info de los articulos por tipo asignados a un usuario
     * @param String $user
     * @param String $type
     * 
     * @return App\Models\SellerInventory
    */
    public static function getArticsAssignData($user = false, $type = 'H'){
        if($user){
            return self::getConnect('R')
                         ->select(
                            'islim_inv_arti_details.msisdn',
                            'islim_inv_articles.title',
                            'islim_inv_articles.artic_type',
                            'islim_inv_arti_details.imei',
                            'islim_inv_arti_details.iccid',
                            'islim_inv_arti_details.price_pay',
                            'islim_inv_assignments.date_reg'
                         )
                         ->join(
                            'islim_inv_arti_details',
                            'islim_inv_arti_details.id',
                            'islim_inv_assignments.inv_arti_details_id'
                         )
                         ->join(
                            'islim_inv_articles',
                            'islim_inv_articles.id',
                            'islim_inv_arti_details.inv_article_id'
                         )
                         ->where([
                            ['islim_inv_assignments.users_email', $user],
                            ['islim_inv_assignments.status', 'A'],
                            ['islim_inv_arti_details.status', 'A'],
                            ['islim_inv_articles.artic_type', $type]
                         ])
                         ->get();
                                            
        }

        return [];
    }

    /**
     * Metodo para validar si un articulo esta asignado a un usuario dado su identificador
     * de asignación y el usuario
     * @param String $user
     * @param String $type
     * 
     * @return App\Models\SellerInventory
    */
    public static function getAsignmentUser($articId, $seller){
        return self::getConnect('R')
                    ->select('date_reg')
                    ->where([
                        ['inv_arti_details_id', $articId],
                        ['users_email', $seller]
                    ])
                    ->first();
    }

    /**
     * Metodo para validar si un articulo esta asignado a un usuario dado su msisdn
     * y el usuario
     * @param String $user
     * @param String $type
     * 
     * @return App\Models\SellerInventory
    */
    public static function isAssignedDn($msisdn = false, $user = false){
      if($msisdn && $user){
        return self::getConnect('R')
                      ->select(
                        'islim_inv_arti_details.id',
                        'islim_inv_arti_details.inv_article_id'
                      )
                      ->join(
                        'islim_inv_arti_details',
                        'islim_inv_arti_details.id',
                        'islim_inv_assignments.inv_arti_details_id'
                      )
                      ->where([
                        'islim_inv_assignments.users_email' => $user,
                        'islim_inv_assignments.status' => 'A',
                        'islim_inv_arti_details.status' => 'A',
                        'islim_inv_arti_details.msisdn' => $msisdn
                      ])
                      ->first();
      }

      return null;
    }

    public static function getDNsWithAlertByUsers($users = []){
        return self::getConnect('R')
                    ->select(
                        'islim_inv_assignments.users_email',
                        'islim_inv_assignments.date_orange',
                        'islim_inv_assignments.date_red',
                        'islim_inv_arti_details.msisdn',
                        'islim_inv_articles.title',
                        'islim_inv_articles.artic_type',
                        'islim_users.name',
                        'islim_users.last_name',
                        'islim_users.phone'
                    )
                    ->join(
                        'islim_inv_arti_details',
                        'islim_inv_arti_details.id',
                        'islim_inv_assignments.inv_arti_details_id'
                    )
                    ->join(
                        'islim_inv_articles',
                        'islim_inv_articles.id',
                        'islim_inv_arti_details.inv_article_id'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_inv_assignments.users_email'
                    )
                    ->where('islim_inv_assignments.status', 'A')
                    ->whereIn('islim_inv_assignments.users_email', $users)
                    ->where(function($q){
                        $q->whereNotNull('islim_inv_assignments.date_orange')
                          ->orWhereNotNull('islim_inv_assignments.date_red');
                    })
                    ->orderBy('islim_inv_assignments.users_email', 'ASC')
                    ->orderBy('islim_inv_assignments.date_orange', 'ASC')
                    ->orderBy('islim_inv_assignments.date_red', 'ASC')
                    ->get();
    }
}
