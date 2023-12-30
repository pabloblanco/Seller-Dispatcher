<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Coppel extends Model
{
  use HasFactory;

  protected $table = 'islim_coppel';

	protected $fillable = [
    'transaction_code',
    'request',
    'token',
    'ip',
    'auth_code',
    'auth',
    'signature',
    'msisdn',
    'amount',
    'clients_dni',
    'service_id',
    'pack_id',
    'articles_id',
    'user_email',
    'user_associated',
    'error',
    'status',
    'date_reg',
    'date_associated'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Coppel
  */
  public static function getConnect($typeCon = false){
      if($typeCon){
          $obj = new Coppel;
          $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

          return $obj;
      }
      return null;
  }

  public static function getTotalReg(){
    return self::getConnect('R')
                ->select('id')
                ->count();
  }

  public static function firstInsert($data){
    return self::getConnect('W')->insertGetId($data);
  }

  public static function setRequest($id, $req){
    return self::getConnect('W')
                ->where('id', $id)
                ->update(['request' => $req]);
  }

  public static function setStatus($id, $st, $msg = ''){
    return self::getConnect('W')
                ->where('id', $id)
                ->update(['status' => $st, 'error' => $msg]);
  }

  public static function getLast($msisdn){
    return self::getConnect('W')
                ->select('id', 'request', 'amount', 'pack_id', 'transaction_code')
                ->where([['msisdn', $msisdn], ['status', 'I']])
                ->whereNotNull('request')
                ->orderBy('id', 'DESC')
                ->first();
  }
}