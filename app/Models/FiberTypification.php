<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberTypification extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_typification';

  protected $fillable = [
    'id',
    'descripcion',
    'status',
    'date_reg'];

  public $timestamps = false;

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
 * [getTypification Obtiene la lista de tipificaciones]
 * @return [type] [description]
 */
  public static function getTypification()
  {
    //El 16 que es la tipificacion automatica de cancelacion por incumplimiento no tiene sentido que sea marcada por el usuario, esto lo hace es el cron de citas vencidas
    return self::getConnect('R')
      ->where([
        ['status', 'A'],
        ['id', '!=', 16]])
      ->orderBy('descripcion', 'ASC')
      ->get();
  }

}
