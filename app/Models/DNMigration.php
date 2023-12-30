<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DNMigration extends Model
{
  use HasFactory;

  protected $table = 'islim_dns_migrations';

  protected $fillable = [
    'msisdn',
    'status',
    'date_reg'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\DNMigration
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new DNMigration;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getDnAvailable()
  {
    $dn = self::getConnect('R')
      ->select('msisdn')
      ->where('status', 'A')
      ->first();

    if (!empty($dn)) {
      self::setDNBusy($dn->msisdn);
      return $dn;
    }

    return null;
  }

  public static function setDNBusy($msisdn)
  {
    return self::getConnect('W')
      ->where('msisdn', $msisdn)
      ->update(['status' => 'B']);
  }

  public static function setProcess($msisdn, $status)
  {
    return self::getConnect('W')
      ->where('msisdn', $msisdn)
      ->update(['status' => $status]);
  }

  public static function setMSISDN_listMigrations($msisdn)
  {
    return self::getConnect('W')
      ->insert([
        'msisdn'   => $msisdn,
        'status'   => 'A',
        'date_reg' => date('Y-m-d H:i:s'),
      ]);
  }
  public static function getExist_MSISDNlistMigrations($msisdn)
  {
    $ban = self::getConnect('R')
      ->select('msisdn')
      ->where('msisdn', $msisdn)
      ->first();
    if (!empty($ban)) {
      return true;
    }
    return false;
  }
}
