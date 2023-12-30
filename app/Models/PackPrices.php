<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackPrices extends Model
{
  use HasFactory;

  protected $table = 'islim_pack_prices';

  protected $fillable = [
    'id',
    'pack_id',
    'service_id',
    'type',
    'price_pack',
    'price_serv',
    'total_price',
    'status',
    'id_financing'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\PackPrices
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new PackPrices;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener info de los servicios asociados a un listado de packs
   * @param Array $idsPack
   *
   * @return App\Models\PackPrices
   */
  public static function getServicesByPacks($idsPack = [])
  {
    if (is_array($idsPack) && count($idsPack)) {
      return self::getConnect('R')
        ->select(
          'islim_pack_prices.service_id',
          'islim_services.title'
        )
        ->join(
          'islim_services',
          function ($join) {
            $join->on(
              'islim_pack_prices.service_id',
              'islim_services.id'
            )
              ->where('islim_services.status', 'A');
          }
        )
        ->whereIn('islim_pack_prices.pack_id', $idsPack)
        ->get();
    }

    return [];
  }

  /**
   * Metodo para obtener info del servicio asociado a un pack dado el id del pack y del servicio
   * @param Int $idPack
   * @param Int $idService
   *
   * @return App\Models\PackPrices
   */
  public static function getServiceByPack($idPack, $idService, $statusList = ['A'])
  {
    if ($idPack && $idService) {
      return self::getConnect('R')
        ->select(
          'islim_pack_prices.id',
          'islim_pack_prices.service_id',
          'islim_pack_prices.total_price',
          'islim_services.title'
        )
        ->join(
          'islim_services',
          'islim_services.id',
          'islim_pack_prices.service_id'
        )
        ->where([
          ['islim_pack_prices.pack_id', $idPack],
          ['islim_pack_prices.service_id', $idService]])
        ->whereIn('islim_services.status', $statusList)
        ->whereIn('islim_pack_prices.status', $statusList)
        ->first();
    }
    return null;
  }

  /**
   * Metodo para obtener info del servicio, financiamiento y precio de un pack dado su id y velocidad
   * @param String $idPack
   * @param String $broads
   *
   * @return App\Models\PackPrices
   */
  public static function getServiceByPackAndBroad($idPack = false, $broads = [], $typeS = false)
  {
    if ($idPack && is_array($broads)) {
      $data = self::getConnect('R')
        ->select(
          'islim_services.id',
          'islim_services.title',
          'islim_services.description',
          'islim_pack_prices.price_pack',
          'islim_pack_prices.price_serv',
          'islim_pack_prices.type',
          'islim_financing.total_amount',
          'islim_financing.amount_financing',
          'islim_financing.SEMANAL',
          'islim_financing.MENSUAL'
        );

      if (!$typeS) {
        $data->join(
          'islim_services',
          function ($join) use ($broads) {
            $join->on(
              'islim_pack_prices.service_id',
              'islim_services.id'
            )
              ->where([
                ['islim_services.status', 'A'],
                ['islim_services.type', 'A']])
              ->whereIn('broadband', $broads);
          }
        );
      }

      if ($typeS == 'MH') {
        $data->join(
          'islim_services',
          function ($join) use ($typeS) {
            $join->on(
              'islim_pack_prices.service_id',
              'islim_services.id'
            )
              ->where([
                ['islim_services.status', 'A'],
                ['islim_services.type', 'A'],
                ['islim_services.service_type', $typeS]]);
          }
        );
      }

      $data->leftJoin(
        'islim_financing',
        function ($join) {
          $join->on(
            'islim_financing.id',
            'islim_pack_prices.id_financing'
          )
            ->where('islim_financing.status', 'A');
        }
      )
        ->where([
          ['islim_pack_prices.status', 'A'],
          ['islim_pack_prices.pack_id', $idPack]]);

      return $data->first();
    }

    return null;
  }

  /**
   * Metodo para obtener info del servicio, financiamiento y precio de un pack dado su id y tipo de pack
   * @param String $idPack
   * @param String $type
   *
   * @return App\Models\PackPrices
   */
  public static function getServiceByPackAndType($idPack = false, $type = 'H')
  {
    if ($idPack) {
      return self::getConnect('R')
        ->select(
          'islim_services.id',
          'islim_services.title',
          'islim_services.description',
          'islim_pack_prices.price_pack',
          'islim_pack_prices.price_serv',
          'islim_pack_prices.type',
          'islim_financing.total_amount',
          'islim_financing.amount_financing',
          'islim_financing.SEMANAL',
          'islim_financing.MENSUAL'
        )
        ->join(
          'islim_services',
          'islim_pack_prices.service_id',
          'islim_services.id'
        )
        ->leftJoin(
          'islim_financing',
          function ($join) {
            $join->on(
              'islim_financing.id',
              'islim_pack_prices.id_financing'
            )
              ->where('islim_financing.status', 'A');
          }
        )
        ->where([
          ['islim_pack_prices.status', 'A'],
          ['islim_pack_prices.pack_id', $idPack],
          ['islim_services.status', 'A'],
          ['islim_services.type', 'A'],
          ['islim_services.service_type', $type]])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para obtener info del servicio, financiamiento, precio y periodicidad de un
   * listado de packs dados sus ids
   * @param String $packsId
   *
   * @return App\Models\PackPrices
   */
  public static function getDataPacksByIds($packsId = [])
  {
    return self::getConnect('R')
      ->select(
        'islim_pack_prices.service_id',
        'islim_pack_prices.price_pack',
        'islim_pack_prices.price_serv',
        'islim_pack_prices.total_price',
        'islim_pack_prices.id_financing',
        'islim_pack_prices.type',
        'islim_services.title',
        'islim_services.description',
        'islim_services.broadband',
        'islim_services.codeAltan',
        'islim_periodicities.periodicity',
        'islim_financing.total_amount'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_pack_prices.service_id'
      )
      ->join(
        'islim_periodicities',
        'islim_periodicities.id',
        'islim_services.periodicity_id'
      )
      ->leftJoin('islim_financing', function ($join) {
        $join->on('islim_financing.id', '=', 'islim_pack_prices.id_financing')
          ->where('islim_financing.status', 'A');
      })
      ->where([
        ['islim_services.type', 'A'],
        ['islim_services.status', 'A'],
        ['islim_pack_prices.status', 'A']])
      ->whereIn('islim_pack_prices.pack_id', $packsId)
      ->get();
  }

  public static function getPackPriceByPackId($id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->select(
        'service_id',
        'id_financing',
        'price_pack',
        'price_serv',
        'total_price'
      )
      ->where('pack_id', $id)
      ->whereIn('status', $statusList)
      ->first();
  }

  public static function getPackPriceDetail($id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->where('id', $id)
      ->whereIn('status', $statusList)
      ->first();
  }

}
