<?php
namespace App\Models;

use App\Models\Bundle;
use App\Models\InstallationsBundle;
use App\Models\Pack;
use App\Models\PackPrices;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Supports extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_support';

  protected $fillable = [
    'id',
    'ticket',
    'msisdn',
    'dni_client',
    'address',
    'lat',
    'lng',
    'tipification_support',
    'description_fail_support',
    'date_support',
    'schedule',
    'status',
    'support_father',
    'user_collector',
    'user_dispatcher',
    'date_dispatcher',
    'user_installer',
    'solution_fail',
    'date_precess',
    'typification_cancel',
    'origin_collector'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Supports
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Supports;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getPendingDatesForSupport($dispatcher, $dateS = false, $dateE = false, $typeUser = "installer")
  {
    $dates = self::getConnect('R')
      ->select(
        'islim_fiber_support.*',
        'islim_clients.name',
        'islim_clients.last_name'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_fiber_support.dni_client'
      )
      ->whereIn('islim_fiber_support.status', ['PC', 'C'])
      ->orderBy('islim_fiber_support.date_support', 'ASC')->get();

    // if ($typeUser == 'installer') {
    //   //Asignadas a alguien para ser instalado el equipo
    //   $dates = $dates->where('islim_fiber_support.user_installer', $dispatcher);
    // } elseif ($typeUser == 'ListinstallerBoss') {
    //   //Lista de instalaciones pendiente de los instaladores de un jefe dado
    //   $dates = $dates->where('islim_fiber_support.user_dispatcher', $dispatcher)
    //     ->whereNotNull('islim_fiber_support.user_installer')
    //     ->whereNotIn('islim_fiber_support.user_installer', [$dispatcher]);
    // } elseif ($typeUser == 'installerBoss') {
    //   //Lista de citas agendadas pero por asignar a un instalador
    //   $dates = $dates->where('islim_fiber_support.user_dispatcher', $dispatcher)
    //     ->whereNull('islim_fiber_support.user_installer');
    // }

    // if ($dateS && $dateE) {

    //   $dates->where([
    //     ['islim_fiber_support.date_support', '>=', $dateS],
    //     ['islim_fiber_support.date_support', '<=', $dateE]]);
    // } else {
    //   if ($dateS) {
    //     $dates->where('islim_fiber_support.date_support', '>=', $dateS);
    //   } elseif ($dateE) {
    //     $dates->where('islim_fiber_support.date_support', '<=', $dateE);
    //   }
    // }

    return $dates;
  }

/**
 * [getPendingDatesForInstaller Retorna la cantidad de citas que se enviaron el dia anterior a reprogramar por incumplimiento]
 * @param  [type] $installer [description]
 * @param  string $typeUser  [description]
 * @return [type]            [description]
 */
  public static function getSupportCaduce($dispatcher, $typeUser = "installer")
  {
    $hoy = date("Y-m-d");
    $LastDay = date("Y-m-d", strtotime($hoy . "- 1 day"));
    $data = self::getConnect('R')
      ->where([
        ['status', 'A'],
        ['date_support', $LastDay]
        ]);

    if ($typeUser == "installer") {
      //citas que se tienen asignadas como instalador
      $data = $data->where('installer', $dispatcher);
    } elseif ($typeUser == "ListinstallerBoss") {
      //citas que se tenian instalador asignado
      $data = $data->where([
        ['user_dispatcher', $dispatcher],
        ['user_installer', '!=', $dispatcher]]);

    } elseif ($typeUser == "installerBoss") {
      //Citas que se tenian en agenda pendiente de asignar
      $data = $data->whereNull('user_dispatcher')
        ->whereNull('user_installer');
    }
    return $data->count();
  }

//   public static function getDateDetailByID($id)
//   {
//     //leftJoin por el listado de instalacion por definir instalador
//     return self::getConnect('R')
//       ->select(
//         'islim_installations.clients_dni',
//         'islim_clients.name',
//         'islim_clients.last_name',
//         'islim_clients.email',
//         'islim_clients.phone_home',
//         'islim_clients.phone',
//         'islim_installations.address_instalation',
//         'islim_installations.photo',
//         DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//         'islim_installations.date_install',
//         'islim_installations.installer',
//         'islim_installations.installer_boss',
//         'islim_installations.seller',
//         'islim_installations.id',
//         'islim_installations.paid',
//         'islim_installations.status',
//         'islim_installations.inv_article_id',
//         'islim_installations.msisdn',
//         'islim_installations.price',
//         'islim_installations.route',
//         'islim_installations.house_number',
//         'islim_installations.colony',
//         'islim_installations.municipality',
//         'islim_installations.reference',
//         'islim_installations.lat',
//         'islim_installations.lng',
//         'islim_installations.schedule',
//         'islim_installations.id_fiber_zone',
//         'islim_fiber_zone.name AS name_zone',
//         'islim_installations.config_conex',
//         'islim_users.name as name_inst',
//         'islim_users.last_name as last_name_inst',
//         'islim_services.title as service',
//         'islim_services.is_payment_forcer',
//         'islim_packs.title as pack',
//         'islim_installations.pack_id',
//         'islim_installations.service_id',
//         'islim_inv_articles.title as article',
//         'state_tbl.location as state',
//         'city_tbl.location as city',
//         'islim_client_document.identification as doc_id',
//         'islim_client_document.photo_front',
//         'islim_client_document.type as doc_type',
//         'islim_installations.payment_force_end',
//         'islim_installations.pack_price_id',
//         'islim_installations.bundle_id_payment',
//         'islim_installations.payer_email',
//         'islim_installations.bundle_id',
//         'islim_installations.referred_dn',
//         'islim_installations.client_bundle_id',
//         'islim_installations.unique_transaction',
//         'islim_installations.payment_url_subscription',
//         'islim_installations.type_payment'
//       )
//       ->join(
//         'islim_clients',
//         'islim_clients.dni',
//         'islim_installations.clients_dni'
//       )
//       ->leftJoin(
//         'islim_users',
//         'islim_users.email',
//         'islim_installations.installer'
//       )
//       ->join(
//         'islim_services',
//         'islim_services.id',
//         'islim_installations.service_id'
//       )
//       ->join(
//         'islim_inv_articles',
//         'islim_inv_articles.id',
//         'islim_installations.inv_article_id'
//       )
//       ->join(
//         'islim_packs',
//         'islim_packs.id',
//         'islim_installations.pack_id'
//       )
//       ->join(
//         'islim_localy_mexico as state_tbl',
//         'state_tbl.id',
//         'islim_installations.id_state'
//       )
//       ->join(
//         'islim_fiber_city',
//         'islim_fiber_city.id',
//         'islim_installations.id_fiber_city'
//       )
//       ->join(
//         'islim_localy_mexico as city_tbl',
//         'city_tbl.id',
//         'islim_fiber_city.localy_id'
//       )
//       ->join(
//         'islim_fiber_zone',
//         'islim_fiber_zone.id',
//         'islim_installations.id_fiber_zone'
//       )
//       ->leftJoin(
//         'islim_client_document',
//         'islim_client_document.dni',
//         'islim_installations.clients_dni'
//       )
//       ->where('islim_installations.id', $id)
//       ->first();
//   }

//   public static function getInstallToEdit($id)
//   {
//     try {
//       return self::getConnect('W')
//         ->where('id', $id)
//         ->first();
//     } catch (Exception $e) {
//       $txmsg = "No se pudo leer la instalacion (MI306) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return false;
//     }
//   }

//   public static function getInstallById($id)
//   {
//     try {
//       return self::getConnect('W')
//         ->select(
//           'id',
//           'installer',
//           'seller',
//           DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//           'pack_id',
//           'service_id',
//           'status',
//           'paid',
//           'inv_article_id',
//           'price',
//           'is_migration',
//           'clients_dni',
//           'id_fiber_city',
//           'id_fiber_zone',
//           'config_conex',
//           'payment_force_start',
//           'type_payment',
//           'bundle_id',
//           'referred_dn',
//           'inv_detail_fiber_id',
//           'client_bundle_id',
//           'unique_transaction',
//           'dateProcess',
//           'payment_url_subscription',
//           'pack_price_id',
//           'bundle_id_payment'
//         )
//         ->where('id', $id)
//         ->first();
//     } catch (Exception $e) {
//       $txmsg = "No se pudo leer la instalacion (MI346) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return null;
//     }
//   }

//   public static function markAsInstalled($id, $msisdn)
//   {
//     try {
//       self::getConnect('W')
//         ->where('id', $id)
//         ->update(['status' => 'P', //'PA'
//           'date_install' => date('Y-m-d H:i:s'),
//           'msisdn' => $msisdn,
//           //,
//           //'token_activate' => null
//         ]);
//       return array('success' => true, 'msg' => 'OK');

//     } catch (Exception $e) {
//       $txmsg = "No se pudo procesar detalles de la instalacion_id " . $id . " - " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg);
//     }
//   }

//   public static function getPendingPay($seller, $filters = [])
//   {
//     $data = self::getConnect('R')
//       ->select(
//         'islim_installations.id',
//         'islim_installations.clients_dni',
//         'islim_installations.address_instalation',
//         'islim_installations.date_reg',
//         'islim_installations.date_install',
//         'islim_installations.status',
//         'islim_clients.name',
//         'islim_clients.last_name',
//         'islim_clients.phone_home'
//       )
//       ->join(
//         'islim_clients',
//         'islim_clients.dni',
//         'islim_installations.clients_dni'
//       )
//       ->where([
//         ['islim_installations.status', '<>', 'T'],
//         ['islim_installations.seller', $seller],
//         ['islim_installations.paid', 'N']]);

//     if (!empty($filters['status'])) {
//       $data->where('islim_installations.status', $filters['status']);
//     }

//     $data->orderBy('islim_installations.date_install', 'ASC');

//     return $data;
//   }

//   public static function markAsPaid($id)
//   {
//     try {
//       self::getConnect('W')
//         ->where('id', $id)
//         ->update([
//           'paid' => 'Y',
//           'date_paid' => date('Y-m-d H:i:s')]);
//       return array('success' => true, 'msg' => 'OK');

//     } catch (Exception $e) {
//       $txmsg = "No se pudo actualizar la instalacion (381) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg);
//     }
//   }

// /**
//  * [getAddressInstalation Obtiene la informacion que sera enviada en el email de bienvenida]
//  * @param  [type] $id  [description]
//  * @return [type]      [description]
//  */
//   public static function getAddressInstalation($id)
//   {
//     return self::getConnect('R')
//       ->select(
//         'islim_installations.address_instalation',
//         'islim_installations.date_reg',
//         DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//         'islim_installations.schedule',
//         'islim_installations.id_fiber_zone',
//         'islim_installations.installer_boss',
//         'islim_clients.name',
//         'islim_clients.last_name',
//         'islim_clients.email',
//         'islim_clients.phone_home',
//         'islim_installations.bundle_id'
//       )
//       ->join(
//         'islim_clients',
//         'islim_clients.dni',
//         'islim_installations.clients_dni'
//       )
//       ->where('islim_installations.id', $id)
//       ->first();
//   }

//   public static function getPendingInstalations($installer, $dateS = false, $dateE = false)
//   {
//     $dates = self::getConnect('R')
//       ->select(
//         'islim_installations.id',
//         'islim_installations.address_instalation',
//         DB::raw("DATE_FORMAT( islim_installations.date_reg, '%d-%m-%Y' ) AS date_instalation"),
//         'islim_installations.schedule',
//         'islim_installations.municipality',
//         'islim_installations.num_rescheduling',
//         'islim_clients.name',
//         'islim_clients.last_name',
//         DB::raw('TIMESTAMPDIFF(DAY, islim_installations.date_reg, NOW()) as daysElapsed'),
//         'islim_fiber_zone.name as zoneName'
//       )
//       ->join(
//         'islim_clients',
//         'islim_clients.dni',
//         'islim_installations.clients_dni'
//       )
//       ->join(
//         'islim_fiber_zone',
//         'islim_fiber_zone.id',
//         'islim_installations.id_fiber_zone'
//       )
//       ->where([
//         ['islim_installations.status', 'A'],
//         ['islim_installations.installer', $installer]]);

//     return $dates->orderBy('islim_installations.date_instalation', 'ASC')->get();
//   }

// /**
//  * [getInstallerPending retorna la cantidad de instalacion pendientes por realizar en la zona por dias]
//  * @param  [type] $fiberZone [description]
//  * @return [type]            [description]
//  */
//   public static function getInstallerPending($fiberZone)
//   {
//     //Me retona las citas que han sido confirmada por la mesa y estan en poder de algun jefe de zona y que no esta por reagendar
//     return self::getConnect('R')
//       ->select(
//         DB::raw('COUNT(date_instalation) as cant_installer'),
//         DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//         'id_fiber_zone')
//       ->where([
//         ['status', 'A'],
//         ['id_fiber_zone', $fiberZone]])
//       ->whereNotNull('installer_boss')
//       ->whereNotIn('status_control', ['C', 'SR'])
//       ->groupBy('date_instalation')
//       ->orderBy('date_instalation', 'ASC')
//       ->get();
//   }

// /**
//  * [getInstallerPendingDay Conoce la lista de instalacion dada una zona y una fecha en particular]
//  * @param  [type] $date      [description]
//  * @param  [type] $fiberZone [description]
//  * @return [type]            [description]
//  */
//   public static function getInstallerPendingDay($fiberZone, $date)
//   {
//     //Citas en cada turno que esta verificadas y que no se hallan enviado a reprogramar en el dia especifico
//     return self::getConnect('R')
//       ->select(
//         DB::raw('COUNT(schedule) as cant_installer'),
//         DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//         'schedule',
//         'id_fiber_zone')
//       ->where([
//         ['status', 'A'],
//         ['date_instalation', $date],
//         ['id_fiber_zone', $fiberZone]])
//       ->whereNotNull('installer_boss')
//       ->whereNotIn('status_control', ['C', 'SR'])
//       ->groupBy('schedule')
//       ->get();
//   }
// /**
//  * [getInstallerAsignedUser Regresa la lista de instalaciones un dia con cada instalador dado un jefe especifico o las instalaciones que se autoasigna el jefe de instaladores]
//  * @param  [type] $userInstall [description]
//  * @param  [type] $date        [description]
//  * @return [type]              [description]
//  */
//   public static function getInstallerAsignedUser($fiberZone, $date, $installerBoss = false)
//   {
//     $data = self::getConnect('R')
//       ->select(
//         DB::raw('COUNT(schedule) as cant_turno'),
//         'installer',
//         DB::raw("DATE_FORMAT( islim_installations.date_instalation, '%d-%m-%Y' ) AS date_instalation"),
//         'schedule',
//         'id_fiber_zone')
//       ->where([
//         ['status', 'A'],
//         ['date_instalation', $date],
//         ['id_fiber_zone', $fiberZone]])
//       ->whereNotIn('status_control', ['C', 'SR']);

//     if (!$installerBoss) {
//       //Instaladores donde asigno el jefe
//       $data = $data->where('installer_boss', session('user'))
//         ->whereNotNull('installer');
//     } else {
//       //Las instalacion que el jefe se auto asigno
//       $data = $data->where('installer', session('user'));
//     }
//     return $data->groupBy('installer', 'schedule')
//       ->get();
//   }

//   public static function markCancelled($dataCanceller)
//   {
//     try {
//       self::getConnect('W')
//         ->where('id', $dataCanceller->cita)
//         ->update([
//           'status_control' => 'SR',
//           'date_mod' => date('Y-m-d H:i:s'),
//           'user_mod' => session('user'),
//           'reason_delete' => !empty($dataCanceller->msgTypi) ? $dataCanceller->msgTypi : null,
//           'typification_id' => $dataCanceller->typification]);
//       return array('success' => true, 'msg' => 'OK');

//     } catch (Exception $e) {
//       $txmsg = "No se pudo modificar el status de control de la instalacion (543) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg);
//     }
//   }
// /**
//  * [markAceptTyc Registra el id del tyc asociado a la cita]
//  * @param  [type] $install_id [description]
//  * @param  [type] $tyc_id     [description]
//  * @param  string $type_tyc   [description]
//  * @return [type]             [description]
//  */
//   public static function markAceptTyc($install_id, $tyc_id, $type_tyc = "END")
//   {
//     try {
//       $regis = self::getConnect('W')
//         ->where('id', $install_id);

//       if ($type_tyc == "END") {
//         $regis = $regis->update(['payment_force_end' => $tyc_id]);
//       } else {
//         $regis = $regis->update(['payment_force_start' => $tyc_id]);
//       }
//       return array('success' => true, 'msg' => 'OK');

//     } catch (Exception $e) {
//       $txmsg = "No se pudo modificar el id del contrato de fibra de la cita (569) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg);
//     }
//   }

// /**
//  * [setChangerPackService Paquete cambiado por el instalador]
//  * @param [type] $id      [id de la cita]
//  * @param [type] $pack    [id del pack]
//  * @param [type] $service [id del servicio]
//  * @param [type] $total_price [monto pagado]
//  * @param [type] $service [description]
//  * @param [type] $delPayment [indica si se coloca los campos en null de la subscripcion]

//  */
//   public static function setChangerPackService($id, $pack, $service, $total_price, $delPayment = false, $bundle_id = false)
//   {
//     try {
//       $install_update = self::getConnect('W')
//         ->where('id', $id)
//         ->first();

//       if (!empty($install_update)) {
//         $install_update->pack_id = $pack;
//         $install_update->service_id = $service;
//         $install_update->price = $total_price;
//         $install_update->user_mod = session('user');
//         $install_update->date_mod = date('Y-m-d H:i:s');

//         if ($bundle_id) {
//           $install_update->bundle_id = $bundle_id;
//         }

//         if ($delPayment) {
//           $install_update->payment_url_subscription = null;
//           $install_update->unique_transaction = null;
//           $install_update->pack_price_id = null;
//           $install_update->bundle_id_payment = null;
//           $install_update->payer_email = null;
//         }
//         $install_update->save();
//         sleep(2);
//         return array('success' => true, 'msg' => 'OK', 'code' => 'OK');
//       }
//       return array('success' => false, 'msg' => "No se encontro la cita a ser actualizada el pack y el servicio (MI634)", 'code' => 'EMP_INS');
//     } catch (Exception $e) {
//       $txmsg = "No se pudo modificar el servicio y el paquete de la cita (MI636) " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg, 'code' => 'FAIL');
//     }
//   }

// /**
//  * [setMethodPayment Actualiza la forma como se pago la cita]
//  * @param [integer] $install_id [id de la cita de instalacion]
//  * @param [boolean] $verify [true=indica que se debe revisar el servicio si debe cambiar a uno subscrito, false=ignora el servicio]

//  * @param [type] $method     [description]
//  */
//   public static function setMethodPayment($install_id, $method, $verify = false)
//   {
//     $response = ['success' => false, 'msg' => "No se pudo actualizar la forma en que se pago, faltan datos", 'code' => 'FAIL'];

//     if ($install_id && $method) {
//       try {
//         $install = self::getConnect('W')
//           ->where('id', $install_id)
//           ->first();

//         if (!empty($install)) {
//           $install->type_payment = $method;
//           $install->paid = ($method == "CARD") ? 'Y' : 'N';
//           $install->save();
//           sleep(2);

//           if ($method == "CARD" && $verify &&
//             (!empty($install->payment_url_subscription) &&
//               (!empty($install->pack_price_id) ||
//                 !empty($install->bundle_id_payment)
//               )
//             )) {
//             //Se verifica el plan que tiene configurado la cita posee pago recurrente, como realizo el pago el cambio de plan se debe hacer

//             if (!empty($install->pack_price_id)) {
//               //Se debe cambiar al plan de pago recurrente de fibra sola
//               $packprice = PackPrices::getPackPriceDetail($install->pack_price_id);

//               if (!empty($packprice)) {
//                 $upd = self::setChangerPackService(
//                   $install_id,
//                   $packprice->pack_id,
//                   $packprice->service_id,
//                   $packprice->total_price);
//                 if (!$upd['success']) {
//                   $response = ['success' => false, 'msg' => $upd['msg'], 'code' => $upd['code']];
//                   return $response;
//                 }
//               } else {
//                 $response = ['success' => false, 'msg' => 'El detalle del paquete a cambiar no pudo se encontrado (MI698)', 'code' => 'EMP_PAK'];
//                 return $response;
//               }
//             } elseif (!empty($install->bundle_id_payment)) {
//               //Se debe cambiar al plan de pago recurrente de fibra bundle
//               //Aca se deben actualizar dos lugares, la instalacion de fibra y la instalacion_bundle donde esta el hijo del bundle

//               $infPack = Bundle::getDetailBundleAlta($install->bundle_id_payment);
//               if ($infPack['success']) {
//                 $infoAllBundle = json_decode(json_encode($infPack['data']));
//                 $update_fail = "";

//                 if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {
//                   $up_F = self::setChangerPackService(
//                     $install_id,
//                     $infoAllBundle->info_F->id,
//                     $infoAllBundle->info_F->service_id,
//                     $infoAllBundle->general->total_payment,
//                     false,
//                     $infoAllBundle->general->id
//                   );
//                   if (!$up_F['success']) {
//                     $update_fail .= $up_F['msg'];
//                   }
//                 }
//                 //Se actualizan los hijos del bundle
//                 $productList = [
//                   'T' => "Telefonia",
//                   'M' => "Mifi",
//                   'MH' => "Mifi Huella",
//                   'H' => "Hogar"];

//                 foreach ($productList as $key => $value) {
//                   $containt = "containt_" . $key;
//                   $infoArt = "info_" . $key;

//                   if ($infoAllBundle->general->$containt == 'Y' &&
//                     !empty($infoAllBundle->$infoArt)) {
//                     $UP = InstallationsBundle::updateChildrenChangerBundle(
//                       $install_id,
//                       $key,
//                       $infoAllBundle->$infoArt->id,
//                       $infoAllBundle->$infoArt->service_id
//                     );
//                     if (!$UP['success']) {
//                       $update_fail .= ' ** ' . $UP['msg'];
//                     }
//                   }
//                 }

//                 if (empty($update_fail)) {
//                   return array('success' => true, 'msg' => "OK", 'code' => 'OK');
//                 } else {
//                   return array('success' => false, 'msg' => $update_fail, 'code' => 'FAIL_BD');
//                 }
//               }
//             }
//           }
//           return array('success' => true, 'msg' => "OK", 'code' => 'OK');
//         } else {
//           $response = ['success' => false, 'msg' => "No se encuentra la cita de instalacion para actualizar", 'code' => 'EMP_INS'];
//         }
//       } catch (Exception $e) {
//         $txmsg = "No se pudo modificar la forma en que se pago la cita (MI685) " . (String) json_encode($e->getMessage());
//         Log::error($txmsg);
//         return array('success' => false, 'msg' => $txmsg, 'code' => 'FAIL');
//       }
//     }
//     return $response;
//   }

// /**
//  * [notifyChangerMP Registra cuando se informa el cambio de plan a la pasarela de pago para que programe el proximo dia que debe cobrar y que servicio recargara]
//  * @param  [type] $install_id [description]
//  * @param  [type] $status     [description]
//  * @return [type]             [description]
//  */
//   public static function notifyChangerMP($install_id, $status)
//   {
//     $response = ['success' => false, 'msg' => "No se pudo actualizar que se notifico el cambio de plan, faltan datos"];

//     if ($install_id && $status) {
//       try {
//         $install = self::getConnect('W')
//           ->where('id', $install_id)
//           ->first();

//         if (!empty($install)) {
//           $install->notify_changer_service = $status;
//           $install->save();

//           return array('success' => true, 'msg' => "OK");
//         } else {
//           $response = ['success' => false, 'msg' => "No se encuentra la cita de instalacion para actualizar"];
//         }
//       } catch (Exception $e) {
//         $txmsg = "No se pudo actualizar que se notifico el cambio de plan de la cita (MI793) " . (String) json_encode($e->getMessage());
//         Log::error($txmsg);
//         return array('success' => false, 'msg' => $txmsg);
//       }
//     }
//     return $response;
//   }

//   public static function totalProcess($install_id)
//   {
//     $master = self::getConnect('R')
//       ->find($install_id);
//     if ($master->status != 'P') {
//       return 'N';
//     }

//     $childs = InstallationsBundle::getConnect('R')
//       ->where('installations_id', $install_id)
//       ->whereIn('status', ['EC', 'E', 'PA'])
//       ->get();

//     if (count($childs) > 0) {
//       return 'N';
//     }
//     return 'Y';
//   }

//   /**
//    * [getComponent Devuelve un componente el bundle para conocer el status de la activacion]
//    * @param  [type] $installer_id [Id de installation bundle]
//    * @return [type]               [description]
//    */
//   public static function getComponent($id)
//   {
//     $component = self::getConnect('R')
//       ->select(
//         'islim_installations.id',
//         'islim_installations.status',
//         DB::raw('IFNULL(islim_installations.msisdn,islim_inv_arti_details.msisdn) as msisdn'),
//         'islim_installations.inv_detail_fiber_id',
//         DB::raw('CONCAT("F") AS dn_type'),
//         DB::raw('CONCAT("master") AS config'),
//         'islim_installations.obs_activate',
//         'islim_installations.id as installations_id'
//       )
//       ->leftJoin('islim_inv_arti_details',
//         'islim_inv_arti_details.id',
//         'islim_installations.inv_detail_fiber_id'
//       )
//       ->where([
//         ['islim_installations.status', '!=', 'T'],
//         ['islim_installations.id', $id]])
//       ->first();

//     return $component;
//   }

// /**
//  * [asigneUniqueTransaction Asignacion de codigo de transaccion y otros datos mas consernientes a una subscripcion, la unique transaction sera usado para otras operaciones como lo es el registro de venta]
//  * @param  [type] $id     [description]
//  * @param  [type] $unique [description]
//  * @return [type]         [description]
//  */
//   public static function asigneSubscriptionInfo($id,
//     $unique,
//     $url,
//     $packPrice_id,
//     $payer_email,
//     $bundle_id) {
//     try {
//       self::getConnect('W')
//         ->where('id', $id)
//         ->update([
//           'unique_transaction' => $unique,
//           'payment_url_subscription' => $url,
//           'pack_price_id' => $packPrice_id,
//           'bundle_id_payment' => $bundle_id,
//           'payer_email' => $payer_email]);
//       sleep(2);
//       return array('success' => true, 'msg' => 'OK');
//     } catch (Exception $e) {
//       $txmsg = "No se pudo asignar la informacion de la subscripcion de la instalacion_id " . $id . " - " . (String) json_encode($e->getMessage());
//       Log::error($txmsg);
//       return array('success' => false, 'msg' => $txmsg);
//     }
//   }
}
