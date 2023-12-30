<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History_conect_platform extends Model
{
  use HasFactory;

  protected $table = 'islim_history_conect_platform';

  protected $fillable = [
    'id',
    'date_conect',
    'type_dispositive',
    'dispositive',
    'os_dispositive',
    'browser',
    'plataform',
    'user_seller',
    'ip'];

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
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function insertHistoryConection($dataSend, $infoconex)
  {
    return self::getConnect('W')
      ->insertGetId([
        'date_conect' => date('Y-m-d H:i:s'),
        'type_dispositive' => $dataSend['type_device'],
        'dispositive' => $dataSend['device'],
        'os_dispositive' => $dataSend['os'],
        'browser' => $dataSend['browser'],
        'plataform' => 'SELLER',
        'user_seller' => session('user'),
        'ip' => $infoconex,
      ]);
  }
}
