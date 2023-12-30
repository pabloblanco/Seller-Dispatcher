<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
  use HasFactory;

  protected $table = 'islim_inv_articles';

  protected $fillable = [
    'provider_dni',
    'category_id',
    'title',
    'description',
    'type_barcode',
    'date_reg',
    'status',
    'sku',
    'brand',
    'model',
    'artic_type',
    'price_ref'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Product;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para consultar titulo y descripcion de un producto
   * @param Integer $id
   *
   * @return App\Models\Product
   */
  public static function getProductById($id = false)
  {
    if ($id) {
      return self::getConnect('R')
        ->select('title', 'description', 'price_ref', 'brand', 'model')
        ->where('id', $id)
        ->first();
    }

    return null;
  }

  /**
   * Metodo para consultar  producto
   * @param String $sku
   *
   * @return App\Models\Product
   */
  public static function getProductBySKU($sku = false)
  {
    if ($sku) {
      return self::getConnect('R')
        ->select('id', 'title', 'description', 'price_ref', 'artic_type')
        ->where('sku', $sku)
        ->first();
    }

    return null;
  }

  /**
   * Metodo para consultar los titulos de un grupo de productos dados sus
   * ids, tipo y opcional su categoría
   * @param Array $ids
   * @param String $type
   * @param String $category_id
   *
   * @return App\Models\Product
   */
  public static function getProductsById($ids = [], $type = 'H', $category_id = false, $brand = false)
  {
    if (is_array($ids) && count($ids)) {
      $data = self::getConnect('R')
        ->select('id', 'title', 'model')
        ->where([
          ['status', 'A'],
          ['artic_type', $type]])
        ->whereIn('id', $ids);

      if ($category_id) {
        $data = $data->where('category_id', $category_id);
      }

      if($brand){
        if($brand=='samsung'){  
          $data = $data->where(DB::raw('LOWER(brand)'), $brand);
        }else{
          $data = $data->where(DB::raw('LOWER(brand)'), '<>', 'samsung');
        }
      }
      return $data->get();
    }

    return [];
  }

  /**
   * Metodo para consultar productos que pueden ser ofrecidos en payjoy
   * ids, tipo y opcional su categoría
   *
   * @return App\Models\Product
   */
  public static function getProductsForPayJoy()
  {
    return self::getConnect('R')
      ->select(
        'islim_inv_articles.id',
        'islim_inv_articles.title',
        'islim_inv_articles.description',
        'islim_inv_articles.brand',
        'islim_inv_articles.model',
        'islim_packs.description',
        'islim_pack_prices.price_pack',
        'islim_pack_prices.price_serv'
      )
      ->join(
        'islim_arti_packs',
        'islim_arti_packs.inv_article_id',
        'islim_inv_articles.id'
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_arti_packs.pack_id'
      )
      ->join(
        'islim_pack_prices',
        'islim_pack_prices.pack_id',
        'islim_arti_packs.pack_id'
      )
      ->where([
        ['islim_inv_articles.status', 'A'],
        ['islim_inv_articles.category_id', env('SMARTCATID', 3)],
        ['islim_arti_packs.status', 'A'],
        ['islim_packs.status', 'A'],
        ['islim_packs.is_visible_payjoy', 'Y'],
        ['islim_pack_prices.status', 'A']])
      ->get();
  }
}
