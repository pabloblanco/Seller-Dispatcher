<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowRequest extends Model
{
  use HasFactory;

  protected $table = 'islim_request_dismissal';

  protected $fillable = [
    'id',
    'user_req',
    'user_dismissal',
    'id_reason',
    'reason_deny',
    'user_process',
    'cash_request',
    'days_cash_request',
    'article_request',
    'status',
    'date_reg',
    'date_step1',
    'date_step2',
    'discounted_amount',
    'user_finish',
    'cash_abonos',
    'cant_abonos',
    'cash_total',
    'cash_hbb',
    'cash_telf',
    'cash_mifi',
    'cash_fibra'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\LowRequest
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new LowRequest;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * [getPullRequest Obtiene el registro de la ultima solicitud para poder asociar la evidencia si la hay]
   * @param  [type] $supervisor [description]
   * @param  [type] $userLow    [description]
   * @param  [type] $reason     [description]
   * @return [type]             [description]
   */
  public static function getPullRequest($supervisor, $userLow, $reason)
  {
    return self::getConnect('R')
      ->select('id')
      ->where([['user_req', $supervisor],
        ['user_dismissal', $userLow],
        ['status', 'R'],
        ['id_reason', $reason]])
      ->orderBy('date_reg', 'DESC')
      ->first();
  }

  /**
   * [getUsersLowsInProcess Obtiene usuarios con procesos de baja]
   * @param  [type] $supervisor [description]
   * @return [type]             [description]
   */
  public static function getUsersLowsInProcess($supervisor, $filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_users.name',
        'islim_users.last_name',
        'islim_request_dismissal.user_dismissal',
        'islim_request_dismissal.id_reason',
        'islim_request_dismissal.reason_deny',
        'islim_request_dismissal.status',
        'islim_request_dismissal.date_reg',
        'islim_request_dismissal.date_step1',
        'islim_reason_dismissal.reason'
      )
      ->join('islim_reason_dismissal', 'islim_reason_dismissal.id', 'islim_request_dismissal.id_reason')
      ->join('islim_users', 'islim_users.email', 'islim_request_dismissal.user_dismissal')
      ->where([
        ['islim_request_dismissal.user_req', $supervisor],
      ])
      ->whereIn('islim_request_dismissal.status', ['R', 'P', 'D']);

    if (count($filters)) {
      if (!empty($filters['status'])) {
        $data = $data->where('islim_request_dismissal.status', $filters['status']);
      }

      if (!empty($filters['vendor'])) {
        $data = $data->where('islim_request_dismissal.user_dismissal', $filters['vendor']);
      }
    }

    $data = $data->orderBy('islim_request_dismissal.date_reg', 'DESC')
      ->get();

    return $data;
  }

/**
 * [getAvailableRequest Verifica que no exista peticion de bajas o en proceso para solicitar el mismo usuario]
 * @param  [type] $emailLow [description]
 * @return [type]           [description]
 */
  public static function getAvailableRequest($emailLow)
  {
    $data = self::getConnect('R')
      ->where('user_dismissal', $emailLow)
      ->whereIn('status', ['R', 'P'])
      ->first();

    if (empty($data)) {
      return true;
    }
    return false;
  }

  public static function getInProcessRequestByUser($email)
  {
    return self::getConnect('W')
      ->select(
        'id',
        'article_request',
        'cash_hbb',
        'cash_telf',
        'cash_mifi',
        'cash_fibra',
        'cash_request',
        'cash_abonos',
        'cash_total'
      )
      ->where('user_dismissal', $email)
      ->whereIn('status', ['R', 'P'])
      ->first();
  }

  public static function getRequestByStatusAndUser($user, $status)
  {
    return self::getConnect('R')
      ->select('id')
      ->where([
        ['status', $status],
        ['user_dismissal', $user]])
      ->first();
  }
}
