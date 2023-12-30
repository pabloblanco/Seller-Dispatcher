<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Client_document extends Model
{
  use HasFactory;

  protected $table = 'islim_client_document';

  protected $fillable = [
    'dni',
    'type',
    'date_reg',
    'photo_front',
    'photo_post',
    'identification',
    'seller_mail',
    'date_update'];

  protected $primaryKey = 'dni';
  public $incrementing = false;
  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Client_document
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

/**
 * [createRegister Registra o actualiza los documentos de un cliente de netwey]
 * @param  [type] $dni            [description]
 * @param  [type] $identification [description]
 * @param  [type] $photoF         [description]
 * @param  [type] $photoP         [description]
 * @return [type]                 [description]
 */
  public static function createRegister($dni, $identification, $photoF, $photoP, $type)
  {
    $insertData = self::getConnect('R')
      ->where('dni', $dni)
      ->first();

    $data = [
      'type' => $type,
      'photo_front' => $photoF,
      'photo_post' => $photoP,
      'identification' => $identification,
      'seller_mail' => session('user')];

    if (empty($insertData)) {
      $data['dni'] = $dni;
      $data['date_reg'] = date('Y-m-d H:i:s');
      try {
        self::getConnect('W')->insert($data);
      } catch (Exception $e) {
        $txmsg = 'Error al insertar el documento del cliente. ' . (String) json_encode($e->getMessage());
        Log::error($txmsg);
        return array('success' => false, 'msg' => $txmsg);
      }
    } else {
      $data['date_update'] = date('Y-m-d H:i:s');
      try {
        self::getConnect('W')
          ->where('dni', $dni)
          ->update($data);
      } catch (Exception $e) {
        $txmsg = 'Error al actualizar el documento del cliente. ' . (String) json_encode($e->getMessage());
        Log::error($txmsg);
        return array('success' => false, 'msg' => $txmsg);
      }
    }
    return array('success' => true, 'msg' => 'OK');
  }

}
