<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Portability extends Model
{
  use HasFactory;

  protected $table = 'islim_portability';

  protected $fillable = [
    'id',
    'sale_id',
    'dn_portability',
    'dn_netwey',
    'company_id',
    'nip',
    'photo_front',
    'photo_back',
    'date_reg',
    'date_process',
    'status',
    'Observation',
    'details_error',
    'portID',
    'latest_soap',
    'update_soap',
    'boton_disable'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Portability
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

  public static function insertNewPortability($data)
  {
    try {
      return self::getConnect('W')->insert($data);
    } catch (Exception $e) {
      $Txmsg = 'Error al insertar la portabilidad (58). ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return response()->json(['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg]);
    }
  }
}
