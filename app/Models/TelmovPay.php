<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelmovPay extends Model
{
  use HasFactory;
  protected $table = 'islim_telmovpay';

  protected $fillable = [
    'id',
    'dni',
    'msisdn',
    'initial_amount',
    'cant_cuotes',
    'total_amount',
    'sale_id',
    'seller_mail',
    'status',
    'date_reg',
    'date_process',
    'verification_id',
    'salesclerk_id',
    'status_verify',
    'agreement_id',
    'customer_id',
    'store_id',
    'url_verify',
    'smartPhone_id',
    'url_contract',
    'pack_id',
    'isPort',
    'minimum_amount',
    'weekly',
    'lockProvider',
    'lockReference',
    'loan_id',
    'status_enrole',
    'enrollment_data',
    'sincrone_data'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\TelmovPay
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function inProcess($seller_mail = false, $status = false, $dni = false)
  {
    if ($seller_mail) {

      $reg = self::getConnect('R')
        ->where('seller_mail', $seller_mail);

      if ($status) {
        $reg = $reg->whereIn('status', $status);
      } else {
        $status = ['CF', 'A', 'T'];
        $reg = $reg->whereNotIn('status', $status);
      }

      if ($dni) {
        $reg = $reg->where('dni', $dni);
      }
      return $reg->first();
    }
    return null;
  }
/**
 * [insertData Insercion inicial de datos ante una peticion de financiacion con telmovPay]
 * @param  boolean $seller_mail [description]
 * @return [type]               [description]
 */
  public static function firstInsertTelmov($data)
  {
    return self::getConnect('W')->insertGetId($data);
  }

  public static function getInfoTelmov($msisdn = false, $status = false)
  {
    if ($msisdn) {
      $datos = self::getConnect('R')
        ->where([
          ['msisdn', $msisdn],
          ['seller_mail', session('user')]]);

      if ($status) {
        $datos = $datos->whereIn('status', $status);
      }
      return $datos->first();
    }
    return null;
  }

  /**
   * [updateStatus Cambia el status de un registro de telmov]
   * @param  [type]  $Newstatus [description]
   * @param  [type]  $dni       [description]
   * @param  boolean $exeption  [description]
   * @param  boolean $id        [description]
   * @param  boolean $msisdn    [description]
   * @return [type]             [description]
   */
  public static function updateStatus($Newstatus, $dni = false, $exeption = false, $id = false, $msisdn = false)
  {

    $info = self::getConnect('W')
      ->where('seller_mail', session('user'));

    if ($dni) {
      $info = $info->where('dni', $dni);
    }
    if ($exeption) {
      $info = $info->whereNotIn('status', ['CF', 'A', 'T']);
    }
    if ($id) {
      $info = $info->where('id', $id);
    }
    if ($msisdn) {
      $info = $info->where('msisdn', $msisdn);
    }

    $info = $info->update([
      'status' => $Newstatus]);
    return $info;
  }

}
