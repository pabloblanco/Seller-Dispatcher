<?php

namespace App\Models;

use App\Models\ArticlePack;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $table = 'islim_users';

  protected $fillable = [
    'name',
    'last_name',
    'id_org',
    'email',
    'parent_email',
    'password',
    'dni',
    'platform',
    'phone',
    'phone_job',
    'profession',
    'position',
    'address',
    'status',
    'charger_com',
    'charger_balance',
    'residue_amount',
    'password_date',
    'url_latter_contract',
    'is_locked',
    'esquema_comercial_id',
    'reset_session',
    'fiber_city_zone_id',
    'last_conection_id'];

  protected $hidden = [
    'password', 'date_reg', 'date_mod',
  ];

  protected $primaryKey = 'email';

  protected $keyType = 'string';

  public $incrementing = false;

  const CREATED_AT = 'date_reg';

  const UPDATED_AT = 'date_mod';

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\User
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new User;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getInfoUser($email)
  {
    return self::getConnect('R')
      ->select('esquema_comercial_id')
      ->where('email', $email)
      ->first();
  }

  /**
   * Metodo para obtener verificar si un usuario esta activo
   * @param String $email
   *
   * @return App\Models\User
   */
  public static function isActive($email = false)
  {
    if ($email) {
      return self::getConnect('R')
        ->select('name')
        ->where([['email', $email], ['status', 'A']])
        ->first();
    }

    return null;
  }

  public static function canLogin($email = false)
  {
    if ($email) {
      return self::getConnect('R')
        ->select('name', 'reset_session')
        ->where('email', $email)
        ->whereIn('status', ['A', 'D'])
        ->first();
    }

    return null;
  }

  /**
   * Metodo para obtener datos de un usuario dado su email
   * @param String $email
   * @param String $tc
   *
   * @return App\Models\User
   */
  public static function getOnliyUser($email, $tc = 'R', $status = ['A'])
  {
    return self::getConnect($tc)
      ->select(
        'email',
        'dni',
        'name',
        'last_name',
        'phone',
        'charger_balance',
        'date_reg',
        'dateToken',
        'tokenPassword',
        'is_locked',
        'status'
      )
      ->where('email', $email)
      ->whereIn('status', $status)
      ->first();
  }

  public static function getUserByEmail($email)
  {
    return self::getConnect('R')
      ->select(
        'email',
        'parent_email',
        'dni',
        'name',
        'last_name',
        'phone',
        'charger_balance',
        'platform',
        'date_reg',
        'dateToken',
        'tokenPassword',
        'is_locked',
        'status'
      )
      ->where('email', $email)
      ->first();
  }

  /**
   * Metodo para obtener datos de un usuario junto con su perfil dado su email
   * @param String $email
   * @param String $tc
   *
   * @return App\Models\User
   */
  public static function getUser($email, $tc = 'R', $status = ['A'])
  {
    return self::getConnect($tc)
      ->select(
        'islim_users.email',
        'islim_users.dni',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.password',
        'islim_users.platform',
        'islim_users.id_org',
        'islim_users.status',
        'islim_users.reset_session',
        'islim_profiles.id as id_profile',
        'islim_profiles.name as name_profile',
        'islim_profiles.hierarchy',
        'islim_profiles.type'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile'
      )
      ->where([
        ['islim_users.email', $email],
        ['islim_profile_details.status', 'A']])
      ->whereIn('islim_users.status', $status)
      ->first();
  }

  /**
   * Metodo para obtener supervisor de un usuario dado su email
   * @param String $user
   *
   * @return App\Models\User
   */
  public static function getParentUser($user)
  {
    return self::getConnect('R')
      ->select('parent_email')
      ->where([
        ['email', $user],
        ['status', 'A']])
      ->first();
  }

  /**
   * Metodo para obtener supervisor de un usuario dado su email, independiente del estatus del usuario
   * @param String $user
   *
   * @return App\Models\User
   */
  public static function getParentUser2($user)
  {
    return self::getConnect('R')
      ->select('parent_email')
      ->where('email', $user)
      ->first();
  }

  /**
   * Metodo para consultar si un usuario es supervisor de otro
   * @param String $user
   * @param String $userSup
   *
   * @return App\Models\User
   */
  public static function isParent($user, $userSup)
  {
    return self::getConnect('R')
      ->select('email')
      ->where([
        ['email', $user],
        ['parent_email', $userSup]])
      ->first();
  }

  /**
   * Metodo para consultar el balance para recargas de un usuario dado su email
   * @param String $email
   *
   * @return App\Models\User
   */
  public static function getBalances($email)
  {
    return self::getConnect('R')
      ->select(
        'charger_balance',
        'residue_amount',
        'platform'
      )
      ->where('email', $email)
      ->first();
  }

  /**
   * Metodo para actualizar el balance para recargas de un usuario
   * @param String $user
   * @param Double $balance
   *
   * @return App\Models\User
   */
  public static function setBalance($user, $balance)
  {
    return self::getConnect('W')
      ->where('email', $user)
      ->update([
        'charger_balance' => $balance]);
  }

  /**
   * Metodo que retorna lista de los usuarios asociados a un usuario dado y a su vez los
   * usuarios asociados a estos (Realiza una busqueda recursiva)
   * @param String $email
   * @param Illuminate\Support\Collection $userList (Este parametro no se debe enviar en el llamado al método)
   *
   * @return App\Models\User
   */
  public static function getParents($email, &$userList = false, $hierarchy = false)
  {
    if ($userList === false) {
      $userList = collect();
    }

    $users = self::getParentsQuery($email, ['A'], $hierarchy);

    if (!$users->count()) {
      return $users;
    }

    foreach ($users as $user) {
      $userList->push($user);
      self::getParents($user->email, $userList, $user->hierarchy);
    }

    return $userList;
  }

  /**
   * Metodo que retorna lista de los usuarios en proceos de baja asociados a un usuario dado y a
   * su vez los usuarios asociados a estos (Realiza una busqueda recursiva)
   * @param String $email
   * @param Illuminate\Support\Collection $userList (Este parametro no se debe enviar en el llamado al método)
   *
   * @return App\Models\User
   */
  public static function getLowParents($email, &$userList = false, $hierarchy = false)
  {
    if ($userList === false) {
      $userList = collect();
    }

    $users = self::getParentsQuery($email, ['D'], $hierarchy);

    if (!$users->count()) {
      return $users;
    }

    foreach ($users as $user) {
      $userList->push($user);
      self::getLowParents($user->email, $userList, $user->hierarchy);
    }

    return $userList;
  }

  /**
   * Metodo que retorna lista de los usuarios asociados a un usuario dado y a su vez los
   * usuarios asociados a estos (Realiza una busqueda recursiva que incluye usuarios en proceso de bajas)
   * @param String $email
   * @param Illuminate\Support\Collection $userList (Este parametro no se debe enviar en el llamado al método)
   *
   * @return App\Models\User
   */
  public static function getParentsWD($email, &$userList = false, $hierarchy = false)
  {
    if ($userList === false) {
      $userList = collect();
    }

    $users = self::getParentsQuery($email, ['A', 'D'], $hierarchy);

    if (!$users->count()) {
      return $users;
    }

    foreach ($users as $user) {
      $userList->push($user);
      self::getParentsWD($user->email, $userList, $user->hierarchy);
    }

    return $userList;
  }

  private static function getParentsQuery($email, $status = ['A'], $hierarchy)
  {
    $users = self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.platform',
        'islim_profiles.name as name_profile',
        'islim_profiles.hierarchy',
        'islim_profiles.type'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile'
      )
      ->where([
        ['islim_users.parent_email', $email],
        ['islim_profile_details.status', 'A']])
      ->whereIn('islim_users.status', $status);

    if ($hierarchy) {
      $users->where('islim_profiles.hierarchy', '>', $hierarchy);
    }

    return $users->get();
  }

  /**
   * Metodo que retorna lista de los usuarios asociados a un usuario dado (1 solo nievel)
   * @param String $email
   *
   * @return App\Models\User
   */
  public static function getParentsOneLevel($email)
  {
    return self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.platform',
        'islim_profiles.name as name_profile',
        'islim_profiles.hierarchy',
        'islim_profiles.type'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile'
      )
      ->where([
        ['islim_users.parent_email', $email],
        ['islim_users.status', 'A'],
        ['islim_profile_details.status', 'A']])
      ->get();
  }

  /**
   * Metodo para consultar solo usuarios de tipo(OJO no perfil) vendedor
   * @param Array $filters
   *
   * @return App\Models\User
   */
  public static function getOnlySellers($filters)
  {
    $data = self::getConnect('R')
      ->select(
        'email',
        'name',
        'last_name'
      )
      ->where([
        ['platform', 'vendor'],
        ['status', 'A']]);

    if (!empty($filters['parents'])) {
      $data->whereIn('email', $filters['parents']);
    }

    return $data->get();
  }

  /**
   * Metodo para consultar usuarios segun los filtros dados
   * @param Array $filters
   *
   * @return App\Models\User
   */
  public static function getUsersByFilter($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.platform',
        'islim_profiles.name as name_profile',
        'islim_profiles.hierarchy',
        'islim_profiles.type',
        DB::raw("CONCAT(islim_users.name,' ', islim_users.last_name) AS info")
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile'
      )
      ->where('islim_profile_details.status', 'A');

    if (!empty($filters['users']) && empty($filters['me'])) {
      $data->whereIn('islim_users.email', $filters['users']);
    }

    if (!empty($filters['users']) && !empty($filters['me'])) {
      $data->where(function ($q) use ($filters) {
        $q->whereIn('islim_users.email', $filters['users'])
          ->orWhere('islim_users.email', $filters['me']);
      });
    }

    if (!empty($filters['likeName'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_users.name', 'like', '%' . $filters['likeName'] . '%')
          ->orWhere('islim_users.last_name', 'like', '%' . $filters['likeName'] . '%');
      });
    }

    if (!empty($filters['platform'])) {
      $data->where('islim_users.platform', $filters['platform']);
    }

    if (!empty($filters['status'])) {
      if (is_array($filters['status'])) {
        $data->whereIn('islim_users.status', $filters['status']);
      } else {
        $data->where('islim_users.status', $filters['status']);
      }
    } else {
      $data->where('islim_users.status', 'A');
    }

    return $data->get();
  }

  /**
   * Metodo para consultar si existe un hash
   * @param String $hash
   *
   * @return App\Models\User
   */
  public static function findHash($hash)
  {
    return self::getConnect('R')
      ->select('email')
      ->where([
        ['tokenPassword', $hash],
        ['status', 'A']])
      ->first();
  }

  /**
   * Metodo para consultar url de contrato de un usuario dado su dni
   * @param String $dni
   *
   * @return App\Models\User
   */
  public static function getUserByDni($dni)
  {
    return self::getConnect('R')
      ->select(
        'url_latter_contract',
        'dni'
      )
      ->where('dni', $dni)
      ->first();
  }

  public static function getSalesNotConcInstReport($filters = [])
  {
    $raw3m = DB::raw("TIMESTAMPDIFF(DAY, islim_sales.date_reg, '" . date('Y-m-d H:i:s') . "') as days");

    $data = self::getConnect('R')
      ->select(
        'islim_sales.id',
        'islim_sales.unique_transaction',
        'islim_sales.msisdn',
        'islim_sales.amount',
        'islim_sales.sale_type',
        'islim_sales.is_migration',
        'islim_users.name as name_seller',
        'islim_users.last_name as last_name_seller',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_client_netweys.lat',
        'islim_client_netweys.lng',
        'islim_sales.date_reg',
        $raw3m
      )
      ->join(
        'islim_sales',
        'islim_sales.users_email',
        'islim_users.email'
      )
      ->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales.unique_transaction'
      )
      ->join(
        'islim_sales_installments_detail',
        'islim_sales_installments_detail.unique_transaction',
        'islim_sales_installments.unique_transaction'
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
        //['islim_users.status', 'A'],
        ['islim_sales.type', 'V'],
        ['islim_sales.status', '!=', 'T'],
        ['islim_sales.unique_transaction', 'LIKE', 'ABO-%'],
        ['islim_sales_installments_detail.status', 'A']])
      ->where(function ($q) {
        $q->where('islim_sales_installments_detail.conciliation_status', 'CV')
          ->orWhere('islim_sales_installments_detail.conciliation_status', 'V');
      })
      ->orderBy('islim_sales.date_reg', 'ASC');

    if (count($filters)) {
      if (!empty($filters['seller'])) {
        $data->where('islim_sales.users_email', $filters['seller']);
      }

      if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
        $data->where([
          ['islim_sales.date_reg', '>=', $filters['dateB']],
          ['islim_sales.date_reg', '<=', $filters['dateE']]]);
      } else {
        if (!empty($filters['dateB'])) {
          $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
        } elseif (!empty($filters['dateE'])) {
          $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
        }
      }

      if (!empty($filters['parent']) && !empty($filters['user']) && empty($filters['seller'])) {
        $data->where(function ($query) use ($filters) {
          $query->whereIn('islim_users.email', $filters['parent'])
            ->orWhere('islim_users.email', $filters['user']);
        });
      }
    }

    return $data;
  }

  /**
   * Metodo para obtener reporte de de ventas no conciliadas con el coordinador
   * @param Array $filters
   *
   * @return App\Models\User
   */
  public static function getSalesNotConcReport($filters = [])
  {
    $raw3m = DB::raw("TIMESTAMPDIFF(DAY, islim_sales.date_reg, '" . date('Y-m-d H:i:s') . "') as days");

    $data = self::getConnect('R')
      ->select(
        'islim_sales.id',
        'islim_sales.unique_transaction',
        'islim_sales.msisdn',
        'islim_sales.amount',
        'islim_sales.sale_type',
        'islim_sales.is_migration',
        'islim_users.name as name_seller',
        'islim_users.last_name as last_name_seller',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_client_netweys.lat',
        'islim_client_netweys.lng',
        'islim_sales.date_reg',
        $raw3m
      )
      ->join(
        'islim_sales',
        'islim_sales.users_email',
        'islim_users.email'
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
        //['islim_users.status', 'A'],
        ['islim_sales.type', 'V'],
        ['islim_sales.status', 'E'],
        ['islim_sales.unique_transaction', 'NOT LIKE', 'ABO-%']])
      ->orderBy('islim_sales.date_reg', 'ASC');

    if (count($filters)) {
      if (!empty($filters['seller'])) {
        $data->where('islim_sales.users_email', $filters['seller']);
      }

      if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
        $data->where([
          ['islim_sales.date_reg', '>=', $filters['dateB']],
          ['islim_sales.date_reg', '<=', $filters['dateE']]]);
      } else {
        if (!empty($filters['dateB'])) {
          $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
        } elseif (!empty($filters['dateE'])) {
          $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
        }
      }

      if (!empty($filters['parent']) && !empty($filters['user']) && empty($filters['seller'])) {
        $data->where(function ($query) use ($filters) {
          $query->whereIn('islim_users.email', $filters['parent']) //parent_email
            ->orWhere('islim_users.email', $filters['user']);
        });
      }
    }

    return $data;
  }

  /**
   * Metodo para obtener reporte de de ventas
   * @param Array $filters
   *
   * @return App\Models\User
   */
  public static function getSalesReport($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_sales.id',
        'islim_sales.unique_transaction',
        'islim_sales.msisdn',
        'islim_sales.amount',
        'islim_sales.sale_type',
        'islim_sales.is_migration',
        'islim_users.name as name_seller',
        'islim_users.last_name as last_name_seller',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_client_netweys.lat',
        'islim_client_netweys.lng',
        'islim_sales.date_reg'
      )
      ->join(
        'islim_sales',
        'islim_sales.users_email',
        'islim_users.email'
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
        ['islim_users.status', 'A'],
        //['islim_sales.amount', '>', 0]
      ])
      ->where(function ($query) {
        $query //->where('islim_sales.type', 'P')
        ->orWhere('islim_sales.type', 'V');
      })
      ->whereIn('islim_sales.status', ['A', 'E'])
      ->orderBy('islim_sales.date_reg', 'DESC');

    if (count($filters)) {
      if (!empty($filters['seller'])) {
        $data->where('islim_sales.users_email', $filters['seller']);
      }

      if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
        $data->where([
          ['islim_sales.date_reg', '>=', $filters['dateB']],
          ['islim_sales.date_reg', '<=', $filters['dateE']]]);
      } else {
        if (!empty($filters['dateB'])) {
          $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
        } elseif (!empty($filters['dateE'])) {
          $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
        }
      }

      if (!empty($filters['parent']) && !empty($filters['user']) && empty($filters['seller'])) {
        $data->where(function ($query) use ($filters) {
          $query->whereIn('islim_users.email', $filters['parent']) //parent_email
            ->orWhere('islim_users.email', $filters['user']);
        });
      }
    }

    return $data;
  }

  /**
   * Metodo para obtener ventas de los usuarios
   * @param Array $filters
   *
   * @return App\Models\User
   */
  public static function getSalesByUser($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_sales.id'
      )
      ->join(
        'islim_sales',
        'islim_sales.users_email',
        'islim_users.email'
      )
      ->where([
        ['islim_users.status', 'A'],
        ['islim_sales.type', 'P']])
      ->whereIn('islim_sales.status', ['A', 'E']);

    if (count($filters)) {
      if (!empty($filters['user'])) {
        $data->where('islim_users.parent_email', $filters['user']);
      }

      if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
        $data->where([
          ['islim_sales.date_reg', '>=', $filters['dateB']],
          ['islim_sales.date_reg', '<=', $filters['dateE']]]);
      } elseif (!empty($filters['dateB'])) {
        $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
      } elseif (!empty($filters['dateE'])) {
        $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
      }
    }

    return $data;
  }
/**
 * [searchInstallerFree usuario de netwey con perfil de instalador y politica de instalacion de fibra que posea disponibilidad de horario en la zona]
 * @param  boolean $search     [palabra parte del nombre o correo del usuario a buscar]
 * @param  boolean $Notuser   [Usuarios a excluir]
 * @param  integer $fiberZone [Zona en la cual liminar la busqueda de usuario]
 * @param  boolean $installBoss [Instaladores cuyo jefe sea el indicado]
 * @param  integer $limit [Limite de resultado a devolver]
 * @return [type]              [description]
 */
  public static function searchInstallerFree($search = false, $Notuser = false, $fiberZone = false, $installBoss = false, $limit = 20)
  {

    if ($fiberZone) {

      $data = self::getConnect('R')
        ->select(
          DB::raw("CONCAT(name,' ', last_name,' - email: ',email) AS info"),
          'islim_users.name',
          'islim_users.last_name',
          'islim_users.email'
        )
        ->join(
          'islim_user_roles',
          'islim_user_roles.user_email',
          'islim_users.email'
        )
        ->join(
          'islim_profile_details',
          'islim_profile_details.user_email',
          'islim_users.email'
        )
        ->join(
          'islim_fiber_city_zone',
          'islim_fiber_city_zone.id',
          'islim_users.fiber_city_zone_id'
        );

      if ($search) {
        $data = $data->where(function ($query) use ($search) {
          $query->where('islim_users.name', $search)
            ->orWhere('islim_users.last_name', $search)
            ->orWhere('islim_users.email', $search)
            ->orWhere(DB::raw("CONCAT(name,' ', last_name,' - email: ',email)"), 'like', '%' . $search . '%');
        });
      }
      if ($installBoss) {
        $data = $data->where('islim_users.parent_email', $installBoss);
      }

      $data = $data->where([
        ['islim_fiber_city_zone.fiber_zone_id', $fiberZone],
        ['islim_user_roles.policies_id', 247],
        ['islim_user_roles.value', 1],
        ['islim_user_roles.status', 'A'],
        ['islim_profile_details.id_profile', 25],
        ['islim_profile_details.status', 'A']]);

      if (!empty($Notuser)) {
        $data = $data->whereNotIn('islim_users.email', $Notuser);
      }
      $data = $data->groupBy('islim_users.email')
        ->limit($limit);

      /*$query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      return is_numeric($binding) ? $binding : "'{$binding}'";
      })->toArray());
      Log::info($query);*/

      return $data->get();
    }
    return [];
  }

/**
 * [searchInstaller lista el instalador que tenga inventario]
 * @param  boolean $search  [description]
 * @param  boolean $pack_id [description]
 * @param  integer $limit   [description]
 * @return [type]           [description]
 */
  public static function searchInstaller($search = false, $pack_id = false, $limit = 20)
  {

    $prod = ArticlePack::getArticPackByPackId($pack_id);
    if ($prod) {
      $prod = $prod->inv_article_id;

      $data = self::getConnect('R')
        ->select(
          DB::raw("CONCAT(name,' ', last_name,' - email: ',email) AS info"),
          'islim_users.name',
          'islim_users.last_name',
          'islim_users.email'
        )
        ->join(
          'islim_user_roles',
          'islim_user_roles.user_email',
          'islim_users.email'
        )
        ->join('islim_inv_assignments', function ($join) {
          $join->on('islim_inv_assignments.users_email', '=', 'islim_users.email')
            ->where('islim_inv_assignments.status', 'A');
        })
        ->join('islim_inv_arti_details', function ($join) use ($prod) {
          $join->on('islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
            ->where('islim_inv_arti_details.status', 'A')
            ->where('islim_inv_arti_details.inv_article_id', $prod);
        })
        ->where(function ($query) use ($search) {
          $query->where('islim_users.name', $search)
            ->orWhere('islim_users.last_name', $search)
            ->orWhere('islim_users.email', $search)
            ->orWhere(DB::raw("CONCAT(name,' ', last_name,' - email: ',email)"), 'like', '%' . $search . '%');
        })
        ->where([
          ['islim_user_roles.policies_id', 247],
          ['islim_user_roles.value', 1],
          ['islim_user_roles.status', 'A']])
        ->groupBy('islim_users.email')
        ->limit($limit);

      // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      //     return is_numeric($binding) ? $binding : "'{$binding}'";
      // })->toArray());

      // Log::info($query);

      return $data->get();
    }
    return null;
  }

/**
 * [searchInstaller verifica que un lista de instaladores de fibra que estan disponibles si cuentan con el inventario para dar el alta]
 * @param  boolean $ListInstall [description]
 * @param  boolean $pack_id     [description]
 * @param  integer $limit       [description]
 * @return [type]               [description]
 */
  public static function FilterInstallerInventary($ListInstall = false, $pack_id = false, $limit = 20)
  {

    $prod = ArticlePack::getArticPackByPackId($pack_id);
    if ($prod) {
      $prod = $prod->inv_article_id;

      $data = self::getConnect('R')
        ->select(
          DB::raw('CONCAT(
            IFNULL(islim_users.name,"")," ",
            IFNULL(islim_users.last_name,"")," - Email: ",
            IFNULL(islim_users.email,"S/N")) AS info'),
          'islim_users.name',
          'islim_users.last_name',
          'islim_users.email'
        )
        ->join(
          'islim_user_roles',
          'islim_user_roles.user_email',
          'islim_users.email'
        )
        ->join('islim_inv_assignments',
          'islim_inv_assignments.users_email',
          'islim_users.email'
        )
        ->join('islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id')
        ->whereIn('islim_users.email', $ListInstall)
        ->where([
          ['islim_user_roles.policies_id', '247'],
          ['islim_user_roles.value', '1'],
          ['islim_user_roles.status', 'A'],
          ['islim_inv_assignments.status', 'A'],
          ['islim_inv_arti_details.status', 'A'],
          ['islim_inv_arti_details.inv_article_id', $prod]])
        ->groupBy('islim_users.email')
        ->limit($limit);

      /* $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      return is_numeric($binding) ? $binding : "'{$binding}'";
      })->toArray());
      Log::info($query);*/

      return $data->get();
    }
    return null;
  }

  public static function isLocked($user)
  {
    return self::getConnect('R')
      ->where([
        ['email', $user],
        ['is_locked', 'Y']])
      ->count();
  }

  public static function setResetSession($user, $status)
  {
    return self::getConnect('W')
      ->where('email', $user)
      ->update(['reset_session' => $status]);
  }

/**
 * [getInstallerZone Obtiene la lista de usuarios con perfil de instalador o jefe de instaladores con permiso de instalar fibra]
 * @param  [type] $zone_id [zona de fibra]
 * @return [type]          [description]
 */
  public static function getInstallerZone($zone_id)
  {
    return self::getConnect('R')
      ->join(
        'islim_fiber_city_zone',
        'islim_fiber_city_zone.id',
        'islim_users.fiber_city_zone_id'
      )
      ->join(
        'islim_user_roles',
        'islim_user_roles.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->where([
        ['islim_fiber_city_zone.status', 'A'],
        ['islim_fiber_city_zone.fiber_zone_id', $zone_id],
        ['islim_users.status', 'A'],
        ['islim_user_roles.policies_id', 247],
        ['islim_user_roles.value', 1],
        ['islim_user_roles.status', 'A'],
        ['islim_profile_details.status', 'A']])
      ->whereIn('islim_profile_details.id_profile', [24, 25])
      ->get();
  }

/**
 * [setLastConection Detalles de la ultima conexion]
 * @param [type] $user [description]
 * @param [type] $id   [description]
 */
  public static function setLastConection($user, $id)
  {
    return self::getConnect('W')
      ->where('email', $user)
      ->update(['last_conection_id' => $id]);
  }

/**
 * [existPhoneUser Busca dentro de los usuarios quien tiene registrado el telefono]
 * @param   $phone [telefono a buscar]
 * @param   $seller [Correo del vendedor a buscar]
 * @return [type]         [description]
 */
  public static function existPhoneUser($phone, $seller = false)
  {
    $dataUser = self::getConnect('R')
      ->where([
        ['phone', $phone],
        ['status', '!=', 'T']]);

    if ($seller) {
      $dataUser = $dataUser
        ->where('email', '!=', $seller);
    }
    return $dataUser->first();
  }
}
