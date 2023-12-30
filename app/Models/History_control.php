<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class History_control extends Model
{
  use HasFactory;
  protected $table = 'islim_history_fiber_control';

  protected $fillable = [
    'id',
    'user_origin',
    'date_reg',
    'user_destino',
    'installations_id',
    'status',
    'status_control'];

  public $timestamps = false;
/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\History_control
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new History_control;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
  public static function insertHistory($user_destino, $installations_id, $status)
  {
    try {
      self::getConnect('W')
        ->where([
          ['installations_id', $installations_id],
          ['status', '!=', 'T']])
        ->update(['status' => 'T']);
    } catch (Exception $e) {
      $Txmsg = 'Error al actualizar el historial de control de cita. ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return array('success' => false, 'msg' => $Txmsg);
    }

    try {
      self::getConnect('W')
        ->insert([
          'user_origin' => session('user'),
          'user_destino' => $user_destino,
          'date_reg' => date('Y-m-d H:i:s'),
          'installations_id' => $installations_id,
          'status_control' => $status,
        ]);
      return array('success' => true, 'msg' => 'OK');

    } catch (Exception $e) {
      $Txmsg = 'Error al insertar el historial de control de cita. ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return array('success' => false, 'msg' => $Txmsg);
    }
  }

}
