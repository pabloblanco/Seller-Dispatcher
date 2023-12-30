<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerInventoryTemp extends Model
{
  protected $table = 'islim_inv_assignments_temp';

  protected $fillable = [
    'user_email',
    'inv_arti_details_id',
    'status',
    'assigned_by',
    'date_reg',
    'date_status',
    'reason_reject',
    'reject_notification_view'
  ];

  protected $primaryKey = 'id';

  public $incrementing = true;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\SellerInventoryTemp
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SellerInventoryTemp;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }


   /**
   * Metodo para consultar info de los articulos por tipo pre asignados a un usuario
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function getArticsPreAssignData($user = false, $type = 'H')
  {
    if ($user) {
      $ret = self::getConnect('R')
        ->select(
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.title',
          'islim_inv_articles.artic_type',
          'islim_inv_arti_details.imei',
          'islim_inv_arti_details.iccid',
          'islim_inv_arti_details.price_pay',
          'islim_inv_assignments_temp.date_reg',
          DB::raw('CONCAT("Y") as preassigned')
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments_temp.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments_temp.user_email', $user],
          ['islim_inv_assignments_temp.status', 'P'],
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_articles.artic_type', $type]
        ]);

        return $ret->get();

    }

    return [];
  }


  public static function preAssinedNotificationsPendingExists($user = false)
  {
    if ($user) {
      $alerts = self::getConnect('R')
        ->select(
          'id'
        )
        ->where([
          'user_email' => $user,
          'status' => 'P',
          'notification_view' => 'N'
        ])
        ->get();

        if(count($alerts)){

            self::getConnect('W')
            ->where([
                ['user_email', $user],
                ['status', 'P'],
                ['notification_view', 'N']
            ])
            ->update([
                'notification_view' => 'Y'
            ]);

            return true;
        }
    }

    return false;
  }


  public static function preAssinedNotificationsRejectsExists($user = false)
  {
    if ($user) {
      $alerts = self::getConnect('R')
        ->select(
          'id'
        )
        ->where([
          'assigned_by' => $user,
          'status' => 'R',
          'reject_notification_view' => 'N'
        ])
        ->get();

        if(count($alerts)){

            self::getConnect('W')
            ->where([
                ['assigned_by', $user],
                ['status', 'R'],
                ['reject_notification_view', 'N']
            ])
            ->update([
                'reject_notification_view' => 'Y'
            ]);

            return true;
        }
    }

    return false;
  }

}