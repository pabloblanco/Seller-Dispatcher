<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History_inv_status extends Model
{
  use HasFactory;

  protected $table = 'islim_history_status_inventory';

  protected $fillable = [
    'id',
    'users_email',
    'inv_arti_details_id',
    'date_reg',
    'status',
    'motivo_rechazo',
    'url_evidencia',
    'color_destino',
    'userAutorizador'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\History_inv_status
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new History_inv_status;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
/**
 * [NotPresetRequest Metodo que revisa que no este el dn aun en espera de ser procesado para cambio de status a Naranja]
 * @param [type] $idInv [description]
 */
  public static function NotPresetRequest($idInv, $status = false)
  {

    $data = self::getConnect('R')
      ->select(
        'islim_history_status_inventory.status',
        'islim_history_status_inventory.date_reg',
        'islim_history_status_inventory.motivo_rechazo',
        'islim_history_status_inventory.userAutorizador')
      ->where([['islim_history_status_inventory.inv_arti_details_id', $idInv],
        ['islim_history_status_inventory.color_destino', 'N']]);

    if ($status) {
      $data = $data->where('islim_history_status_inventory.status', $status);
    } else {
      $data = $data->where('islim_history_status_inventory.status', '!=', 'T');
    }

    $data = $data->orderBy('islim_history_status_inventory.id', 'DESC')
      ->first();

    return $data;
  }
/**
 * [LastRedStatus Revisamos si antes ha estado en rojo el DN]
 * @param [type] $idInv [description]
 */
  public static function LastRedStatus($idInv)
  {
    return self::getConnect('R')
      ->select('inv_arti_details_id AS total_red')
      ->where('inv_arti_details_id', $idInv)
      ->whereNotNull('url_evidencia')
      ->first();
  }

}
