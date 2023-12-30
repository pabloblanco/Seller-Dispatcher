<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
  use HasFactory;
  protected $table = 'islim_bundle';

  protected $fillable = [
    'id',
    'title',
    'description',
    'is_up_subscription',
    'is_up_payment_force',
    'date_reg',
    'date_mod',
    'status',
    'containt_H',
    'total_up_H',
    'containt_M',
    'total_up_M',
    'containt_MH',
    'total_up_MH',
    'containt_T',
    'total_up_T',
    'containt_F',
    'total_up_F',
    'recharge_susbcription'];

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
 * [infoDetailBundleAlta Consulta base que comparte cada producto que conforma un bundle]
 * @return [type] [description]
 */
  private static function infoDetailBundleAlta($Id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->select(
        'islim_packs.id',
        'islim_packs.title',
        'islim_packs.description',
        'islim_packs.pack_type',
        'islim_services.id as service_id',
        'islim_services.title as service_title',
        'islim_services.description as service_description',
        'islim_services.method_pay as service_pay',
        'islim_inv_articles.id as product_id',
        'islim_inv_articles.title as product_title',
        'islim_inv_categories.id as category_id',
        'islim_inv_categories.title as category_title',
        'islim_pack_prices.total_price'
      )
      ->join(
        'islim_pack_bundle',
        'islim_pack_bundle.bundle_id',
        'islim_bundle.id',
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_pack_bundle.packs_id'
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
      ->join(
        'islim_inv_categories',
        'islim_inv_categories.id',
        'islim_inv_articles.category_id'
      )
      ->where('islim_pack_bundle.bundle_id', $Id)
      ->whereIn('islim_bundle.status', $statusList)
      ->whereIn('islim_pack_bundle.status', $statusList)
      ->whereIn('islim_packs.status', $statusList)
      ->whereIn('islim_pack_prices.status', $statusList)
      ->whereIn('islim_arti_packs.status', $statusList);
  }

  public static function getComponentBundle($Id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->select('id', 'title', 'description',
        'containt_H', 'total_up_H',
        'containt_M', 'total_up_M',
        'containt_MH', 'total_up_MH',
        'containt_T', 'total_up_T',
        'containt_F', 'total_up_F',
        'recharge_susbcription')
      ->where('id', $Id)
      ->whereIn('status', $statusList)
      ->first();
  }

/**
 * [getDetailBundleAlta Se obtiene el detalle de cada tipo de producto que conforma un bundle de alta]
 * @param  boolean $Id   [id del bundle]
 * @return [type]        [description]
 */
  public static function getDetailBundleAlta($Id, $statusList = ['A'])
  {
    $init = self::getComponentBundle($Id, $statusList);
    if (!empty($init)) {

      $costo = 0;
      if ($init->containt_H == 'Y') {
        //Posee hbb, se verifica
        $dataH = self::infoDetailBundleAlta($Id, $statusList);
        $dataH = $dataH->where('islim_packs.pack_type', 'H')
          ->first();

        if (!empty($dataH)) {
          $infoH = $dataH;
          $subtotal = $infoH->total_price * $init->total_up_H;
          $costo += $subtotal;
        } else {
          return array('success' => false, 'data' => "Falta el paquete de alta de HBB del bundle: '" . $init->title . " (" . $Id . ")'", 'code' => "EMP_PAK");
        }
      } else {
        $infoH = null;
      }

      if ($init->containt_M == 'Y') {
        //Posee Mifi, se verifica
        $dataM = self::infoDetailBundleAlta($Id, $statusList);
        $dataM = $dataM->where('islim_packs.pack_type', 'M')
          ->first();
        if (!empty($dataM)) {
          $infoM = $dataM;
          $subtotal = $infoM->total_price * $init->total_up_MH;
          $costo += $subtotal;
        } else {
          return array('success' => false, 'data' => "Falta el paquete de alta de Mifi del bundle: '" . $init->title . " (" . $Id . ")'", 'code' => "EMP_PAK");
        }
      } else {
        $infoM = null;
      }

      if ($init->containt_MH == 'Y') {
        //Posee Mifi huella, se verifica
        $dataMH = self::infoDetailBundleAlta($Id, $statusList);
        $dataMH = $dataMH->where('islim_packs.pack_type', 'MH')
          ->first();
        if (!empty($dataMH)) {
          $infoMH = $dataMH;
          $subtotal = $infoMH->total_price * $init->total_up_MH;
          $costo += $subtotal;
        } else {
          return array('success' => false, 'data' => "Falta el paquete de alta de Mifi Huella del bundle: '" . $init->title . " (" . $Id . ")'", 'code' => "EMP_PAK");
        }
      } else {
        $infoMH = null;
      }

      if ($init->containt_T == 'Y') {
        //Posee Telefono, se verifica
        $dataT = self::infoDetailBundleAlta($Id, $statusList);
        $dataT = $dataT->where('islim_packs.pack_type', 'T');

        /*
        $query = vsprintf(str_replace('?', '%s', $dataT->toSql()), collect($dataT->getBindings())->map(function ($binding) {
        return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());

        Log::info($query);*/

        $dataT = $dataT->first();

        if (!empty($dataT)) {
          $infoT = $dataT;
          $subtotal = $infoT->total_price * $init->total_up_T;
          $costo += $subtotal;
        } else {
          return array('success' => false, 'data' => "Falta el paquete de alta de Telefonia del bundle: '" . $init->title . " (" . $Id . ")'", 'code' => "EMP_PAK");
        }
      } else {
        $infoT = null;
      }

      if ($init->containt_F == 'Y') {
        //Posee Fibra, se verifica
        $dataF = self::infoDetailBundleAlta($Id, $statusList);
        $dataF = $dataF->where('islim_packs.pack_type', 'F');

        /*Log::info("********************");
        $query = vsprintf(str_replace('?', '%s', $dataF->toSql()), collect($dataF->getBindings())->map(function ($binding) {
        return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());

        Log::info($query);*/

        $dataF = $dataF->first();

        if (!empty($dataF)) {
          $infoF = $dataF;
          $subtotal = $infoF->total_price * $init->total_up_F;
          $costo += $subtotal;
        } else {
          return array('success' => false, 'data' => "Falta el paquete de alta de Fibra del bundle: '" . $init->title . " (" . $Id . ")'", 'code' => "EMP_PAK");
        }
      } else {
        $infoF = null;
      }

      $init->total_payment = $costo;
      $info = array("general" => $init, 'info_H' => $infoH, 'info_M' => $infoM, 'info_MH' => $infoMH, 'info_T' => $infoT, 'info_F' => $infoF);

      return array('success' => true, 'data' => $info, 'code' => "OK");
    }
    return array('success' => false, 'data' => "No se encuentra bundle " . $Id . " de la cita (MB241) ", 'code' => "EMP_BUN");
  }

  /**
   * [getBundleByPack Obtiene el bundle al cual pertenece un pack]
   * @param  [type] $pack_id [description]
   * @return [type]          [description]
   */
  public static function getBundleByPack($pack_id, $statusList = ['A'])
  {
    return self::getConnect('R')
      ->select('islim_pack_bundle.bundle_id')
      ->join('islim_pack_bundle',
        'islim_pack_bundle.bundle_id',
        'islim_bundle.id')
      ->join('islim_packs',
        'islim_packs.id',
        'islim_pack_bundle.packs_id')
      ->where('islim_pack_bundle.packs_id', $pack_id)
      ->whereIn('islim_pack_bundle.status', $statusList)
      ->whereIn('islim_bundle.status', $statusList)
      ->whereIn('islim_packs.status', $statusList)
      ->first();
  }

/**
 * [getPriceBundle Obtiene el costo total de un bundle]
 * @param  [type] $bundle [description]
 * @return [type]         [description]
 */
  public static function getPriceBundleByObj($InfoBundle)
  {
    $response = ['success' => false, 'msg' => "Faltan precios para retorna el precio total del bundle", 'code' => "FAIL", 'price' => null];

    $price = 0;
    if ($InfoBundle->general->containt_H == 'Y') {
      if (isset($InfoBundle->info_H->total_price) &&
        $InfoBundle->general->total_up_H > 0) {
        $price += $InfoBundle->info_H->total_price * $InfoBundle->general->total_up_H;
      } else {
        return ['success' => false, 'msg' => "Fallo el calculo del precio de 'Hogar' para retorna el precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "FAIL", 'price' => null];
      }
    }
    if ($InfoBundle->general->containt_M == 'Y') {
      if (isset($InfoBundle->info_M->total_price) &&
        $InfoBundle->general->total_up_M > 0) {
        $price += $InfoBundle->info_M->total_price * $InfoBundle->general->total_up_M;
      } else {
        return ['success' => false, 'msg' => "Fallo el calculo del precio de 'Mifi' para retorna el precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "FAIL", 'price' => null];
      }
    }
    if ($InfoBundle->general->containt_MH == 'Y') {
      if (isset($InfoBundle->info_MH->total_price) &&
        $InfoBundle->general->total_up_MH > 0) {
        $price += $InfoBundle->info_MH->total_price * $InfoBundle->general->total_up_MH;
      } else {
        return ['success' => false, 'msg' => "Fallo el calculo del precio de 'Mifi Huella' para retorna el precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "FAIL", 'price' => null];
      }
    }
    if ($InfoBundle->general->containt_T == 'Y') {
      if (isset($InfoBundle->info_T->total_price) &&
        $InfoBundle->general->total_up_T > 0) {
        $price += $InfoBundle->info_T->total_price * $InfoBundle->general->total_up_T;
      } else {
        return ['success' => false, 'msg' => "Fallo el calculo del precio de 'Telefonia' para retorna el precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "FAIL", 'price' => null];
      }
    }
    if ($InfoBundle->general->containt_F == 'Y') {
      if (isset($InfoBundle->info_F->total_price) &&
        $InfoBundle->general->total_up_F > 0) {
        $price += $InfoBundle->info_F->total_price * $InfoBundle->general->total_up_F;
      } else {
        return ['success' => false, 'msg' => "Fallo el calculo del precio de 'Fibra' para retorna el precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "FAIL", 'price' => null];
      }
    }
    return ['success' => true, 'msg' => "Precio total del bundle '" . $InfoBundle->general->title . "'", 'code' => "OK", 'price' => $price];
  }
}
