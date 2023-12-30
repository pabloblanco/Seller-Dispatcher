<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SellerInventoryTrack extends Model
{
  use HasFactory;

  protected $table = 'islim_inv_assignments_tracks';

  protected $fillable = [
    'inv_arti_details_id',
    'origin_user',
    'origin_wh',
    'destination_user',
    'destination_wh',
    'assigned_by',
    'comment',
    'date_reg',
  ];

  protected $primaryKey = 'id';

  public $incrementing = true;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\SellerInventory
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SellerInventoryTrack;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para crear registro de movimiento de inventarios
   * @param Integer $arti_detail_id
   * @param String $origin_user
   * @param Integer $origin_wh
   * @param String $destination_user
   * @param Integer $destination_wh
   * @param String $assigned_by
   * @param String $comment
   *
   * @return App\Models\SellerInventoryTrack
   */
  public static function setInventoryTrack($arti_detail_id, $origin_user, $origin_wh, $destination_user, $destination_wh, $assigned_by, $comment = null)
  {
    try {
      $track = self::getConnect('W');
      $track->inv_arti_details_id = $arti_detail_id;
      $track->origin_user = $origin_user;
      $track->origin_wh = $origin_wh;
      $track->destination_user = $destination_user;
      $track->destination_wh = $destination_wh;
      $track->assigned_by = $assigned_by;
      $track->comment = $comment;
      $track->date_reg = date('Y-m-d H:m:i', time());
      $track->save();
      return array('success' => true, 'msg' => "OK");

    } catch (Exception $e) {
      $txmsg = "No se pudo realizar el traking del producto creado " . (String) json_encode($e->getMessage());
      Log::error($txmsg);
      return array('success' => false, 'msg' => $txmsg);
    }
  }
}
