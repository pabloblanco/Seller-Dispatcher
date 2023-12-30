<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelephoneCompany extends Model
{
  use HasFactory;

  protected $table = 'islim_telephone_companys';

  protected $fillable = [
    'name',
    'date_reg',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\TelephoneCompany
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new TelephoneCompany;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener listado de telefonias
   *
   * @return App\Models\Product
   */
  public static function getCompanys()
  {
    return self::getConnect('R')
      ->where('status', 'A')
      ->orderBy('name', 'ASC')
      ->get();
  }
}
