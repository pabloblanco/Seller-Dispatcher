<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Low_Reason extends Model
{
  use HasFactory;

  protected $table = 'islim_reason_dismissal';

  protected $fillable = [
    'id',
    'reason',
    'date_reg',
    'status'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Low_Reason
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Low_Reason;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getReason Retorna todos las razones activas para ver en el formulario]
 * @return [type] [description]
 */
  public static function getReason()
  {
    return self::getConnect('R')
      ->where([
        ['islim_reason_dismissal.status', 'A']])
      ->get();
  }

}
