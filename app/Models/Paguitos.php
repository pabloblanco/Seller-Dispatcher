<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paguitos extends Model
{
  use HasFactory;
  protected $table = 'islim_paguitos';

  protected $fillable = [
    'id',
    'dni',
    'msisdn',
    'initial_amount',
    'total_amount',
    'sale_id',
    'seller_name',
    'cve_seller',
    'cve_branch',
    'branch_name',
    'status',
    'date_reg',
    'date_process',
    'cve_solicitud',
    'date_enganche'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Models\Paguitos
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Paguitos;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getActiveFinancingBydn Metodo que busca si tengo una solicitud de financiamiento de paguitos]
 * @param  boolean $msisdn [msisdn al que queremos revisar si hay financiacion]
 * @return [type]          [description]
 */
  public static function getActiveFinancingBydn($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->select(
          'id',
          'dni',
          'msisdn',
          'initial_amount',
          'total_amount',
          'sale_id',
          'seller_name',
          'date_reg',
          'date_enganche')
        ->where([
          ['msisdn', $msisdn],
          ['status', 'A']])
        ->first();
    }
    return null;
  }

/**
 * [UpdateFinancingBydn Metodo que actualiza los datos cuando se consulta la financiacion de un msisdn]
 * @param [type] $id             [id de la financiacion de pagitos]
 * @param [type] $paguitoRequest [description]
 */
  public static function UpdateFinancingBydn($id, $paguitoRequest)
  {
    $paguitoRequest = json_decode(json_encode($paguitoRequest));

    //Tratamiento de fecha
    $barras    = array("\/", "/");
    $clearDate = str_replace($barras, "-", $paguitoRequest->fecha);
    $extra     = array("a", "m", "p", ".");
    $clearDate = str_replace($extra, "", $clearDate);

    $date    = date_create($clearDate);
    $newDate = date_format($date, 'Y-m-d H:i:s');

    //$DateTime = \DateTime::createFromFormat('d-m-Y H:i:s', $clearDate);
    //$newDate  = $DateTime->format('Y-m-d H:i:s');

    $obj = self::getConnect('W')
      ->where('id', $id)
      ->update([
        'initial_amount' => $paguitoRequest->monto_pagado,
        'seller_name'    => $paguitoRequest->vendedor,
        'cve_seller'     => $paguitoRequest->cve_vendedor,
        'cve_branch'     => $paguitoRequest->cve_sucursal,
        'branch_name'    => $paguitoRequest->sucursal,
        'cve_solicitud'  => $paguitoRequest->cve_solicitud,
        'date_enganche'  => $newDate]);

    sleep(2);
    return true;
  }

/**
 * [marckAsProcess Metodo que indica que es procesada la asociacion de financiacion]
 * @param  [type] $msisdn [description]
 * @return [type]         [description]
 */
  public static function marckAsProcess($msisdn)
  {
    return self::getConnect('W')
      ->where([
        ['msisdn', $msisdn],
        ['status', 'A']])
      ->update([
        'status'       => 'P',
        'date_process' => date('Y-m-d H:i:s'),
      ]);
  }

}
