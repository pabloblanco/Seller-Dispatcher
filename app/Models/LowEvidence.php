<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowEvidence extends Model
{
  use HasFactory;

  protected $table = 'islim_documentation_dismissal';

  protected $fillable = [
    'id',
    'url',
    'id_req_dismissal',
    'date_reg',
    'status'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\LowEvidence
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new LowEvidence;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
