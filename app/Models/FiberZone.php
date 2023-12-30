<?php

namespace App\Models;

use App\Utilities\Api815;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberZone extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_zone';

  protected $fillable = [
    'id',
    'name',
    'url_api',
    'param',
    'type_soft',
    'status',
    'ambiente',
    'configuration'];

  public $timestamps = false;
  protected $casts = [
    'configuration' => 'array',
    'param' => 'array',
  ];

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getInfoZone($idZone)
  {
    return self::getConnect('R')
      ->select('name', 'configuration', 'param', 'url_api')
      ->where([
        ['id', $idZone],
        ['status', 'A']])
      ->first();
  }
  public static function getAllZone()
  {
    $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';

    return self::getConnect('R')
      ->where([
        ['ambiente', $ambiente],
        ['status', 'A']])
      ->get();
  }

  public static function chekingZone($fiberZoneID)
  {
    $zona = self::getInfoZone($fiberZoneID);
    $disponible = false;
    if (!empty($zona)) {
      $disponible = API815::verifyEndPointFiberZone($zona->url_api);
    }
    if ($disponible) {
      $credencial = API815::verifyEndPointCredencial($fiberZoneID);

      if ($credencial) {
        return ['success' => true, 'title' => 'Endpoint OK', 'msg' => 'El endPoint ' . $zona->url_api . ' funciona correctamente!', 'icon' => 'success'];
      }
      return ['success' => false, 'title' => 'Conexion fallo', 'msg' => 'El endPoint ' . $zona->url_api . ' responde al llamado pero no se puede autenticar de forma correcta, por favor verificar el usuario y contrasena!', 'icon' => 'error'];
    } else {
      return ['success' => false, 'title' => 'Endpoint fallo', 'msg' => 'El endPoint ' . $zona->url_api . ' no esta disponible, por favor verifique que la url este correcta!', 'icon' => 'error'];
    }
  }
}
