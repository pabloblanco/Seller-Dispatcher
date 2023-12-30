<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MPLinksPayment extends Model
{
  use HasFactory;
  protected $table = 'islim_mp_links';

  protected $fillable = [
    'id_mp_link',
    'id_subscription',
    'service_id',
    'payment_id',
    'bill_id',
    'msisdn',
    'external_reference',
    'payer_email',
    'init_point',
    'collector_id',
    'currency',
    'status_pay',
    'date_reg',
    'date_update',
    'parent_subscription_id',
    'service_type',
    'installation_id',
    'pack_price_id',
    'client_id'];

  public $timestamps = false;
/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\islim_mp_links
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

  public static function registerDNFiber($subscription_id, $payer_mail, $msisdn)
  {
    try {
      $update = self::getConnect('W')
        ->where([
          ['id_subscription', $subscription_id],
          ['payer_email', $payer_mail],
          ['status_pay', 'A']])
        ->whereNull('msisdn')
        ->first();

      if (!empty($update)) {
        $update->msisdn = $msisdn;
        $update->save();
        return array('success' => true, 'msg' => 'OK');
      }
      return array('success' => false, 'msg' => 'No se encontro registros en ' . $this->table);
    } catch (Exception $e) {
      $txmsg = "No se pudo modificar el registro de pago recurrente de la cita (71) " . (String) json_encode($e->getMessage());
      Log::error($txmsg);
      return array('success' => false, 'msg' => $txmsg);
    }
  }

}
