<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//use Illuminate\Support\Facades\Log;

class FiberCityZone extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_city_zone';

  protected $fillable = [
    'id',
    'fiber_zone_id',
    'fiber_city_id',
    'pk_city',
    'status',
    'poligono'];

  public $timestamps = false;
  protected $casts   = [
    'poligono' => 'array',
  ];

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new FiberCityZone;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function getCoordenada($codeCity = false, $codeZone = false)
  {
    if ($codeCity && $codeZone) {
      //Log::info($codeCity . ' - ' . $codeZone);
      $Coordenadas = self::getConnect('R')
        ->select('poligono')
        ->where([['fiber_zone_id', $codeZone],
          ['fiber_city_id', $codeCity]])
        ->whereNotNull('poligono')
        ->first();

      return $Coordenadas;
    }
    return null;
  }

/** * Get a center latitude,longitude from an array of like geopoints * * @param array data 2 dimensional array of latitudes and longitudes
 * * For Example:
 *  $data = array * (
 *   0 = > array(45.849382, 76.322333),
 *   1 = > array(45.843543, 75.324143),
 *   2 = > array(45.765744, 76.543223),
 *   3 = > array(45.784234, 74.542335));
 * *  */

  public static function getCoordCenter($data = false)
  {

    if (!is_array($data)) {
      return null;
    }

    $num_coords = count($data);
    $X          = 0.0;
    $Y          = 0.0;
    $Z          = 0.0;

    foreach ($data as $coord) {
      $lat = $coord['lat'] * pi() / 180;
      $lon = $coord['lng'] * pi() / 180;
      $a   = cos($lat) * cos($lon);
      $b   = cos($lat) * sin($lon);
      $c   = sin($lat);
      $X += $a;
      $Y += $b;
      $Z += $c;
    }

    $X /= $num_coords;
    $Y /= $num_coords;
    $Z /= $num_coords;
    $lon = atan2($Y, $X);
    $hyp = sqrt($X * $X + $Y * $Y);
    $lat = atan2($Z, $hyp);

    return array('lat' => $lat * 180 / pi(), 'lng' => $lon * 180 / pi());
  }

  /**
   * Descripción: determina si el punto está dentro del área del polígono
   * @param $x
   * @param $y
   * @Param $ arr Coordenadas de orden geométrica
   * @return int
   *
   */
  public static function servicialidadFibra($lat, $lng, $poligono)
  {
    // Número de puntos
    $count    = count($poligono);
    $n        = 0; // El número de puntos cruzados por la línea
    $location = 'FUERA';
    for ($i = 0, $j = $count - 1; $i < $count; $j = $i, $i++) {
      //Dos puntos y una línea Saque los puntos fijos de dos puntos de conexión
      $px1 = $poligono[$i]['lat'];
      $py1 = $poligono[$i]['lng'];
      $px2 = $poligono[$j]['lat'];
      $py2 = $poligono[$j]['lng'];
      //Dibuja un rayo en la posición horizontal de $ lat
      if ($lat >= $px1 || $lat >= $px2) {
        // El área para determinar si $ lng está en línea
        if (($lng >= $py1 && $lng <= $py2) || ($lng >= $py2 && $lng <= $py1)) {

          if (($lng == $py1 && $lat == $px1) || ($lng == $py2 && $lat == $px2)) {

            //Si el valor de $ lat es igual que la coordenada del punto
            $location = 'VERTICE'; // En el punto
            return $location;

          } else {
            $px = $px1 + ($lng - $py1) / ($py2 - $py1) * ($px2 - $px1);
            if ($px == $lat) {
              $location = 'BORDE'; // En línea
            } elseif ($px < $lat) {
              $n++;
            }
          }
        }
      }
    }
    if ($n % 2 != 0) {
      $location = 'DENTRO';
    }
    return $location;
  }

}
