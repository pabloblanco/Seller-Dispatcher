<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Inventory extends Model
{
  use HasFactory;

  protected $table = 'islim_inv_arti_details';

  protected $fillable = [
    'id',
    'parent_id',
    'inv_article_id',
    'warehouses_id',
    'serial',
    'msisdn',
    'iccid',
    'imei',
    'imsi',
    'date_reception',
    'date_sending',
    'price_pay',
    'obs',
    'date_reg',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Inventory
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Inventory;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener datos de detalle de inventario dado un tipo de artículo(H ó T) y una bodega
   *
   * @param String $wh
   * @param String $type
   *
   * @return App\Models\Inventory
   */
  public static function getArticsByWh($wh = false, $type = 'H')
  {
    if ($wh) {
      $data = self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_articles.artic_type', $type]])
        ->whereIn('warehouses_id', $wh);

      $data = $data->get();

      return $data;
    }

    return [];
  }

  /**
   * Metodo para obtener el id del articulo de los detalles de inventario asociados a
   * un grupo de ids de detalle de articulos y un tipo de artículo(H ó T) y una bodega
   *
   * @param Array $ids
   * @param String $type
   *
   * @return App\Models\Inventory
   */
  public static function getArticsByIds($ids = [], $type = 'H')
  {
    if (is_array($ids) && count($ids)) {
      $data = self::getConnect('R')
        ->select('islim_inv_arti_details.inv_article_id')
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_articles.artic_type', $type]])
        ->whereIn('islim_inv_arti_details.id', $ids);

      $data = $data->get();

      return $data;
    }

    return [];
  }

  /**
   * Metodo para obtener datos de los detalles de articulo dado sus msisdns
   * y asociados a un vendedor
   *
   * @param Array $dns
   * @param String $owner
   *
   * @return App\Models\Inventory
   */
  public static function getArticsByDns($dns = [], $owner)
  {
    if (is_array($dns) && count($dns)) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.title',
          'islim_inv_articles.artic_type',
          'islim_inv_arti_details.imei',
          'islim_inv_arti_details.serial',
          'islim_inv_arti_details.iccid'
        )
        ->join(
          'islim_inv_assignments',
          'islim_inv_assignments.inv_arti_details_id',
          'islim_inv_arti_details.id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_assignments.users_email', $owner]])
        ->whereIn('islim_inv_arti_details.msisdn', $dns)
        ->get();
    }

    return [];
  }

  /**
   * Metodo para obtener datos de un detalle de articulo dado su msisdn
   * y asociado a un pack
   *
   * @param String $pack
   * @param String $msisdn
   *
   * @return App\Models\Inventory
   */
  public static function getArticByIdAndDN($pack = false, $msisdn = false)
  {
    if ($pack && $msisdn) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.msisdn',
          'islim_inv_arti_details.inv_article_id'
        )
        ->join(
          'islim_arti_packs',
          'islim_arti_packs.inv_article_id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_arti_details.msisdn', $msisdn],
          ['islim_inv_arti_details.status', 'A'],
          ['islim_arti_packs.pack_id', $pack]])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para marcar como vendido un detalle de articulo
   *
   * @param String $id
   *
   * @return App\Models\Inventory
   */
  public static function markArticleSale($id = false, $newStatus = 'V', $obs = false)
  {
    if ($id) {
      try {
        self::getConnect('W')
          ->where('id', $id)
          ->update([
            'status' => $newStatus,
            'obs' => ($obs) ? $obs : null]);
        return array('success' => true, 'msg' => 'OK');

      } catch (Exception $e) {
        $txMsg = 'No se pudo marcar el articulo como vendido. ' . (String) json_encode($e->getMessage());
        Log::error($txMsg);
        return array('success' => false, 'msg' => $txMsg);
      }
    }
    return array('success' => false, 'msg' => 'Se requiere el id para continuar');
  }

  /**
   * Metodo para obtener datos de un detalle de articulo dado su dn y la bodega donde
   * se encuentra
   *
   * @param String $msisdn
   * @param String $wh
   *
   * @return App\Models\Inventory
   */
  public static function getArticByDnAndWh($msisdn = false, $wh = false)
  {
    if ($msisdn && $wh) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.inv_article_id',
          'islim_inv_arti_details.msisdn',
          'islim_inv_arti_details.serial',
          'islim_inv_arti_details.iccid',
          'islim_inv_arti_details.imei',
          'islim_inv_articles.title',
          'islim_inv_articles.description',
          'islim_inv_articles.artic_type'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_arti_details.msisdn', $msisdn],
          ['islim_inv_articles.status', 'A']])
        ->whereIn('islim_inv_arti_details.warehouses_id', $wh)
        ->first();
    }

    return null;
  }

  /**
   * Metodo para obtener datos de un detalle de articulo dado su dn y como opcional
   * se puede filtar por si esta asigna a un usuario especifico
   *
   * @param String $dn
   * @param Array $filters
   *
   * @return App\Models\Inventory
   */
  public static function getDetail($dn, $filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_inv_arti_details.id',
        'islim_inv_articles.artic_type',
        'islim_inv_arti_details.iccid',
        'islim_inv_arti_details.price_pay'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_inv_assignments',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.id'
      )
      ->where([
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_arti_details.msisdn', $dn]]);

    if (!empty($filters['activeAsigne'])) {
      $data->where([
        ['islim_inv_assignments.status', 'A'],
        ['islim_inv_assignments.users_email', $filters['activeAsigne']]]);
    }

    return $data->first();
  }

  /**
   * Metodo para consultar si un articulo tiene inventario disponible
   *
   * @param String $artic
   *
   * @return App\Models\Inventory
   */
  public static function hasInventory($artic)
  {
    return self::getConnect('R')
      ->select('msisdn')
      ->where([
        ['status', 'A'],
        ['inv_article_id', $artic]]);
  }

  /**
   * Metodo para consultar si un msisdn existe
   *
   * @param String $msisdn
   *
   * @return App\Models\Inventory
   */
  public static function getDataDn($msisdn)
  {
    return self::getConnect('R')
      ->select('id', 'msisdn', 'status', 'inv_article_id', 'imei')
      ->where('msisdn', $msisdn)
      ->first();
  }

  /**
   * Metodo para consultar msisdn dado un iccid
   *
   * @param String $iccid
   *
   * @return App\Models\Inventory
   */
  public static function getDnByiccid($iccid = false)
  {
    if ($iccid) {
      return self::getConnect('R')
        ->select('msisdn')
        ->where('iccid', $iccid)
        ->first();
    }

    return null;
  }

  /**
   * Metodo para consultar msisdns que se encuentran en una bodega
   *
   * @param String $warehouses
   *
   * @return App\Models\Inventory
   */
  public static function getDnsByWareHouse($warehouses = [])
  {
    return self::getConnect('R')
      ->select('msisdn')
      ->whereIn('warehouses_id', $warehouses)
      ->get();
  }

  public static function getDNsByArticAndAssign($artic, $zone, $user)
  {
    return self::getConnect('R')
      ->select(
        'islim_inv_arti_details.msisdn',
        'islim_inv_arti_details.serial',
        'islim_inv_arti_details.imei'
      )
      ->join(
        'islim_inv_assignments',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.id'
      )
      ->join('islim_inv_articles', function ($join) {
        $join->on('islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->where('islim_inv_articles.status', 'A');
      })
      ->join('islim_fiber_article_zone', function ($join) use ($zone) {
        $join->on('islim_fiber_article_zone.article_id', 'islim_inv_articles.id')
          ->where('islim_fiber_article_zone.fiber_zone_id', $zone)
          ->where('islim_fiber_article_zone.status', 'A');
      })
      ->where([
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_arti_details.inv_article_id', $artic],
        ['islim_inv_assignments.status', 'A'],
        ['islim_inv_assignments.users_email', $user]]
      )
      ->get();
  }

  /**
   * [getDnsById Obtengo el DN del detalle de inventario basado el id]
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public static function getDnsById($id)
  {
    return self::getConnect('R')
      ->select(
        'islim_inv_arti_details.id',
        'islim_inv_arti_details.msisdn',
        'islim_inv_arti_details.inv_article_id',
        'islim_inv_arti_details.imei',
        'islim_inv_articles.sku',
        'islim_inv_articles.model',
        'islim_inv_articles.brand',
        'islim_inv_articles.title',
        'islim_inv_articles.artic_type'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->where('islim_inv_arti_details.id', $id)
      ->first();
  }
/**
 * [getTypeArticle Regresa la informacion de que tipo de equipos se trata el identificador de inventario consultado]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
  public static function getTypeArticle($id)
  {
    return self::getConnect('R')
      ->select('islim_inv_categories.id AS Category_id')
      ->join('islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id')
      ->join('islim_inv_categories',
        'islim_inv_categories.id',
        'islim_inv_articles.category_id')
      ->where('islim_inv_arti_details.id', $id)
      ->first();
  }
  /**
   * [getArticleExist Revisa si la mac del producto de fibra existe en BD o no]
   * @param  [type] $mac [description]
   * @return [type]      [description]
   */
  public static function getArticleExist($mac)
  {
    $mac = strtoupper($mac);
    $inv = self::getConnect('R')
      ->select('islim_inv_articles.id AS idArt',
        'islim_inv_arti_details.id',
        'islim_inv_arti_details.warehouses_id',
        'islim_inv_arti_details.status',
        'islim_inv_arti_details.serial',
        'islim_inv_arti_details.msisdn')
      ->join('islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id')
      ->where([
        ['islim_inv_arti_details.imei', $mac],
        ['islim_inv_articles.artic_type', 'F']])
      ->first();

    if (!empty($inv)) {
      $text = 'La dirección Mac se encuentra registrada en netwey';
      $success = false;
      if ($inv->status == 'V') {
        $text .= ' como vendido';
      } elseif ($inv->status == 'A') {
        $assigne = SellerInventory::getAssigneArt($inv->id);
        $textAux = '';
        if (!empty($assigne)) {
          if ($assigne->users_email != session('user')) {
            $textAux = ", esta asignado al instalador " . $assigne->users_email . ' se debe contactar a netwey si desea que sea reasignado el articulo';
          } else {
            $success = true;
          }
        }
        $text .= $textAux . ', por favor verifica la MAC';
      }
      return array('success' => $success, 'code' => $inv->status, 'msg' => $text, 'infoArt' => $inv);
    }
    return array('success' => true, 'code' => 'EMPTY', 'msg' => 'mac ' . $mac . ' libre de usarse');
  }

/**
 * [getSerialArt Verifica si el serial ingresado para el equipo de fibra existe o no en netwey]
 * @param  [type] $serial [description]
 * @return [type]         [description]
 */
  public static function getSerialArt($serial)
  {
    $inv = self::getConnect('R')
      ->select('islim_inv_arti_details.id AS idArt',
        'islim_inv_arti_details.status')
      ->where('islim_inv_arti_details.serial', $serial)
      ->first();

    if (!empty($inv)) {
      return array('success' => false, 'msg' => 'Serial ya utilizado ante netwey, por favor verificar');
    }
    return array('success' => true, 'msg' => 'Serial ' . $serial . ' disponible');
  }

/**
 * [existDN retorna si el DN existe o no en inventario]
 * @param  boolean $msisdn [description]
 * @return [type]          [description]
 */
  public static function existDN($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->where([
          ['msisdn', $msisdn]])
        ->first();
    }
    return null;
  }

  /**
   * [getAvailableDnAutogen retorna el primer dn de fibra disponible para ser usado]
   * @return [type] [description]
   */
  public static function getAvailableDnAutogen()
  {
    $dn = self::getConnect('R')
      ->where([
        ['msisdn', ">=", env('MIN_FIBER_DN', '1000000001')],
        ['msisdn', "<=", env('MAX_FIBER_DN', '1999999999')],
        ['dn_autogen', 'Y'],
      ])
      ->whereRaw('msisdn REGEXP "^[0-9]+$" = 1')
      ->whereRaw('LENGTH(msisdn) = 10')
      ->max('msisdn');

    if (!empty($dn)) {
      $dn = (String) ($dn + 1);
    } else {
      $dn = env('MIN_FIBER_DN', '1000000001');
    }
    while (
      self::existDN($dn) != null
      && $dn >= env('MIN_FIBER_DN', '1000000001')
      && $dn <= env('MAX_FIBER_DN', '1999999999')
    ) {
      $dn = (String) ($dn + 1);
    }
    if ($dn >= env('MIN_FIBER_DN', '1000000001')
      && $dn <= env('MAX_FIBER_DN', '1999999999')) {
      return $dn;
    } else {
      return null;
    }
  }

  public static function create_ArtFiber($msisdn, $datosArt)
  {
    if (isset($datosArt->mac) && !empty($datosArt->mac)
      && isset($datosArt->serial) && !empty($datosArt->serial)
      && isset($datosArt->idArtInstall) && !empty($datosArt->idArtInstall)) {

      //Verifico el precio que tiene al articulo
      $prices = Product::getProductById($datosArt->idArtInstall);

      try {

        $newArticle = self::getConnect('W');
        $newArticle->inv_article_id = $datosArt->idArtInstall;
        $newArticle->warehouses_id = env('WHEREHOUSE', '5');
        $newArticle->serial = strtoupper(trim($datosArt->serial));
        $newArticle->msisdn = trim($msisdn);
        $newArticle->imei = strtoupper(trim($datosArt->mac));
        $newArticle->price_pay = !empty($prices) ? $prices->price_ref : '699';
        $newArticle->date_reg = date('Y-m-d H:i:s');
        $newArticle->obs = 'Generado por instaladores Velocom';
        $newArticle->dn_autogen = 'Y';
        $newArticle->save();
        return array('success' => true, 'newArticle' => $newArticle);

      } catch (Exception $e) {
        $txmsg = "No se pudo crear el articulo en inventario " . (String) json_encode($e->getMessage());
        Log::error($txmsg);
        return array('success' => false, 'msg' => $txmsg);
      }

    } else {
      return array('success' => false, 'msg' => 'No se pudo crear el articulo en inventario hacen falta datos');
    }
  }

  public static function getArticleAssigne($msisdn, $type, $cant)
  {
    return self::getConnect('R')
      ->select('islim_inv_arti_details.id',
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title AS product_name',
        'islim_inv_articles.artic_type')
      ->join('islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id')
      ->join('islim_inv_assignments',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.id')
      ->where([
        ['islim_inv_arti_details.msisdn', 'like', '%' . $msisdn . '%'],
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_articles.artic_type', $type],
        ['islim_inv_assignments.users_email', session('user')],
        ['islim_inv_assignments.status', 'A']])
      ->limit($cant)
      ->get();
  }

}
