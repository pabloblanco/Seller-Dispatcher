<?php

namespace App\Models;

use App\Utilities\Common;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
  use HasFactory;

  protected $table = 'islim_clients';

  protected $fillable = [
    'dni',
    'user_mail',
    'reg_email',
    'name',
    'last_name',
    'address',
    'birthday',
    'email',
    'phone_home',
    'phone',
    'social',
    'campaign',
    'date_reg',
    'note',
    'contact_date',
    'cancel_suscription',
    'code_curp',
    'verify_phone_id'];

  protected $primaryKey = 'dni';

  public $incrementing = false;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Client
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Client;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /**
   * Metodo para consultar clientes o prospectos que coincidan con el valor de $search
   *
   * @param String $search
   * @param Integer $limit
   *
   * @return App\Models\Client
   */
  public static function searchClients($search = false, $limit = 20)
  {
    if ($search) {
      $nameClientAs = DB::raw('CONCAT(
        IFNULL(islim_clients.name,"")," ",
        IFNULL(islim_clients.last_name,"")," - Teléfono: ",
        IFNULL(islim_clients.phone_home,"S/N")) AS info');

      $infoClient = DB::raw('CONCAT(
        IFNULL(islim_clients.name,"")," ",
        IFNULL(islim_clients.last_name,"")," ",
        IFNULL(islim_clients.phone_home,""))');

      return self::getConnect('R')
        ->select(
          $nameClientAs,
          'islim_clients.dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.email',
          'islim_clients.address',
          'islim_clients.phone_home',
          'islim_clients.phone',
          'islim_client_netweys.msisdn'
        )
        ->leftJoin(
          'islim_client_netweys',
          'islim_client_netweys.clients_dni',
          'islim_clients.dni'
        )
        ->where(function ($query) use ($search, $infoClient) {
          $query->where('islim_clients.phone_home', $search)
            ->orWhere('islim_clients.phone', $search)
            ->orWhere('islim_client_netweys.msisdn', $search)
            ->orWhere($infoClient, 'like', '%' . $search . '%');
        })
        ->groupBy('islim_clients.dni')
        ->limit($limit)
        ->get();
    }
    return null;
  }

  /**
   * Metodo para consultar cliente o prospecto dado su dni
   *
   * @param String $dni
   *
   * @return App\Models\Client
   */
  public static function getClientByDNI($dni = false)
  {
    if ($dni) {
      return self::getConnect('R')
        ->select(
          'islim_clients.dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.email',
          'islim_clients.address',
          'islim_clients.phone_home',
          'islim_clients.phone',
          'islim_client_netweys.msisdn',
          'islim_clients.code_curp',
          'islim_clients.verify_phone_id'
        )
        ->leftJoin(
          'islim_client_netweys',
          'islim_client_netweys.clients_dni',
          'islim_clients.dni'
        )
        ->where('islim_clients.dni', $dni)
        ->first();

    }

    return null;
  }

  /**
   * Metodo para consultar cliente dado su msisdn
   *
   * @param String $msisdn
   *
   * @return App\Models\Client
   */
  public static function getClientByMSISDN($msisdn = false, $type = false)
  {
    if ($msisdn) {
      $data = self::getConnect('R')
        ->select(
          'islim_clients.dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.email',
          'islim_clients.phone_home',
          'islim_clients.phone',
          'islim_clients.address',
          'islim_client_netweys.msisdn',
          'islim_client_netweys.lat',
          'islim_client_netweys.lng',
          'islim_client_netweys.address',
          'islim_client_netweys.status'
        )
        ->leftJoin(
          'islim_client_netweys',
          'islim_client_netweys.clients_dni',
          'islim_clients.dni'
        )
        ->where('islim_client_netweys.msisdn', $msisdn);

      if ($type) {
        $data->where('islim_client_netweys.dn_type', $type);
      }

      return $data->first();
    }

    return null;
  }

  /**
   * Metodo para consultar cliente dado su dn
   *
   * @param String $dn
   *
   * @return App\Models\Client
   */
  public static function getClient($dn = false)
  {
    if ($dn) {
      return self::getConnect('R')
        ->select(
          'islim_clients.dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.email',
          'islim_clients.phone_home',
          'islim_clients.phone',
          'islim_client_netweys.msisdn'
        )
        ->leftJoin(
          'islim_client_netweys',
          'islim_client_netweys.clients_dni',
          'islim_clients.dni'
        )
        ->where(function ($query) use ($dn) {
          $query->where('islim_clients.phone_home', $dn)
            ->orWhere('islim_clients.phone', $dn)
            ->orWhere('islim_client_netweys.msisdn', $dn);
        })
        ->groupBy('islim_clients.dni')
        ->first();

    }

    return null;
  }

  /**
   * Metodo para consultar cliente dado su dni, telefono o email
   *
   * @param String $ine
   * @param String $phone
   * @param String $email
   *
   * @return App\Models\Client
   */
  public static function getClientINEorDN($ine = false, $phone = false, $email = false)
  {
    if ($ine || $phone) {
      $data = self::getConnect('R')
        ->select(
          'islim_clients.dni',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.email',
          'islim_clients.phone_home',
          'islim_clients.phone',
          'islim_clients.address',
          'islim_clients.note',
          'islim_clients.birthday',
          'islim_clients.social',
          'islim_clients.contact_date'
        );

      if ($phone && !$ine) {
        $data->where('islim_clients.phone_home', $phone);
      }

      if ($ine && !$phone) {
        $data->where('islim_clients.dni', $ine);
      }

      if ($ine && $phone && !$email) {
        $data->where(function ($q) use ($ine, $phone) {
          $q->where('islim_clients.dni', $ine)
            ->orWhere('islim_clients.phone_home', $phone);
        });
      }

      if ($ine && !$phone && $email) {
        $data->where(function ($q) use ($ine, $email) {
          $q->where('islim_clients.dni', $ine)
            ->orWhere('islim_clients.email', $email);
        });
      }

      if ($ine && $phone && $email) {
        $data->where(function ($q) use ($ine, $phone, $email) {
          $q->where('islim_clients.dni', $ine)
            ->orWhere('islim_clients.phone_home', $phone)
            ->orWhere('islim_clients.email', $email);
        });
      }

      return $data->first();
    }

    return null;
  }

  /**
   * Metodo para consultar listado de clientes o prospectos que se filtran segun $filters
   *
   * @param Array $filters
   *
   * @return App\Models\Client
   */
  public static function getClientByfilter($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_clients.dni',
        'islim_clients.user_mail',
        'islim_clients.reg_email',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.address',
        'islim_clients.email',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_clients.date_reg',
        'islim_clients.note',
        'islim_clients.contact_date',
        'islim_users.name as vname',
        'islim_users.last_name as vlast_name'
      )
      ->leftJoin(
        'islim_users',
        'islim_users.email',
        'islim_clients.reg_email'
      )
      ->whereNotIn('islim_clients.dni', function ($query) {
        $query->from('islim_client_netweys')
          ->select('clients_dni')
          ->where('islim_client_netweys.status', 'A');
      });

    if (!empty($filters['parents']) && count($filters['parents']) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->whereIn('islim_clients.user_mail', $filters['parents'])
          ->orWhere('islim_clients.reg_email', $filters['me']);
        //->orWhere('islim_clients.user_mail', $filters['me']);//OJO con esta linea
      });
    }

    if ((empty($filters['parents']) || !count($filters['parents'])) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.user_mail', $filters['me'])
          ->orWhere('islim_clients.reg_email', $filters['me']);
      });
    }

    if (!empty($filters['name'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.name', 'like', '%' . $filters['name'] . '%')
          ->orWhere('islim_clients.last_name', 'like', '%' . $filters['name'] . '%')
          ->orWhere('islim_clients.phone_home', 'like', '%' . $filters['name'] . '%');
      });
    }

    if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
      $data->whereBetween('islim_clients.date_reg', [$filters['dateB'], $filters['dateE']]);
    }

    if (!empty($filters['seller'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.user_mail', $filters['seller'])
          ->orWhere('islim_clients.reg_email', $filters['seller']);
      });
    }

    return $data->orderBy('islim_clients.date_reg', 'DESC');
  }

  /**
   * Metodo para consultar listado de clientes que se filtran segun $filters,
   * se puede obtner campos especificos colocandolos en el array $fields
   *
   * @param Array $filters
   * @param Array $fields
   *
   * @return App\Models\Client
   */
  public static function getClientNetweyByfilter($filters = [], $fields = [])
  {
    if (!count($fields)) {
      $fields = [
        'islim_clients.dni',
        'islim_clients.user_mail',
        'islim_clients.reg_email',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.address',
        'islim_clients.email',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_clients.date_reg',
        'islim_clients.note',
        'islim_clients.contact_date',
        'islim_users.name as vname',
        'islim_users.last_name as vlast_name',
        'islim_client_netweys.msisdn',
      ];
    }

    $data = self::getConnect('R')
      ->select($fields)
      ->leftJoin(
        'islim_users',
        'islim_users.email',
        'islim_clients.reg_email'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.clients_dni',
        'islim_clients.dni'
      )
      ->WhereIn('islim_clients.dni', function ($query) {
        $query->from('islim_client_netweys')
          ->select('clients_dni')
          ->where('status', 'A');
      });

    if (!empty($filters['parents']) && count($filters['parents']) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->whereIn('islim_clients.user_mail', $filters['parents'])
          ->orWhere('islim_clients.reg_email', $filters['me']);
        //->orWhere('islim_clients.user_mail', $filters['me']);//OJO con esta linea
      });
    }

    if ((empty($filters['parents']) || !count($filters['parents'])) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.user_mail', $filters['me'])
          ->orWhere('islim_clients.reg_email', $filters['me']);
      });
    }

    if (!empty($filters['name'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.name', 'like', '%' . $filters['name'] . '%')
          ->orWhere('islim_clients.last_name', 'like', '%' . $filters['name'] . '%')
          ->orWhere('islim_clients.phone_home', 'like', '%' . $filters['name'] . '%');
      });
    }

    if (!empty($filters['dateB']) && !empty($filters['dateE'])) {
      $data->whereBetween('islim_clients.date_reg', [$filters['dateB'], $filters['dateE']]);
    }

    if (!empty($filters['seller'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('islim_clients.user_mail', $filters['seller'])
          ->orWhere('islim_clients.reg_email', $filters['seller']);
      });
    }

    return $data->orderBy('islim_clients.date_reg', 'DESC');
  }

  /**
   * Metodo para consultar prospecto o cliente dado un dni y el telefono
   * o dado un dni telefono y correo el resultado será donde coincida el telefono
   * o el email pero que el dni sea diferente al que se envía
   *
   * @param Array $phone
   * @param Array $dni
   * @param Array $email
   *
   * @return App\Models\Client
   */
  public static function getProspectByPhone($phone, $dni, $email = false)
  {
    $data = self::getConnect('R')
      ->select('dni')
      ->where([
        ['phone_home', $phone],
        ['dni', '!=', $dni]]);

    if ($email) {
      $data->orWhere([
        ['email', $email],
        ['dni', '!=', $dni]]);
    }

    return $data->first();
  }

  /**
   * Metodo para consultar prospecto o cliente dado un dni y otros filtros(opcionales)
   *
   * @param Array $filter
   *
   * @return App\Models\Client
   */
  public static function getClientByuser($filter = [])
  {
    $data = self::getConnect('R')
      ->select(
        'name',
        'last_name',
        'address',
        'phone_home',
        'user_mail'
      )
      ->where('dni', $filter['dni']);

    if (!empty($filters['parents']) && count($filters['parents']) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->whereIn('user_mail', $filters['parents'])
          ->orWhere('reg_email', $filters['me']);
      });
    }

    if ((empty($filters['parents']) || !count($filters['parents'])) && !empty($filters['me'])) {
      $data->where(function ($query) use ($filters) {
        $query->orWhere('user_mail', $filters['me'])
          ->orWhere('reg_email', $filters['me']);
      });
    }

    return $data->first();
  }

/**
 * [getcancel_suscription Consulta en la tabla de clientes el campo cancel uscribe el cual si es Y indica que el usuario dijo que no quiere que le envien emails]
 * @param  [type] $mail [correo del cliente]
 * @return [type]       [true si no desea recibir email y falso si desea recibir]
 */
  public static function getcancel_suscription($mail)
  {
    $data = self::getConnect('R')
      ->select(
        'cancel_suscription'
      )
      ->where('email', $mail)
      ->first();

    if (!empty($data)) {
      if ($data->cancel_suscription == 'Y') {
        return true;
      }
    }
    return false;
  }

/**
 * [updateInfoContact Se registra los datos de contacto para el financimiento de telmovPay]
 * @param  boolean $ine   [description]
 * @param  boolean $curp  [description]
 * @param  boolean $phone [description]
 * @param  boolean $email [description]
 * @return [type]         [description]
 */
  public static function updateInfoContact($ine = false, $curp = false, $phone = false, $email = false)
  {
    if ($ine && $email) {

      //Verifico que el correo no lo tenga otro usuario

      $data = self::getConnect('R')
        ->select('dni')
        ->where('dni', '!=', $ine)
        ->whereRaw('LOWER(email) = "' . strtolower($email) . '"')
        ->first();

      if (empty($data)) {
        try {
          $insr = self::getConnect('W')
            ->where('dni', $ine)
            ->first();

          $insr->email = strtolower(Common::normaliza($email));

          if ($curp) {
            $insr->code_curp = strtoupper((String) $curp);
          }
          if ($phone) {
            $insr->phone_home = $phone;
          }
          $insr->save();
          sleep(1);

        } catch (Exception $e) {
          $txmsg = 'Se presento un problema al actualizar los datos del cliente (MC578) ' . (String) json_encode($e->getMessage());
          Log::error($txmsg);
          return array('success' => false, 'error' => true, 'msg' => $txmsg);
        }

        if ($insr) {
          return array('success' => true, 'error' => false, 'msg' => 'Datos de contacto registrados correctamente');
        }
        return array('success' => true, 'error' => true, 'msg' => 'Datos de contacto no fue necesario actualizarlos');
      }
      return array('success' => false, 'error' => true, 'msg' => 'Correo usado por otro cliente');
    }
    return array('success' => false, 'error' => true, 'msg' => 'Faltan información para insertar los nuevos datos');
  }

/**
 * [updatePhoneContact Registra el telefono verificado y el registro con el que se verifico dicho telefono]
 * @param  [type]  $clientDni    [description]
 * @param  [type]  $phone        [description]
 * @param  boolean $verify_phone [description]
 * @return [type]                [description]
 */
  public static function updatePhoneContact($clientDni, $phone, $verify_phone = null)
  {
    try {
      $exist = self::getConnect('R')
        ->where([
          ['phone_home', $phone],
          ['dni', '!=', $clientDni]])
        ->first();

      if (empty($exist)) {
        //No hay otra persona con el telefono
        self::getConnect('W')
          ->where('dni', $clientDni)
          ->update([
            'phone_home' => $phone,
            'verify_phone_id' => $verify_phone]);

        return array('success' => true, 'title' => "Datos actualizados", 'msg' => "Registro de telefono principal realizado exitosamente", 'icon' => "success", 'code' => "OK");
      }
      return array('success' => false, 'title' => "Datos no actualizados", 'msg' => "El telefono " . $phone . " ya existe en sistema, verifique por favor el telefono suministrado", 'icon' => "error", 'code' => "PHONE_EXIST");

    } catch (Exception $e) {
      $txmsg = 'Se presento un problema al actualizar los datos del cliente (MC618) ' . (String) json_encode($e->getMessage());
      Log::error($txmsg);
      return array('success' => false, 'title' => "Datos no actualizados", 'msg' => $txmsg, 'icon' => "error", 'code' => "FAIL_DB");
    }
  }
}
