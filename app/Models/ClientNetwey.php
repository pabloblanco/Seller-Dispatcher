<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNetwey extends Model
{
  use HasFactory;

  protected $table = 'islim_client_netweys';

  protected $fillable = [
    'msisdn',
    'clients_dni',
    'service_id',
    'address',
    'type_buy',
    'periodicity',
    'num_dues',
    'paid_fees',
    'unique_transaction',
    'serviceability',
    'lat',
    'lng',
    'point',
    'date_buy',
    'price_remaining',
    'date_reg',
    'date_expire',
    'date_cd30',
    'date_cd90',
    'type_cd90',
    'status',
    'obs',
    'credit',
    'n_update_coord',
    'n_sim_swap',
    'tag',
    'id_list_dns',
    'dn_type',
    'payjoy_id',
    'type_client',
    'id_identity_verification',
    'referred_dn',
    'telmovpay_id',
    //'bundle_id',
    //'parent_bundle',
    'client_netweys_bundle_id'];

  protected $primaryKey = 'msisdn';

  public $incrementing = false;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\ClientNetwey
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ClientNetwey;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para msisdn de un cliente dado su ine y tipo de compra (hbb(H)-mbb(T))
   *
   * @param String $ine
   * @param String $type
   *
   * @return App\Models\ClientNetwey
   */
  public static function getTypeDns($ine = false, $type = false)
  {
    if ($ine && $type) {
      return self::getConnect('R')
        ->select('islim_client_netweys.msisdn')
        ->join(
          'islim_sales',
          'islim_sales.msisdn',
          'islim_client_netweys.msisdn'
        )
        ->where([
          ['islim_client_netweys.clients_dni', $ine],
          ['islim_sales.sale_type', $type],
          ['islim_sales.status', '!=', 'T'],
          ['islim_sales.type', 'P']])
        ->get();
    }

    return false;
  }

  /**
   * Metodo para obtener cliente dado un msisdn
   *
   * @param String $msisdn
   *
   * @return App\Models\ClientNetwey
   */
  public static function getClientByDN($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->select(
          'islim_client_netweys.clients_dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.phone_home',
          'islim_clients.email',
          'islim_clients.code_curp'
        )
        ->join(
          'islim_clients',
          'islim_clients.dni',
          'islim_client_netweys.clients_dni'
        )
        ->where('islim_client_netweys.msisdn', $msisdn)
        ->first();
    }

    return null;
  }

  public static function setInactive($msisdn)
  {
    return self::getConnect('W')
      ->where('msisdn', $msisdn)
      ->update(['status' => 'I']);
  }

  public static function isClient($msisdn)
  {
    $ban = self::getConnect('R')
      ->select('islim_client_netweys.msisdn')
      ->where('islim_client_netweys.msisdn', $msisdn)
      ->first();

    if (!empty($ban)) {
      return true;
    }
    return false;

  }

  public static function isClientByDNI($dni, $type)
  {
    $isCli = self::getConnect('R')
      ->where([
        ['status', '!=', 'T'],
        ['clients_dni', $dni],
        ['dn_type', $type],
      ])
      ->count();

    return ($isCli > 0) ? true : false;
  }

  public static function isClientByTypes($dni, $type)
  {
    $isCli = self::getConnect('R')
      ->where([
        ['status', '!=', 'T'],
        ['clients_dni', $dni],
      ])
      ->whereIn('dn_type', $type)
      ->count();

    return ($isCli > 0) ? true : false;
  }

  public static function setRegisterBundle($msisdn, $client_bundle_id)
  {
    try {
      $save = self::getConnect('W')
        ->where('msisdn', $msisdn)
        ->update(
          ['client_netweys_bundle_id' => $client_bundle_id]);
      return ['success' => true, 'code' => 'OK', 'msg' => ''];
    } catch (Exception $e) {
      $Txmsg = 'Error al actualizar el bundle de instalación (191). ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return ['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg];
    }
  }

/**
 * [getRegisterBundle Se busca el padre del bundle para conocer el registro de cliente bundle]
 * @param  [type] $msisdnMaster [description]
 * @return [type]               [description]
 */
  public static function getRegisterBundle($msisdnMaster)
  {
    $bun = self::getConnect('R')
      ->where('msisdn', $msisdnMaster)
      ->whereNotNull('client_netweys_bundle_id')
      ->first();

    if (!empty($bun)) {
      return $bun->client_netweys_bundle_id;
    }
    return null;
  }
/*DEPRECADO
public static function setMasterBundle($msisdn, $msisdn_master, $bundle_id)
{
try {
$save = self::getConnect('W')
->where('msisdn', $msisdn)
->update(
['parent_bundle' => $msisdn_master,
'bundle_id' => $bundle_id]);
return ['success' => true, 'code' => 'OK', 'msg' => ''];
} catch (Exception $e) {
$Txmsg = 'Error al actualizar el hijo del bundle de instalación (207). ' . (String) json_encode($e->getMessage());
Log::error($Txmsg);
return ['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg];
}
}*/

}
