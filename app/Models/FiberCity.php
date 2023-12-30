<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberCity extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_city';

  protected $fillable = [
    'id',
    'localy_id',
    'status'];

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new FiberCity;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getStates Retorna los stados donde esta configurado vender fibra]
 * @return [type] [description]
 */
  public static function getStates()
  {
    return self::getConnect('R')
      ->select('state.id AS localy_state_id',
        'state.location AS location_state')
      ->join('islim_localy_mexico AS city',
        'city.id',
        'islim_fiber_city.localy_id')
      ->join('islim_localy_mexico AS state',
        'state.id',
        'city.parent_id')
      ->join('islim_fiber_city_zone',
        'islim_fiber_city_zone.fiber_city_id',
        'islim_fiber_city.id')
      ->where([
        ['islim_fiber_city.status', 'A'],
        ['islim_fiber_city_zone.status', 'A']])
      ->whereNotNull('state.code')
      ->groupBy('state.id')
      ->get();
  }

/**
 * [getCitys Retorna la ciudades del stado donde se vende fibra]
 * @param  [type] $State_localy_id [description]
 * @return [type]                  [description]
 */
  public static function getCitys($State_localy_id)
  {
    return self::getConnect('R')
      ->select('islim_fiber_city.id AS fiber_city_id',
        'city.location AS fiber_city')
      ->join('islim_localy_mexico as city',
        'city.id',
        'islim_fiber_city.localy_id')
      ->join('islim_localy_mexico as state',
        'state.id',
        'city.parent_id')
      ->join('islim_fiber_city_zone',
        'islim_fiber_city_zone.fiber_city_id',
        'islim_fiber_city.id')
      ->where([
        ['islim_fiber_city.status', 'A'],
        ['islim_fiber_city_zone.status', 'A'],
        ['state.id', $State_localy_id]])
      ->groupBy('city.id')
      ->get();
  }

/**
 * [getOlts Retorna las OLTs de la ciudad donde se instala fibra]
 * @param  [type] $city_localy_id [description]
 * @return [type]                 [description]
 */
  public static function getOlts($city_localy_id)
  {
    $type = env('APP_ENV', 'local');
    $entorno = "QA";
    if ($type == 'production') {
      $entorno = 'P';
    }
    return self::getConnect('R')
      ->select('fiberzone.id AS zone_id',
        'fiberzone.name AS name_zone')
      ->join('islim_fiber_city_zone as fiber_city_zone',
        'fiber_city_zone.fiber_city_id',
        'islim_fiber_city.id')
      ->join('islim_fiber_zone as fiberzone',
        'fiberzone.id',
        'fiber_city_zone.fiber_zone_id')
      ->where([['fiber_city_zone.status', 'A'],
        ['fiberzone.status', 'A'],
        ['fiberzone.ambiente', $entorno],
        ['fiber_city_zone.fiber_city_id', $city_localy_id]])
      ->get();
  }

  public static function getCityNameById($id){

    return self::getConnect('R')
    ->select(
      'islim_localy_mexico.location'
    )
    ->join('islim_localy_mexico', 'islim_localy_mexico.id', 'islim_fiber_city.localy_id')
    ->where('islim_fiber_city.id', $id)
    ->first();
  }

}
