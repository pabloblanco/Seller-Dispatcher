<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AltasSpeed extends Model
{
  use HasFactory;
  protected $table = 'islim_altas_speed';

  protected $fillable = [
    'id',
    'type_serv',
    'msisdn',
    'date_reg',
    'status'];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\models\AltasSpeed
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new AltasSpeed;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * [getDnsActivateMigrations Metodo que devuelve los Dns que son HBB, que estan procesados para ser analizados y posteriormente ser trasladados para migracion]
   * @param  [type] $idStar [id de inicio]
   * @param  [type] $idEnd  [id final]
   * @return [type]         [array con los Dns que cumplen los filtros]
   */
  public static function getDnsActivateMigrations($idStar, $idCant)
  {

    $registros = self::getConnect('R')
      ->select('islim_altas_speed.msisdn')
      ->where([
        ['islim_altas_speed.status', 'P'],
        ['islim_altas_speed.type_serv', 'HBB']])
      ->orderBy('islim_altas_speed.date_reg', 'ASC')
      ->orderBy('islim_altas_speed.msisdn', 'ASC')
      ->skip($idStar)
      ->take($idCant)
      ->get();
    // Log::info(count($registros));
    // Log::info((String) json_encode($registros));
    return $registros;
  }
}
