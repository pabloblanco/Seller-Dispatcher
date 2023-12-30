<?php

namespace App\Models;

use App\Models\PackPrices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History_payment_seller extends Model
{
  use HasFactory;
  protected $table = 'islim_history_payment_seller';

  protected $fillable = [
    'id',
    'email_client',
    'subscription_id',
    'external_reference',
    'url_payment',
    'date_changer_webhook',
    'status_payment',
    'date_reg',
    'installation_id',
    'status',
    'pack_price_alta_id',
    'service_recharge_id'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\History_inv_status
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
  public static function newUrlPayment($data, $cita, $email_client, $service_recharge, $packpricesID = false)
  {
    if (!$packpricesID) {
      $pack_price = PackPrices::getServiceByPack($cita->pack_id, $cita->service_id);

      if (empty($pack_price)) {
        return false;
      } else {
        $pack_price = $pack_price->id;
      }
    } else {
      $pack_price = $packpricesID;
    }
    return self::getConnect('W')
      ->insertGetId([
        'email_client' => $email_client,
        'subscription_id' => $data['suscripcion_id'],
        'external_reference' => $data['external_reference'],
        'url_payment' => $data['url'],
        'date_reg' => date('Y-m-d H:i:s'),
        'installation_id' => $cita->id,
        'pack_price_alta_id' => $pack_price,
        'service_recharge_id' => $service_recharge]);
  }

  public static function getDataPayment($idHistory)
  {
    return self::getConnect('R')
      ->where('id', $idHistory)
      ->where('status', 'A')
      ->first();
  }

  public static function setDescartPreviusPayment($idCita, $idpayment = false)
  {
    $update = self::getConnect('W')
      ->where([
        ['installation_id', $idCita],
        ['status', '!=', 'T']]);

    if ($idpayment) {
      $update = $update->where('id', '!=', $idpayment);
    }
    return $update->update(['status' => 'T']);
  }

}
