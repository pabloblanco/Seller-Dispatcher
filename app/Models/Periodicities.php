<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodicities extends Model
{
  use HasFactory;

  protected $table = 'islim_periodicities';

  protected $fillable = [
    'id',
    'periodicity',
    'price_fee',
    'days',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Periodicities
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Periodicities;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener datos de una periodicidad dado su id
   * @param String $typeCon
   *
   * @return App\Models\Periodicities
   */
  public static function getPeriodicity($id = false)
  {
    if ($id) {
      return self::getConnect('R')
        ->where('id', $id)
        ->first();
    }
    return null;
  }
}
