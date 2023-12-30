<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ClientNetweyBundle extends Model
{
  use HasFactory;
  protected $table = 'islim_client_netweys_bundle';
  protected $fillable = [
    'id',
    'date_expire',
    'status',
    'bundle_id',
    'parent_id'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\models\ClientNetweyBundle
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function newBundle($bundleId)
  {
    $data = [
      // 'parent_id' => null,
      // 'date_expire' => null,
      'bundle_id' => $bundleId];
    try {
      $id = self::getConnect('W')->insertGetId($data);
      return array('success' => true, 'id' => $id);

    } catch (Exception $e) {
      $Txmsg = 'Error al insertar nuevo ClientNetweyBundle. ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return array('success' => false, 'msg' => $Txmsg);
    }
  }

  public static function updateBundle($IdClientBundle, $msisdn_parent, $date_expire)
  {
    try {
      $update = self::getConnect('W')
        ->where('id', $IdClientBundle)
        ->update([
          'parent_id' => $msisdn_parent,
          'date_expire' => $date_expire]);
      return array('success' => true, 'msg' => 'OK');
    } catch (Exception $e) {
      $Txmsg = 'Error al actualizar el ClientNetweyBundle ' . $IdClientBundle . ' - ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return array('success' => false, 'msg' => $Txmsg);
    }
  }

}
