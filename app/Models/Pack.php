<?php

namespace App\Models;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pack extends Model
{
  use HasFactory;

  protected $table = 'islim_packs';

  protected $fillable = [
    'id',
    'title',
    'description',
    'price_arti',
    'date_ini',
    'date_end',
    'date_reg',
    'status',
    'view_web',
    'desc_web',
    'pack_type',
    'sale_type',
    'is_portability',
    'is_band_twenty_eight',
    'service_prom_id',
    'is_visible_payjoy',
    'is_migration',
    'valid_identity',
    'is_visible_paguitos',
    'is_visible_telmovPay',
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Pack
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Pack;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para consultar datos de un pack dado su id
   * @param String $id
   *
   * @return App\Models\Pack
   */
  public static function getActivePackById($id = false, $statusList = ['A'])
  {
    if ($id) {
      return self::getConnect('R')
        ->select(
          'title',
          'description',
          'sale_type',
          'is_migration'
        )
        ->where('id', $id)
        ->whereIn('status', $statusList)
        ->first();
    }
    return null;
  }

  /**
   * Metodo para consultar datos de un grupo de packs dados sus ids
   * y opcional se puede filtrar por packs que no sean de tipo pago en abono
   * y que no sean para portabilidad
   * @param String $id
   *
   * @return App\Models\Pack
   */
  public static function getPacksById($ids = [], $whtInst = false, $isPort = false, $isBandTE = false, $typePack = false, $isMigration = 'N', $esCoorID = false, $typePay = false)
  {
    if (is_array($ids) && count($ids)) {
      $packs = self::getConnect('R')
        ->select(
          'islim_packs.id',
          'islim_packs.title',
          'islim_packs.description',
          'islim_packs.sale_type',
          'islim_packs.date_ini',
          'islim_packs.date_end',
          'islim_packs.is_visible_coppel',
          'islim_packs.is_visible_telmovPay',
          'islim_packs.valid_identity',
          DB::raw('(select count(pe.id) from islim_pack_esquema as pe where pe.id_pack = islim_packs.id and pe.status = "A") as countEsq')
        )
        ->where([
          ['islim_packs.status', 'A'],
          ['islim_packs.is_migration', $isMigration],
        ])
        ->whereIn('islim_packs.id', $ids);

      if ($whtInst) {
        $packs->where('islim_packs.sale_type', '!=', 'Q');
      }

      if (!$isPort) {
        $packs->where('islim_packs.is_portability', 'N');
      }

      if ($isBandTE) {
        $packs->where('islim_packs.is_band_twenty_eight', $isBandTE);
      }

      if ($typePack) {
        $packs->where('islim_packs.pack_type', $typePack);
      }

      if ($typePay) {
        if ($typePay == 'payjoy') {
          $packs->where('islim_packs.is_visible_payjoy', 'Y');
        }
        if ($typePay == 'paguitos') {
          $packs->where('islim_packs.is_visible_paguitos', 'Y');
        }
        if ($typePay == 'coppel') {
          $packs->where('islim_packs.is_visible_coppel', 'Y');
        }
        if ($typePay == 'contado') {
          $packs->where('islim_packs.is_visible_coppel', 'N');
        }
        if ($typePay == 'telmovpay') {
          $packs->where('islim_packs.is_visible_telmovPay', 'Y');
        }
      }

      if (!$esCoorID) {
        $packs->where(DB::raw('(select count(pe.id) from islim_pack_esquema as pe where pe.id_pack = islim_packs.id and pe.status = "A")'), 0);
        $packs = $packs->get();
      } else {
        $packs->leftJoin('islim_pack_esquema', function ($join) use ($esCoorID) {
          $join->on('islim_pack_esquema.id_pack', 'islim_packs.id')
            ->where([
              ['islim_pack_esquema.status', 'A'],
              ['islim_pack_esquema.id_esquema', $esCoorID],
            ]);
        });

        $packs->where(function ($query) use ($esCoorID) {
          $query->where(DB::raw('(select count(pe.id) from islim_pack_esquema as pe where pe.id_pack = islim_packs.id and pe.status = "A")'), 0)
            ->orWhere('islim_pack_esquema.id_esquema', $esCoorID);
        });

        $packs = $packs->get();

        $packsFilter = $packs->filter(function ($val) {
          return $val->countEsq > 0;
        });

        if (count($packsFilter)) {
          $packs = $packsFilter;
        }
      }

      return $packs;
    }

    return [];
  }

  /**
   * Metodo para consultar datos de un pack dado su id y tipo de pack
   * @param String $id
   *
   * @return App\Models\Pack
   */
  public static function getInfoPack($idPack = false, $typePack = false)
  {
    if ($idPack && $typePack) {
      return self::getConnect('R')
        ->select(
          'islim_packs.id',
          'islim_packs.sale_type',
          'islim_packs.service_prom_id',
          'islim_packs.title',
          'islim_packs.description',
          'islim_packs.valid_identity',
          'islim_packs.is_visible_paguitos',
          'islim_packs.is_visible_telmovPay',
          'islim_pack_prices.price_pack',
          'islim_pack_prices.price_serv',
          'islim_pack_prices.id_financing',
          'islim_financing.total_amount'
        )
        ->join(
          'islim_pack_prices',
          'islim_packs.id',
          'islim_pack_prices.pack_id'
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
          ['islim_packs.id', $idPack],
          ['islim_packs.status', 'A'],
          ['islim_pack_prices.status', 'A'],
          ['islim_pack_prices.type', $typePack],
        ])
        ->first();

    }
  }

/**
 * [getFiberPacks Retorna la lista de planes de fibra disponibles para la olt en cuestion]
 * @param  [type]  $olt         [description]
 * @param  boolean $isMigration [description]
 * @param  boolean $isForcer    [Y=plan forzado; N=>Plan sin contrato]
 * @param  boolean $isSuscrip   [Y=plan subscrito; N=>sin subscripcion]
 * @param  boolean $isBundle    [Y=Es un bundle; N=>Paquete individual]
 * @param  boolean $bundle_id   [Null si es individual, Id del bundle si el campo isBundle es Y]
 * @return [type]               [description]
 */
  public static function getFiberPacks($olt, $isMigration = false, $isForcer = false, $isSuscrip = false, $isBundle = false, $bundle_id = null)
  {
    $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';
    $data = self::getConnect('R');

    if ($isBundle == 'Y') {
      $data = $data->select(
        'islim_bundle.id',
        'islim_bundle.title',
        'islim_bundle.description',
        'islim_packs.date_ini',
        'islim_packs.date_end',
        'islim_packs.is_migration',
        'islim_pack_prices.total_price',
        'islim_services.is_payment_forcer',
        'islim_services.for_subscription',
        'islim_services.is_bundle'
      );
    } else {
      $data = $data->select(
        'islim_packs.id',
        'islim_packs.title',
        'islim_packs.description',
        'islim_packs.date_ini',
        'islim_packs.date_end',
        'islim_packs.is_migration',
        'islim_pack_prices.total_price',
        'islim_services.is_payment_forcer',
        'islim_services.for_subscription',
        'islim_services.is_bundle'
      );
    }

    $data = $data->join(
      'islim_pack_prices',
      'islim_pack_prices.pack_id',
      'islim_packs.id'
    )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_pack_prices.service_id'
      )
      ->join(
        'islim_arti_packs',
        'islim_arti_packs.pack_id',
        'islim_packs.id'
      )
      ->join('islim_fiber_article_zone', function ($join) {
        $join->on('islim_fiber_article_zone.article_id', '=', 'islim_arti_packs.inv_article_id')
          ->where('islim_fiber_article_zone.status', 'A');
      })
      ->join('islim_fiber_service_zone', function ($join) {
        $join->on('islim_fiber_service_zone.service_id', '=', 'islim_pack_prices.service_id')
          ->where('islim_fiber_service_zone.status', 'A');
      })
      ->join('islim_fiber_zone', function ($join) use ($ambiente, $olt) {
        $join->on('islim_fiber_zone.id', '=', 'islim_fiber_article_zone.fiber_zone_id')
          ->on('islim_fiber_zone.id', '=', 'islim_fiber_service_zone.fiber_zone_id')
          ->where('islim_fiber_zone.status', 'A')
          ->where('islim_fiber_zone.ambiente', $ambiente)
          ->where('islim_fiber_zone.id', $olt);
      });

    if ($isBundle == 'Y') {
      $data = $data->join(
        'islim_pack_bundle',
        'islim_pack_bundle.packs_id',
        'islim_packs.id',
      )
        ->join(
          'islim_bundle',
          'islim_bundle.id',
          'islim_pack_bundle.bundle_id',
        )
        ->where([
          ['islim_pack_bundle.status', 'A'],
          ['islim_bundle.status', 'A']]);

      if (!is_null($bundle_id)) {
        //Log::info('Buscamos un bundle con los mismos productos con subscripcion');
        $dataBun = Bundle::getComponentBundle($bundle_id);
        if (empty($dataBun)) {
          return null;
        }
        // Log::info('Bundle actual ' . (String) json_encode($dataBun));
        $data = $data->where([
          ['islim_bundle.total_up_F', $dataBun->total_up_F],
          ['islim_bundle.total_up_T', $dataBun->total_up_T],
          ['islim_bundle.total_up_M', $dataBun->total_up_M],
          ['islim_bundle.total_up_MH', $dataBun->total_up_MH],
          ['islim_bundle.total_up_H', $dataBun->total_up_H],
          ['islim_bundle.containt_F', $dataBun->containt_F],
          ['islim_bundle.containt_T', $dataBun->containt_T],
          ['islim_bundle.containt_M', $dataBun->containt_M],
          ['islim_bundle.containt_MH', $dataBun->containt_MH],
          ['islim_bundle.containt_H', $dataBun->containt_H]]);
      }
    }
    $data = $data->where([
      ['islim_packs.pack_type', 'F'],
      ['islim_services.service_type', 'F'],
      ['islim_packs.status', 'A'],
      ['islim_services.status', 'A'],
      ['islim_pack_prices.status', 'A'],
      ['islim_arti_packs.status', 'A']]);

    if ($isMigration) {
      $data = $data->where('islim_packs.is_migration', 'Y');
    } else {
      $data = $data->where('islim_packs.is_migration', 'N');
    }

    if ($isForcer) {
      $data = $data->where('islim_services.is_payment_forcer', $isForcer);
    }
    if ($isSuscrip) {
      $data = $data->where('islim_services.for_subscription', $isSuscrip);
    }
    if ($isBundle) {
      $data = $data->where('islim_services.is_bundle', $isBundle);
    }
    /*$query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());

    Log::info($query);*/

    return $data->get();
  }

  public static function getPlanDetailFiber($id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->select(
        'islim_packs.id',
        'islim_packs.title',
        'islim_packs.description',
        'islim_packs.pack_type',
        'islim_services.title as service_title',
        'islim_services.description as service_description',
        'islim_inv_articles.title as product_title',
        'islim_pack_prices.total_price'
      )
      ->join(
        'islim_pack_prices',
        'islim_pack_prices.pack_id',
        'islim_packs.id'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_pack_prices.service_id'
      )
      ->join(
        'islim_arti_packs',
        'islim_arti_packs.pack_id',
        'islim_packs.id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_arti_packs.inv_article_id'
      )
      ->where('islim_packs.id', $id)
      ->whereIn('islim_pack_prices.status', $statusList)
      ->whereIn('islim_arti_packs.status', $statusList)
      ->first();
  }
}
