<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaguitosLogs extends Model
{
  use HasFactory;
  protected $table = 'islim_paguitos_logs';

  protected $fillable = [
    'id',
    'ip',
    'header',
    'data_send',
    'data_in',
    'request',
    'date_reg',
    'time',
    'type'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\PaguitosLogs
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new PaguitosLogs;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function saveLog($ip, $header, $data_send, $data_in, $request, $tiempo, $type)
  {
    $log = self::getConnect('W');

    $log->ip        = $ip;
    $log->header    = $header;
    $log->data_send = $data_send;
    $log->data_in   = $data_in;
    $log->request   = $request;
    $log->time      = $tiempo;
    $log->type      = $type;
    $log->save();

    return $log;
  }
}
