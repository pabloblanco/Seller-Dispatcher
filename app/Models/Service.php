<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
  use HasFactory;

  protected $table = 'islim_services';

  protected $fillable = [
    'id',
    'periodicity_id',
    'codeAltan',
    'title',
    'description',
    'price_pay',
    'price_remaining',
    'broadband',
    'supplementary',
    'date_reg',
    'status',
    'type',
    'method_pay',
    'gb',
    'plan_type',
    'service_type',
    'for_subscription',
    'is_payment_forcer',
    'service_recharge',
    'is_bundle'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Service
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Service;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener datos de un servicio dado su id
   * @param String $id
   *
   * @return App\Models\Service
   */
  public static function getService($id = false, $statusList = ['A'])
  {
    if ($id) {
      return self::getConnect('R')
        ->select(
          'id',
          'codeAltan',
          'method_pay',
          'periodicity_id',
          'broadband',
          'title',
          'price_pay',
          'for_subscription',
          'type',
          'is_payment_forcer',
          'service_recharge',
          'is_bundle'
        )
        ->where('id', $id)
        ->whereIn('status', $statusList)
        ->first();
    }

    return null;
  }

  public static function getPKService815($idService = false, $fiberZone = false, $statusList = ['A'])
  {
    if ($idService && $fiberZone) {
      return self::getConnect('R')
        ->select('islim_fiber_service_zone.service_pk')
        ->join('islim_fiber_service_zone',
          'islim_fiber_service_zone.service_id',
          'islim_services.id')
        ->where([
          ['islim_fiber_service_zone.service_id', $idService],
          ['islim_fiber_service_zone.fiber_zone_id', $fiberZone]])
        ->whereIn('islim_services.status', $statusList)
        ->whereIn('islim_fiber_service_zone.status', $statusList)
        ->first();
    }
    return null;
  }
}
