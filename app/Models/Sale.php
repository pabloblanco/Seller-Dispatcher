<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
  use HasFactory;

  protected $table = 'islim_sales';

  protected $fillable = [
    'services_id',
    'concentrators_id',
    'assig_pack_id',
    'inv_arti_details_id',
    'api_key',
    'users_email',
    'packs_id',
    'order_altan',
    'unique_transaction',
    'codeAltan',
    'type',
    'id_point',
    'description',
    'amount',
    'amount_net',
    'com_amount',
    'msisdn',
    'conciliation',
    'lat',
    'lng',
    'position',
    'date_reg',
    'status',
    'sale_type',
    'from',
    'is_migration',
    'user_locked',
    'typePayment'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\\Models\Sale
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Sale;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para obtener montos de venta de un usuario y estatus dado
   * @param String $email
   * @param String $status
   *
   * @return App\\Models\Sale
   */
  public static function getSalesByuser($email, $status)
  {
    return self::getConnect('R')
      ->select('id', 'amount', 'date_reg')
      ->where([
        ['status', $status],
        ['users_email', $email],
        ['amount', '>', 0]]);
  }

  /**
   * Metodo para obtener monto total de un grupo de venta dadas sus transacciones unicas
   * tipo y usuario
   * @param String $user
   * @param String $type
   * @param String $transactions
   *
   * @return App\\Models\Sale
   */
  public static function getSumSalesIns($user, $type, $transactions)
  {
    return self::getConnect('R')
      ->select(
        DB::raw('COUNT(users_email) as total_sales'),
        DB::raw('SUM(amount) as total_mount')
      )
      ->where([
        ['users_email', $user],
        ['status', 'E'],
        ['islim_sales.sale_type', $type]])
      ->whereNotIn('unique_transaction', $transactions)
      ->groupBy('users_email')
      ->first();
  }

  /**
   * Metodo para obtener ventas no conciliadas de un usuario dado
   * @param String $user
   *
   * @return App\\Models\Sale
   */
  public static function getUnConciSales($user)
  {
    return self::getConnect('R')
      ->select(
        'islim_sales.date_reg',
        'islim_sales.amount',
        'islim_sales.msisdn',
        'islim_sales.sale_type',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_services.title as service',
        'islim_packs.title as pack'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_sales.services_id'
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_sales.packs_id'
      )
      ->where([
        ['islim_sales.users_email', $user],
        ['islim_sales.status', 'E'],
        ['islim_sales.type', 'V']])
    //->whereBetween('islim_sales.date_reg', [$dateA, $dateB])
      ->get();
  }

  /**
   * Metodo para obtener totales de ventas (Abonos o normales)
   * @param Array $filters
   *
   * @return App\\Models\Sale
   */
  public static function getTotalSalesByType($filters = [])
  {
    $whtI = DB::raw('(SELECT id FROM islim_sales_installments AS i WHERE i.unique_transaction = islim_sales.unique_transaction AND (i.status = "P" OR i.status = "F"))');

    $data = self::getConnect('R')
      ->select(
        DB::raw('COUNT(islim_sales.users_email) AS total_sales'),
        DB::raw('SUM(islim_sales.amount) AS total_mount')
      )
      ->where([
        ['islim_sales.users_email', $filters['user']],
        ['islim_sales.type', 'V']])
      ->whereIn('islim_sales.status', ['A', 'E']);

    if (!empty($filters['type'])) {
      $data = $data->where('islim_sales.sale_type', $filters['type']);
    }

    if (!empty($filters['dateB'])) {
      $data = $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
    }

    if (!empty($filters['dateE'])) {
      $data = $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
    }

    if ($filters['whtI']) {
      $data = $data->whereNull($whtI);
    } else {
      $data = $data->whereNotNull($whtI);
    }

    return $data->groupBy('islim_sales.users_email')
      ->first();
  }

  /**
   * Metodo para detalles de ventas
   * @param Array $filters
   *
   * @return App\\Models\Sale
   */
  public static function getDetailSalesUser($filters = [])
  {
    //Abono
    $whtI = DB::raw('(select id from islim_sales_installments as i where i.unique_transaction = islim_sales.unique_transaction and (i.status = "P" or i.status = "F"))');

    $data = self::getConnect('R')
      ->select(
        'islim_sales.msisdn',
        'islim_clients.name',
        'islim_clients.last_name'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->where([
        ['islim_sales.users_email', $filters['user']],
        ['islim_sales.type', 'P']])
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      });

    if (!empty($filters['type'])) {
      $data->where('islim_sales.sale_type', $filters['type']);
    }

    if (!empty($filters['dateB'])) {
      $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
    }

    if (!empty($filters['dateE'])) {
      $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
    }

    if ($filters['whtI']) {
      $data->whereNull($whtI);
    } else {
      $data->whereNotNull($whtI);
    }

    return $data->get();
  }

  /**
   * Metodo para obtener ventas no reportadas(entrega de efectivo) de un vendedor
   * @param String $seller
   *
   * @return App\\Models\Sale
   */
  public static function getActiveSalesBySeller($seller)
  {
    return self::getConnect('R')
      ->select('id')
      ->where([
        ['users_email', $seller],
        ['status', 'E'],
        ['amount', '>', 0],
      ])
      ->get();
  }

  /**
   * Metodo para obtener numero de ventas
   * @param String $type
   * @param String $dateB
   * @param String $dateE
   * @param String $user
   *
   * @return App\\Models\Sale
   */
  public static function getSalesMetric($type, $dateB, $dateE, $user)
  {
    $subInst = DB::raw('(select id from islim_sales_installments as i where i.unique_transaction = islim_sales.unique_transaction and (i.status = "P" or i.status = "F"))');

    $ventasT = self::getConnect('R')
      ->select(DB::raw('COUNT(users_email) as total_sales'))
      ->whereBetween(
        'islim_sales.date_reg',
        [$dateB, $dateE]
      )
      ->where(function ($query) use ($user) {
        $query->where('islim_sales.users_email', $user)
          ->where('islim_sales.type', 'P');
      })
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      })
      ->groupBy('islim_sales.users_email');

    if ($type == 'inst') {
      $ventasT->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales.unique_transaction'
      );
    } else {
      if ($type == 't') {
        $ventasT->where('islim_sales.sale_type', 'T');
      } elseif ($type == 'mi') {
        $ventasT->where('islim_sales.sale_type', 'M');
      } elseif ($type == 'mih') {
        $ventasT->where('islim_sales.sale_type', 'MH');
      } elseif ($type == 'f') {
        $ventasT->where('islim_sales.sale_type', 'F');
      } else {
        $ventasT->where('islim_sales.sale_type', 'H');
      }
    }

    return $ventasT->first();
  }

  /**
   * Metodo para obtener detalle de ventas
   * @param String $type
   * @param String $dateB
   * @param String $dateE
   * @param String $user
   *
   * @return App\\Models\Sale
   */
  public static function getSalesMetricDetail($type, $dateB, $dateE, $user)
  {
    $subInst = DB::raw('(select id from islim_sales_installments as i where i.unique_transaction = islim_sales.unique_transaction and (i.status = "P" or i.status = "F"))');

    $data = self::getConnect('R')
      ->select(
        'islim_sales.msisdn',
        'islim_clients.name',
        'islim_clients.last_name'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->whereBetween(
        'islim_sales.date_reg',
        [$dateB, $dateE]
      )
      ->where(function ($query) use ($user) {
        $query->where('islim_sales.users_email', $user)
          ->where('islim_sales.type', 'P');
      })
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      });

    if ($type == 'inst') {
      $data->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales.unique_transaction'
      );
    } else {
      if ($type == 't') {
        $data->where('islim_sales.sale_type', 'T');
      } elseif ($type == 'mi') {
        $data->where('islim_sales.sale_type', 'M');
      } elseif ($type == 'mih') {
        $data->where('islim_sales.sale_type', 'MH');
      } elseif ($type == 'f') {
        $data->where('islim_sales.sale_type', 'F');
      } else {
        $data->where('islim_sales.sale_type', 'H');
      }
    }

    return $data->get();
  }

  /**
   * Metodo para obtener datos de venta dado un msisdn
   * @param String $dn
   *
   * @return App\\Models\Sale
   */
  public static function getSaleByDn($dn = false, $type = 'V')
  {
    if ($dn) {
      return self::getConnect('R')
        ->select(
          'id',
          'users_email',
          'packs_id',
          'amount',
          'unique_transaction'
        )
        ->where([
          ['msisdn', $dn],
          ['status', '!=', 'T'],
          ['type', $type]])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para marcar ventas como entrega a su superior
   * @param Array $transations
   *
   * @return App\\Models\Sale
   */
  public static function markAssign($transations = [])
  {
    return self::getConnect('W')
      ->whereIn('unique_transaction', $transations)
      ->update(['status' => 'A']);
  }

  /**
   * Metodo para obtener ventas no reportadas al superior
   * @param Array $transations
   *
   * @return App\\Models\Sale
   */
  public static function getNotConciliationSalesByUser($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'id',
        'unique_transaction',
        'amount',
        'msisdn'
      )
      ->where('status', 'E');

    if (count($filters)) {
      if (!empty($filters['user'])) {
        $data->where('users_email', $filters['user']);
      }

      if (!empty($filters['sales'])) {
        $data->whereIn('id', $filters['sales']);
      }
    }

    return $data;
  }

  /**
   * Metodo para obtener ventas no reportadas al superior
   * @param Array $transations
   *
   * @return App\\Models\Sale
   */
  public static function getSalePendingReport($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_sales.id',
        'islim_sales.msisdn',
        'islim_sales.amount',
        'islim_sales.unique_transaction',
        //'islim_concentrators.name as concentrator',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_packs.title as pack',
        'islim_services.title as service',
        'islim_inv_articles.title as product',
        'islim_sales.date_reg'
      )
    /*->join(
    'islim_concentrators',
    'islim_concentrators.id',
    'islim_sales.concentrators_id'
    )*/
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_sales.packs_id'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_sales.services_id'
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_sales.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->where('islim_sales.type', 'V');
    //'islim_sales.amount', '>', 0

    if (count($filters)) {
      if (!empty($filters['status'])) {
        $data->where('islim_sales.status', $filters['status']);
      }

      if (!empty($filters['notTransactions']) && count($filters['notTransactions'])) {
        $data->whereNotIn('islim_sales.unique_transaction', $filters['notTransactions']);
      }

      if (!empty($filters['user'])) {
        $data->where('islim_sales.users_email', $filters['user']);
      }

      //if(!empty($filters['']))
    }

    return $data->get();
  }

/**
 * [getSaleUser Obtiene el detalle de las ventas realizada por el usuario]
 * @param  array  $filters [description]
 * @return [type]          [description]
 */
  public static function getSaleUser($filters = [])
  {
    //Abono
    $whtI = DB::raw('(SELECT id FROM islim_sales_installments AS i WHERE i.unique_transaction = islim_sales.unique_transaction AND (i.status = "P" OR i.status = "F"))');

    $data = self::getConnect('R')
      ->select('islim_sales.msisdn',
        'islim_sales.date_reg',
        'islim_sales.amount',
        'islim_inv_articles.artic_type',
        'islim_inv_articles.title'
      )
      ->join('islim_inv_arti_details',
        'islim_inv_arti_details.msisdn',
        'islim_sales.msisdn')
      ->join('islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id')
      ->where([
        ['islim_sales.type', 'V'],
        ['islim_sales.users_email', $filters['user']],
        ['islim_sales.date_reg', '>=', $filters['dateB']],
        ['islim_sales.date_reg', '<=', $filters['dateE']]])
      ->whereIn('islim_sales.status', ['A', 'E']);

    if ($filters['whtI']) {
      $data = $data->whereNull($whtI);
    } else {
      $data = $data->whereNotNull($whtI);
    }

    $data = $data->orderBy('islim_sales.date_reg', 'DESC');
    //  ->get();
    return $data;
  }
}
