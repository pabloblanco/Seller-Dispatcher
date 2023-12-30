<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerInventory extends Model
{
  use HasFactory;

  protected $table = 'islim_inv_assignments';

  protected $fillable = [
    'users_email',
    'inv_arti_details_id',
    'obs',
    'status',
    'date_reg',
    'first_assignment',
    'date_orange',
    'date_red',
    'last_assigned_by',
    'last_assignment',
    'user_red',
    'red_notification_view'];

  protected $primaryKey = [
    'users_email',
    'inv_arti_details_id'];

  public $incrementing = false;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\SellerInventory
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SellerInventory;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para consultar articulos asignados a un usuario dado su tipo
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function getArticsAssign($user = false, $type = 'H')
  {
    if ($user) {
      return self::getConnect('R')
        ->select('islim_inv_assignments.inv_arti_details_id')
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments.users_email', $user],
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_articles.artic_type', $type]])
        ->get();

    }

    return [];
  }

  /**
   * Metodo para consultar articulos asignados a un usuario
   * @param String $user
   *
   * @return App\Models\SellerInventory
   */
  public static function getAllArticsAssign($user = false)
  {
    if ($user) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.title',
          'islim_inv_articles.artic_type',
          'islim_inv_arti_details.iccid',
          'islim_inv_arti_details.imei',
          'islim_inv_assignments.date_reg'
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments.users_email', $user],
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_arti_details.status', 'A']])
        ->get();

    }

    return [];
  }

  /**
   * Metodo para eliminar asignación de articulo a un usuario dado
   * @param String $id
   * @param String $user
   *
   * @return App\Models\SellerInventory
   */
  public static function cleanAssign($id = false, $user = false)
  {
    if ($id && $user) {
      self::getConnect('W')
        ->where([
          ['inv_arti_details_id', $id],
          ['users_email', '!=', $user]])
        ->update([
          'status' => 'T']);
    }

    return false;
  }

  /**
   * Metodo para marcar como vendido un articulo
   * @param String $id
   * @param String $user
   * @param String $orgType
   *
   * @return App\Models\SellerInventory
   */
  public static function markSale($id = false, $user = false, $orgType = 'N')
  {
    if ($id && $user) {
      $exist = self::getConnect('R')
        ->select('users_email')
        ->where([
          ['users_email', $user],
          ['inv_arti_details_id', $id]])
        ->first();

      if (!empty($exist)) {
        try {
          self::getConnect('W')
            ->where([['inv_arti_details_id', $id], ['status', 'A']])
            ->update(['status' => 'P']);

          return array('success' => true, 'msg' => 'OK');
        } catch (Exception $e) {
          $txmsg = "No se pudo actualizar el detalle de inventario " . $id . " - " . (String) json_encode($e->getMessage());
          Log::error($txmsg);
          return array('success' => false, 'msg' => $txmsg);
        }
      } elseif ($orgType == 'R') {
        //Si no se le ha asignado el articulo se hace la asignacion y se marca como vendido
        try {
          self::getConnect('W')
            ->insert([
              'users_email' => $user,
              'inv_arti_details_id' => $id,
              'date_reg' => date("Y-m-d H:i:s"),
              'status' => 'P',
              'obs' => 'Auto asignado - Retail']);

          return array('success' => true, 'msg' => 'OK');
        } catch (Exception $e) {
          $txmsg = "No se pudo insertar el detalle de inventario " . $id . " - " . (String) json_encode($e->getMessage());
          Log::error($txmsg);
          return array('success' => false, 'msg' => $txmsg);
        }
      }
    }
    return array('success' => false, 'msg' => 'Falta informacion para continuar');
  }

  /**
   * Metodo para consultar info de articulo dado su dn y usuario que lo tiene asignado
   * @param String $id
   * @param String $user
   * @param String $orgType
   *
   * @return App\Models\SellerInventory
   */
  public static function getArticByDnAndUser($msisdn = false, $user = false)
  {
    if ($msisdn && $user) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.inv_article_id',
          'islim_inv_arti_details.msisdn',
          'islim_inv_arti_details.serial',
          'islim_inv_arti_details.iccid',
          'islim_inv_arti_details.imei',
          'islim_inv_articles.title',
          'islim_inv_articles.description',
          'islim_inv_articles.artic_type'
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_assignments.users_email', $user],
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_arti_details.msisdn', $msisdn],
          ['islim_inv_articles.status', 'A']])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para consultar info de los articulos por tipo asignados a un usuario
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function getArticsAssignData($user = false, $type = 'H')
  {
    if ($user) {

      $ret = self::getConnect('R')
        ->select(
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.title',
          'islim_inv_articles.artic_type',
          'islim_inv_arti_details.imei',
          'islim_inv_arti_details.iccid',
          'islim_inv_arti_details.price_pay',
          'islim_inv_assignments.date_reg',
          DB::raw('CONCAT("N") as preassigned'),
          DB::raw('(case
            when islim_inv_assignments.date_red is not null then "red"
            when islim_inv_assignments.date_orange is not null then "orange"
            ELSE null
          end) as color')
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments.users_email', $user],
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_articles.artic_type', $type]])
      //->whereNull('islim_inv_assignments.date_red')
        ->whereRaw("islim_inv_assignments.inv_arti_details_id NOT IN ( SELECT islim_inv_assignments_temp.inv_arti_details_id FROM islim_inv_assignments_temp WHERE islim_inv_assignments_temp.status = 'P' AND islim_inv_assignments_temp.inv_arti_details_id = islim_inv_assignments.inv_arti_details_id )");

      return $ret->get();

    }

    return [];
  }

  /**
   * Metodo para validar si un articulo esta asignado a un usuario dado su identificador de asignación y el usuario
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function getAsignmentUser($articId, $seller)
  {
    return self::getConnect('R')
      ->select('date_reg')
      ->where([
        ['inv_arti_details_id', $articId],
        ['users_email', $seller]])
      ->first();
  }
/**
 * [getAsignmentUserRED Revisa si el equipo que esta en rojo lo esta cambiando el usuario propietario]
 * @param  [type] $articId [description]
 * @param  [type] $seller  [description]
 * @return [type]          [description]
 */
  public static function getAsignmentUserRED($articId, $seller = false)
  {
    $data = self::getConnect('R')
      ->select('user_red')
      ->where([
        ['inv_arti_details_id', $articId],
        ['status', 'A']]);

    if ($seller) {
      $data = $data->where('user_red', $seller);
    } else {
      $data = $data->whereNotNull('user_red');
    }
    $data = $data->first();
    return $data;
  }

/**
 * [isValidChangeStatus revisa si el dn que quieren cambiar de status pertenece al usuario logeado, esta en rojo y esta activo]
 * @param  [type]  $dn [description]
 * @return boolean     [description]
 */
  public static function isValidChangeStatus($dn)
  {
    $datos = self::getConnect('R')
      ->select('islim_inv_assignments.inv_arti_details_id')
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_inv_assignments.inv_arti_details_id'
      )
      ->where([
        ['islim_inv_assignments.status', 'A'],
        ['islim_inv_assignments.user_red', session('user')],
        ['islim_inv_arti_details.msisdn', $dn]])
      ->whereNotNull('islim_inv_assignments.date_red')
      ->first();

    return $datos;
  }

/**
 * [getDNsWithAlertByUsers muestra el listado de Dn que estan en rojo y naranaja de un vendedor y de un coordinador ]
 * @param  array   $users     [usuario a revisar]
 * @param  boolean $typeColor [color que buscar (rojo o naranja)]
 * @return [type]             [objeto con el resultado de la consulta]
 */
  public static function getDNsWithAlertByUsers($users = [], $typeColor = false)
  {

    $lastStatus = DB::raw('(SELECT islim_history_status_inventory.status FROM islim_history_status_inventory WHERE islim_history_status_inventory.inv_arti_details_id = islim_inv_assignments.inv_arti_details_id AND
      islim_history_status_inventory.status != "T"
      ORDER BY islim_history_status_inventory.id DESC limit 1) AS ChangeStatus');

    $datos = self::getConnect('R')
      ->select(
        'islim_inv_assignments.users_email',
        DB::raw('CONCAT(origin_user.name," ",origin_user.last_name) as origin'),
        'islim_inv_assignments.date_orange',
        'islim_inv_assignments.date_red',
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_inv_articles.artic_type',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.phone',
        'islim_inv_assignments.inv_arti_details_id',
        $lastStatus
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_inv_assignments.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_inv_assignments.users_email'
      )
      ->leftJoin('islim_inv_assignments_tracks', function ($join) {
        $join->on('islim_inv_assignments_tracks.inv_arti_details_id', '=', 'islim_inv_assignments.inv_arti_details_id')
          ->whereRaw("islim_inv_assignments.users_email = islim_inv_assignments_tracks.destination_user")
          ->whereRaw("islim_inv_assignments_tracks.id = (SELECT MAX(iat.id) FROM islim_inv_assignments_tracks as iat WHERE iat.inv_arti_details_id = islim_inv_assignments.inv_arti_details_id  AND iat.destination_user  = islim_inv_assignments.users_email GROUP BY iat.inv_arti_details_id)");
      })
      ->leftJoin(
        'islim_users as origin_user',
        'origin_user.email',
        'islim_inv_assignments_tracks.origin_user'
      )
      ->leftJoin('islim_history_status_inventory', function ($join) use ($users) {
        $join->on('islim_history_status_inventory.inv_arti_details_id', '=', 'islim_inv_assignments.inv_arti_details_id')
          ->whereNotNull('islim_inv_assignments.date_red')
          ->whereIn('islim_history_status_inventory.status', ['C', 'R']);
      })
      ->where('islim_inv_assignments.status', 'A');

    if (session('user_type') != 'vendor') {
      //coordinador
      $datos = $datos->where(function ($q) use ($users) {
        $q->whereIn('islim_inv_assignments.users_email', $users)
          ->orWhereIn('islim_inv_assignments.user_red', $users);
      });

      if (empty($typeColor)) {
        $datos = $datos->where(function ($q) {
          $q->whereNotNull('islim_inv_assignments.date_orange')
            ->orWhereNotNull('islim_inv_assignments.date_red');
        });
      } else {
        if ($typeColor == 'O') {
          $datos = $datos->whereNotNull('islim_inv_assignments.date_orange')
            ->whereNull('islim_inv_assignments.date_red');
        } else {
          $datos = $datos->whereNotNull('islim_inv_assignments.date_red');
        }
      }
    } else {
      //vendedores
      if (empty($typeColor)) {

        $datos = $datos->where(function ($q) use ($users) {
          $q->where(function ($q2) use ($users) {
            $q2->whereIn('islim_inv_assignments.users_email', $users)
              ->whereNotNull('islim_inv_assignments.date_orange');
          })
            ->orWhere(function ($q3) use ($users) {
              $q3->whereIn('islim_inv_assignments.user_red', $users)
                ->whereNotNull('islim_inv_assignments.date_red');
            });
        });

      } else {
        if ($typeColor == 'O') {
          $datos = $datos->whereIn('islim_inv_assignments.users_email', $users)
            ->whereNotNull('islim_inv_assignments.date_orange')
            ->whereNull('islim_inv_assignments.date_red');
        } else {
          $datos = $datos->whereIn('islim_inv_assignments.user_red', $users)
            ->whereNotNull('islim_inv_assignments.date_red');
        }
      }
    }

    $datos = $datos
      ->orderBy('islim_inv_assignments.date_orange', 'ASC')
      ->orderBy('islim_inv_assignments.date_red', 'ASC')
      ->orderBy('islim_inv_assignments.users_email', 'ASC')
      ->groupBy('islim_inv_assignments.inv_arti_details_id')
      ->get();

    return $datos;
  }

  /**
   * Metodo para validar si un articulo esta asignado a un usuario dado su msisdn
   * y el usuario
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function isAssignedDn($msisdn = false, $user = false)
  {
    if ($msisdn && $user) {
      return self::getConnect('R')
        ->select(
          'islim_inv_arti_details.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->where([
          'islim_inv_assignments.users_email' => $user,
          'islim_inv_assignments.status' => 'A',
          'islim_inv_arti_details.status' => 'A',
          'islim_inv_arti_details.msisdn' => $msisdn])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para consultar si un usuario tiene notificaciones de inventario en status rojo sin ver
   * @param String $user
   *
   * @return boolean
   */
  public static function redStatusNotificationsPendingExists($user = false)
  {
    if ($user) {
      $alerts = self::getConnect('R')
        ->select(
          'islim_inv_assignments.users_email'
        )
        ->join('islim_inv_assignments_tracks', function ($join) {
          $join->on('islim_inv_assignments_tracks.inv_arti_details_id', '=', 'islim_inv_assignments.inv_arti_details_id')
            ->whereRaw('islim_inv_assignments_tracks.destination_user = islim_inv_assignments.users_email')
            ->whereRaw('islim_inv_assignments_tracks.origin_user = islim_inv_assignments.user_red');
        })
        ->where([
          'islim_inv_assignments.users_email' => $user,
          'islim_inv_assignments.status' => 'A',
          'islim_inv_assignments.red_notification_view' => 'N'])
        ->whereNotNull('islim_inv_assignments.date_red');

      // $query = vsprintf(str_replace('?', '%s', $alerts->toSql()), collect($alerts->getBindings())->map(function ($binding) {
      //         return is_numeric($binding) ? $binding : "'{$binding}'";
      //     })->toArray());

      // dd($query);

      $alerts = $alerts->get();

      if (count($alerts)) {
        self::getConnect('W')
          ->where([
            ['users_email', $user],
            ['status', 'A'],
            ['red_notification_view', 'N']])
          ->whereNotNull('islim_inv_assignments.date_red')
          ->update([
            'red_notification_view' => 'Y']);

        return true;
      }
    }
    return false;
  }

/**
 * [setValidMotive Cambia de status un equipo asignado al usuario que pidio cambio de status de inventario de rojo a naranja]
 */
  public static function setValidMotive()
  {

    if (session('user_type') == 'vendor') {
      //coloco en status T el registro del coordinador y activo y actualizo el registro del vendedor
      //
      //Busco el coordinador del vendedor
      //
      $coord = User::getParentUser2(session('user'));
      if (!empty($coord)) {

        $deleteCoord = self::getConnect('W')
          ->where([
            ['users_email', $coord->parent_email],
            ['inv_arti_details_id', session('id_InvRed')],
            ['user_red', session('user')],
            ['status', 'A']])
          ->whereNotNull('date_red')
          ->update([
            'status' => 'T']);
      }
    }
    $date_reg = Carbon::now()->copy()->subDays(20)->format('Y-m-d H:i:s');

    return self::getConnect('W')
      ->where([
        ['users_email', session('user')],
        ['inv_arti_details_id', session('id_InvRed')]])
    //->whereNotNull('islim_inv_assignments.date_red')
    //->whereIn('status', ['A', 'T'])
      ->where(function ($q) {
        $q->where(function ($q2) {
          $q2->where('status', 'A')
            ->whereNotNull('date_red');
        })
          ->orWhere(function ($q3) {
            $q3->where('status', 'T');
          });
      })
      ->update([
        'date_orange' => date('Y-m-d H:i:s'),
        'date_reg' => $date_reg,
        'status' => 'A',
        'date_red' => null,
        'user_red' => null,
        'last_assigned_by' => session('user'),
        'last_assignment' => date('Y-m-d H:i:s'),
        'red_notification_view' => null]);
  }

  public static function getAssigneArt($idArt)
  {
    return self::getConnect('R')
      ->select('users_email')
      ->where([['status', 'A'],
        ['inv_arti_details_id', $idArt]])
      ->first();
  }

  public static function newAssigneArt($NewArt)
  {
    try {
      $newAssigne = self::getConnect('W');
      $newAssigne->users_email = session('user');
      $newAssigne->inv_arti_details_id = $NewArt->id;
      $newAssigne->obs = "Auto-asignado desde Seller por proceso de alta en zona de fibra con instalador Velocom";
      $newAssigne->date_reg = date('Y-m-d H:i:s');
      $newAssigne->status = 'A';
      $newAssigne->first_assignment = date('Y-m-d H:i:s');
      $newAssigne->save();

    } catch (Exception $e) {
      $txmsg = "No se pudo asignar el producto creado al instalador " . (String) json_encode($e->getMessage());
      Log::error($txmsg);
      return array('success' => false, 'msg' => $txmsg);
    }

    //Se hace el trakking del inventario
    //
    $Idtrakin = SellerInventoryTrack::setInventoryTrack(
      $NewArt->id,
      null,
      $NewArt->warehouses_id,
      session('user'),
      null,
      session('user'),
      'Auto-asignado desde seller por instalador velocom');

    if (!$Idtrakin['success']) {
      return array('success' => false, 'msg' => $Idtrakin['msg']);
    } else {
      return array('success' => true, 'idAssigne' => $newAssigne->id);
    }
  }

}
