<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoppelLogs extends Model
{
  use HasFactory;

  protected $table = 'islim_coppel_logs';

	protected $fillable = [
    'transaction_code',
    'msisdn',
    'data_out',
    'data_in',
    'end_point',
    'date_reg'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\CoppelLogs
  */
  public static function getConnect($typeCon = false){
      if($typeCon){
          $obj = new CoppelLogs;
          $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

          return $obj;
      }
      return null;
  }
}