<?php

namespace App\Utilities;

use App\Mail\mailMifi;
use App\Mail\mailMifiHuella;
use App\Mail\mailMigracion;
use App\Mail\mailSuperSim;
use App\Mail\mailWelcome;
use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\DNMigration;
use App\Models\GiftService;
use App\Models\IdentityVerification;
use App\Models\infoDevice;
use App\Models\Installations;
use App\Models\InstallationsBundle;
use App\Models\Inventory;
use App\Models\InvRecicle;
use App\Models\Migration;
use App\Models\Organization;
use App\Models\Paguitos;
use App\Models\Periodicities;
use App\Models\Portability;
use App\Models\Sale;
use App\Models\SellerInventory;
use App\Models\ServicesProm;
use App\Models\TelmovPay;
use App\Models\User;
use App\Utilities\Altan;
use App\Utilities\Google;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/*
Clase que contiene metodos relacionados con las altas.
 */

class ProcessRegAlt
{
  public static function doProcessRegAlt(
    $typeReg, /*1*/
    $msisdn, /*2*/
    $addrs = false, /*3*/
    $lat = false, /*4*/
    $lng = false, /*5*/
    $service, /*6*/
    $artiDetail, /*7*/
    $typeBuyP, /*8*/
    $unique, /*9*/
    $client, /*10*/
    $plan, /*11*/
    $isPort, /*12*/
    $nip = false, /*13*/
    $dnPort = false, /*14*/
    $companyPort = false, /*15*/
    $urlDniB = false, /*16*/
    $urlDniF = false, /*17*/
    $imei = false, /*18*/
    $saleTo = false, /*19*/
    $isBandTE = false, /*20*/
    $isMigration = false, /*21*/
    $payCoppel = false, /*22*/
    $typePaymentF = false, /*23*/
    $referred = false, /*24*/
    $childrenBundle_id = false/*25*/) {

    if ($childrenBundle_id) {
      $isBundle = true;
      $infoChildren = InstallationsBundle::getChildren($childrenBundle_id);
      if (!empty($infoChildren)) {
        $infoinstall = Installations::getDateDetailByID($infoChildren->installations_id);
        if (!empty($infoinstall)) {
          $SESSION_MAIL = $infoinstall->installer;
          $SESSION_MAIL_SELLER = $infoinstall->seller;

          $user = User::getUser($infoinstall->installer, 'R', ['A', 'D']);

          //$childrenBundleId = ClientNetwey::getRegisterBundle($infoinstall->msisdn_parent);
          $childrenBundleId = $infoinstall->client_bundle_id;

          if (empty($childrenBundleId)) {
            Log::error('El registro de asociacion del cliente bundle esta vacio para el DN de fibra ' . $infoinstall->msisdn);
          }

          if (!empty($user)) {
            $SESSION_USR_TYPE = $user->platform;

            $org = false;
            if (!empty($user->id_org)) {
              $userOrg = Organization::getOrg($user->id_org);
              if (!empty($userOrg)) {
                $org = $userOrg->type;
              }
            }
            $SESSION_ORG_TYPE = $org;
            if ($typeReg == 'mov' || $typeReg == 'mov-ph') {

              $infoImei = json_decode(json_encode($infoChildren->info_imei));

              $res = Altan::validIMEI($infoImei->imei);
              if ($res['success']) {
                $SESSION_DEVICE = $res['data'];
              } else {
                return ['success' => false, 'message' => 'No se pudo verificar el imei del equipo de telefonia.'];
              }
            }
          } else {
            return ['success' => false, 'message' => 'No se pudo obtener datos del usuario.'];
          }
        } else {
          return ['success' => false, 'message' => 'No se pudo obtener detalles de la instalacion bundle.'];
        }
      } else {
        return ['success' => false, 'message' => 'No se pudo obtener detalles del registro hijo bundle.'];
      }
    } else {
      $isBundle = false;
      $SESSION_MAIL = session('user');
      $SESSION_MAIL_SELLER = session('user');
      $SESSION_ORG_TYPE = session('org_type');
      $SESSION_USR_TYPE = session('user_type');
      $SESSION_DEVICE = session('device');
      $childrenBundleId = null;
    }

    $response = ['success' => false, 'message' => 'Falló conexión con altan.'];
    $environment = env('APP_ENV', 'local');
    $doRegAltan = env('DO_REG_ALTAN', true);
    $resAlt = false;

    $payTelmov = ($typePaymentF == 'telmovpay') ? true : false;

    //Verificando que se haya validado la identidad si el pack lo requiere
    if ($plan->valid_identity == 'Y') {
      $isVeri = IdentityVerification::getSuccesVerification($msisdn, $client->dni);

      if (empty($isVeri)) {
        $response['message'] = 'Debes verificar la identidad del cliente para poder darlo de alta.';
        return response()->json($response);
      }
    }

    if ($environment != 'local' && $doRegAltan) {
      $resAlt = false;
      if ($typeReg == 'home') {
        $data = [
          'lat' => $lat,
          'lng' => $lng,
          'offer' => $service->codeAltan,
        ];

        $resArr = Altan::activation2($data, $msisdn);
      } elseif ($typeReg == 'mov' || $typeReg == 'mov-ph' || $typeReg == 'mifi' || $typeReg == 'mifi-h') {
        $data = [
          'offer' => $service->codeAltan,
        ];

        $resArr = Altan::activation2($data, $msisdn);
      }

      if ($resArr['success']) {
        $resAlt = $resArr['order_id'];
      } else {
        $response['messageAltan'] = $resArr['message'];
      }
    } else {
      $resAlt = 123456;
    }

    if ($resAlt) {
      $resTelmov = null;
      if (!$payTelmov) {
        $amount = $plan->price_pack + $plan->price_serv;
      } else {
        //Debo revisar el enganche de telmovpay
        $resTelmov = TelmovPay::inProcess($SESSION_MAIL_SELLER, ['CF'], $client->dni);
        if (!empty($resTelmov)) {
          $amount = $resTelmov->initial_amount;
        } else {
          $message = 'No se pudo obtener el monto del enganche de TelmovPay.';
          return ['success' => false, 'message' => $message];
        }
      }
      //Marcando artículo como vendido
      Inventory::markArticleSale($artiDetail->id);

      //Limpiando asignaciones del articulo
      SellerInventory::cleanAssign($artiDetail->id, $SESSION_MAIL);

      //Marcando como vendida la asignacion del articulo
      SellerInventory::markSale($artiDetail->id, $SESSION_MAIL, $SESSION_ORG_TYPE);

      $periodicity = Periodicities::getPeriodicity($service->periodicity_id);

      $date = date("Y-m-d H:i:s");

      //Tipos de altas (Productos)
      $typeProduct = [
        'mov' => 'T',
        'mov-ph' => 'T',
        'home' => 'H',
        'mifi' => 'M',
        'mifi-h' => 'MH'];

      //Calculando fecha de expiración del servicio comprado
      $dateExp = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
      $dateExp = $dateExp->addDays((int) $periodicity->days + 1)->format('Y-m-d');

      //Calculando fecha en que entaria en churn o decay
      $dateCD30 = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
      $dateCD30 = $dateCD30->addDays((int) $periodicity->days + 29)->format('Y-m-d');
      $dateCD90 = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
      $dateCD90 = $dateCD90->addDays((int) $periodicity->days + 89)->format('Y-m-d');

      //Creando cliente
      $dataClient = [
        'msisdn' => $msisdn,
        'clients_dni' => $client->dni,
        'service_id' => $service->id,
        'type_buy' => $typeBuyP,
        'periodicity' => !empty($periodicity) ? $periodicity->periodicity : '',
        'num_dues' => 0,
        'paid_fees' => 0,
        'unique_transaction' => $unique,
        'date_buy' => $date,
        'date_reg' => $date,
        'dn_type' => $typeProduct[$typeReg],
        'type_client' => ($saleTo && $saleTo == 'A') ? 'A' : 'C',
        'status' => 'A',
        'is_band_twenty_eight' => !empty($isBandTE) ? $isBandTE : 'Y',
        'date_expire' => $dateExp,
        'date_cd30' => $dateCD30,
        'date_cd90' => $dateCD90,
        'type_cd90' => 'D',
        'id_identity_verification' => !empty($isVeri) ? $isVeri->id : null,
        'telmovpay_id' => ($payTelmov && !empty($resTelmov)) ? $resTelmov->id : null,
        'origin_active' => 'SELLER',
        'client_netweys_bundle_id' => $childrenBundleId];

      //Quitar esta condición cuando acabe la promo de 3 meses para los mifi huella altan
      if ($typeReg == 'mifi-h') {
        $dataClient['id_list_dns'] = 18;
      }

      if ($typeReg == 'home' || $typeReg == 'mifi-h') {
        $dataClient['lat'] = $lat;
        $dataClient['lng'] = $lng;
        $dataClient['point'] = DB::raw("(GeomFromText('POINT(" . $lat . " " . $lng . ")'))");

        if ($typeReg == 'home') {
          $dataClient['serviceability'] = $service->broadband;
        }
      }

      if ($typeReg == 'mov-ph') {
        $dataClient['referred_dn'] = !empty($referred) ? $referred : null;
      }

      if ($typeBuyP == 'CR') {
        $dataClient['price_remaining'] = $plan->total_amount;
        $dataClient['total_debt'] = $plan->total_amount;
        $dataClient['credit'] = 'A';
      }

      ClientNetwey::getConnect('W')->insert($dataClient);

      if ($typeReg == 'mov-ph') {
        if (!empty($referred)) {
          DB::table('islim_sms_notifications')
            ->insert([
              'msisdn' => $referred,
              'sms_type' => 'G',
              'service' => 0,
              'concentrator_id' => 1,
              'sms_attribute' => '¡Gracias por referirnos a un nuevo cliente de telefonia! Has ganado un mes de recarga que aplicaremos al concluir tu ultimo periodo pagado',
              'sms' => '¡Gracias por referirnos a un nuevo cliente de telefonia! Has ganado un mes de recarga que aplicaremos al concluir tu ultimo periodo pagado',
              'date_reg' => date('Y-m-d H:i:s')]
            );
        }
      }

      $statusSale = 'E';

      if (($SESSION_USR_TYPE != 'vendor' || $SESSION_ORG_TYPE == 'R') || $amount == 0 || $payCoppel) {
        $statusSale = 'A';
      }

      //Buscando si la venta fue hecha cuando el supervisor estaba bloqueado
      $isLocked = 'N';
      if ($SESSION_USR_TYPE == 'vendor') {
        $parent = User::getParentUser($SESSION_MAIL);

        if (!empty($parent)) {
          $isLocked = User::isLocked($parent->parent_email) ? 'Y' : 'N';
        }
      }

      $methodF = 'CONTADO';
      if ($typePaymentF) {
        if ($typePaymentF == 'paguitos' ||
          $typePaymentF == 'payjoy' ||
          $typePaymentF == 'coppel' ||
          $typePaymentF == 'telmovpay') {

          $methodF = strtoupper($typePaymentF);
        }
      }

      $dataSale = [
        'services_id' => $service->id,
        'inv_arti_details_id' => $artiDetail->id,
        'concentrators_id' => 1,
        'api_key' => env('API_KEY_ALTAM'),
        'users_email' => $SESSION_MAIL_SELLER,
        'packs_id' => $plan->id,
        'unique_transaction' => $unique,
        'codeAltan' => $service->codeAltan,
        'type' => 'V',
        'id_point' => 'VENDOR',
        'description' => 'ARTICULO',
        'amount' => (!$childrenBundle_id) ? $amount : 0,
        'amount_net' => (!$childrenBundle_id) ? ($amount / env('TAX')) : 0,
        'com_amount' => 0,
        'msisdn' => $msisdn,
        'date_reg' => $date,
        'status' => $statusSale,
        'sale_type' => $typeProduct[$typeReg],
        'from' => 'S',
        'is_migration' => $isMigration ? 'Y' : 'N',
        'conciliation' => ($amount == 0 || $payCoppel) ? 'Y' : 'N',
        'user_locked' => $isLocked,
        'typePayment' => $methodF];

      $idSaleV = Sale::getConnect('W')->insertGetId($dataSale);

      //Creando registro en tabla de paguitos si el pack esta asociado a paguitos
      if ($plan->is_visible_paguitos == 'Y' &&
        $typePaymentF == 'paguitos') {
        $paguitos = Paguitos::getConnect('W');
        $paguitos->dni = $client->dni;
        $paguitos->msisdn = $msisdn;
        $paguitos->total_amount = $amount;
        $paguitos->sale_id = $idSaleV;
        $paguitos->status = 'A';
        $paguitos->date_reg = $date;
        $paguitos->save();
      } else {

        //Actualizo registro en tabla de telmovPay si el pack esta asociado a telmovPay
        if ($plan->is_visible_telmovPay == 'Y' &&
          $typePaymentF == 'telmovpay') {

          $Regtelmovpay = TelmovPay::getConnect('W')
            ->where([
              ['seller_mail', $SESSION_MAIL_SELLER],
              ['dni', $client->dni]])
            ->whereIn('status', ['CF'])
            ->update([
              'sale_id' => $idSaleV,
              'status' => 'A']);
        }
      }
      //En caso de que sea un coordinador el que hace la venta se ejecuta el flujo de recepción de dinero automaticamente
      if (($SESSION_USR_TYPE != 'vendor' || $SESSION_ORG_TYPE == 'R') || $amount == 0 || $payCoppel || $payTelmov) {
        $pemail = $SESSION_MAIL;

        if ($SESSION_ORG_TYPE == 'R') {
          $sup = User::getParentUser($SESSION_MAIL);

          $pemail = !empty($sup) ? $sup->parent_email : $SESSION_MAIL;
        }

        $dataAssigSale = array(
          'parent_email' => $pemail,
          'users_email' => $SESSION_MAIL,
          'amount' => $amount,
          'amount_text' => $amount,
          'date_reg' => $date,
          'date_accepted' => $date,
          'status' => ($amount == 0 || $payCoppel || $payTelmov) ? 'A' : 'P',
        );

        $idAssig = AssignedSales::getConnect('W')->insertGetId($dataAssigSale);

        $dataDetailAssig = array(
          'asigned_sale_id' => $idAssig,
          'amount' => $amount,
          'amount_text' => $amount,
          'unique_transaction' => $unique,
        );

        AssignedSalesDetail::getConnect('W')->insert($dataDetailAssig);
      }

      $dataSale = array(
        'services_id' => $service->id,
        'inv_arti_details_id' => $artiDetail->id,
        'concentrators_id' => 1,
        'api_key' => env('API_KEY_ALTAM'),
        'users_email' => $SESSION_MAIL,
        'packs_id' => $plan->id,
        'order_altan' => $resAlt,
        'unique_transaction' => $unique,
        'codeAltan' => $service->codeAltan,
        'type' => 'P',
        'id_point' => 'VENDOR',
        'description' => 'ALTA',
        'amount' => ($childrenBundle_id) ? $amount : 0,
        'amount_net' => ($childrenBundle_id) ? ($amount / env('TAX')) : 0,
        'com_amount' => 0,
        'msisdn' => $msisdn,
        'date_reg' => $date,
        'status' => $statusSale,
        'sale_type' => $typeProduct[$typeReg],
        'from' => 'S',
        'is_migration' => $isMigration ? 'Y' : 'N',
        'conciliation' => ($amount == 0 || $payCoppel) ? 'Y' : 'N',
        'user_locked' => $isLocked,
        'typePayment' => $methodF);

      if ($typeReg == 'home' || $typeReg == 'mifi-h') {
        $dataSale['lat'] = $lat;
        $dataSale['lng'] = $lng;
        $dataSale['position'] = DB::raw("(GeomFromText('POINT(" . $lat . " " . $lng . ")'))");
      }

      $sup = Sale::getConnect('W')->insertGetId($dataSale);

      //Verificando si la venta tiene recargas de promo asociada
      if (!empty($plan->service_prom_id)) {
        $prom = ServicesProm::getPromByID($plan->service_prom_id);

        if (!empty($prom)) {
          if ($prom->period_days) {
            $dateAct = Carbon::createFromFormat('Y-m-d H:i:s', $date)
              ->startOfDay();

            if (!empty($prom->max_time)) {
              $expired = Carbon::createFromFormat('Y-m-d H:i:s', $date)
                ->endOfDay()
                ->addMonths($prom->max_time);
            }

            for ($i = 0; $i < $prom->qty; $i++) {
              $dateAct->addDays($prom->period_days);

              GiftService::getConnect('W')
                ->insert([
                  'msisdn' => $msisdn,
                  'service_id' => $prom->service_id,
                  'activation_date' => $dateAct->format('Y-m-d H:i:s'),
                  'expired_date' => !empty($expired) ? $expired : null,
                  'date_reg' => $date,
                  'status' => 'A',
                ]);
            }
          }
        } else {
          //Reportar error al slack
          Log::error('No se consiguio la promo de servicios asignada al pack id: ' . $plan->id);
        }
      }

      //Verificando si es alta es con portabilidad para guardar información
      if ($isPort && $companyPort && $nip && $dnPort) {

        $insertData = [
          'sale_id' => $sup,
          'dn_portability' => $dnPort,
          'dn_netwey' => $msisdn,
          'company_id' => $companyPort,
          'nip' => $nip,
          'photo_front' => $urlDniF,
          'photo_back' => $urlDniB,
          'date_reg' => $date,
          'status' => 'A'];
        Portability::insertNewPortability($insertData);

        $isClient = ClientNetwey::isClient($dnPort);

        if ($isClient) {
          //Si el Dn a portar esta de cliente se envia reciclar
          InvRecicle::markToRecicle($dnPort, $SESSION_MAIL);
        }
      }

      if ($typeReg == 'mov' || $typeReg == 'mov-ph') {
        $device = $SESSION_DEVICE;

        $imei = !empty($imei) ? $imei : (!empty($artiDetail->imei) ? $artiDetail->imei : false);

        if ($imei) {
          $data = Altan::validIMEI($imei);
          if ($data['success']) {
            infoDevice::insert([
              'imei' => $imei,
              'homologated' => $data['data']['homologated'],
              'blocked' => $data['data']['blocked'],
              'volteCapable' => $data['data']['volteCapable'],
              'model' => $data['data']['model'],
              'brand' => $data['data']['brand'],
              'msisdn' => $msisdn,
              'date_reg' => $date,
              'status' => 'A',
            ]);
          } elseif (!empty($device)) {
            infoDevice::insert([
              'imei' => $imei,
              'homologated' => $device['homologated'],
              'blocked' => $device['blocked'],
              'volteCapable' => $device['volteCapable'],
              'model' => $device['model'],
              'brand' => $device['brand'],
              'msisdn' => $msisdn,
              'date_reg' => $date,
              'status' => 'A',
            ]);
          }
        }
      }

      session(['device' => null]);

      if ($environment != 'local' && $doRegAltan) {
        //envio de sms de Alta
        if ($typeReg == 'home') {
          Altan::sendSms([
            "msisdn" => $msisdn,
            "service" => $service->title,
            "pack" => $plan->id,
            "concentrator" => 1,
            "type_sms" => "A",
          ]);
        }
      }

      /*envio de email*/
      /*Envio del correo si tiene correo y si compro modem*/
      $send_mail = Client::getcancel_suscription($client->email);

      if (!$send_mail && $environment != 'local') {
        if ($typeReg == 'home' && !empty($client->email)) {

          $addressModem = Google::getAddressGoogle($lat, $lng);

          $infodata = [
            'name' => $client->name,
            'lastname' => $client->last_name,
            'dn' => $msisdn,
            'phone1' => $client->phone_home,
            'email' => $client->email,
            'Labeladdress' => '',
            'address' => ''];

          if ($addressModem['success']) {
            $infodata['Labeladdress'] = 'Tu módem fue activado en la siguiente dirección:';
            $infodata['address'] = $addressModem['data']['address'];
          }

          try {
            Mail::to($client->email)->send(new mailWelcome($infodata));
          } catch (\Exception $e) {
          }
        } else {

          $infodata = [
            'dn' => $msisdn,
            'phone1' => $client->phone_home,
            'email' => $client->email];

          if ($typeReg == 'mifi' && !empty($client->email)) {
            try {
              Mail::to($client->email)->send(new mailMifi($infodata));
            } catch (\Exception $e) {
            }
          } else {
            if ($typeReg == 'mifi-h' && !empty($client->email)) {
              try {
                Mail::to($client->email)->send(new mailMifiHuella($infodata));
              } catch (\Exception $e) {
              }
            } else {
              if ($typeReg == 'mov' && !empty($client->email)) {
                try {
                  Mail::to($client->email)->send(new mailSuperSim($infodata));
                } catch (\Exception $e) {
                }
              } else {
                if ($typeReg == 'mov-ph' && !empty($client->email)) {
                  /*No hay email para telefonos*/
                } else {
                  if ($isMigration && !empty($client->email)) {
                    try {
                      Mail::to($client->email)->send(new mailMigracion($infodata));
                    } catch (\Exception $e) {
                    }
                  }
                }
              }
            }
          }
        }
        /*end envio de email*/
      } /*Permite el cliente que se le envie emails*/
      //Migración
      if ($typeReg == 'mifi-h') {
        $dnM = $isMigration;
        //Migración ficticia
        if (!$isMigration) {
          $dnM = DNMigration::getDnAvailable();
          $dnM = !empty($dnM) ? $dnM->msisdn : false;
        }

        if ($dnM) {
          if ($environment != 'local' && $doRegAltan) {
            $resDeac = Altan::deactive($dnM);
          } else {
            $resDeac = 1234;
          }

          if (!$isMigration) {
            DNMigration::setProcess($dnM, $resDeac ? 'U' : 'E');
          }

          Migration::addMigration(
            $dnM,
            $msisdn,
            $isMigration ? 'R' : 'F',
            $resDeac ? 'P' : 'E'
          );

          ClientNetwey::setInactive($dnM);
        }
      }
      return ['success' => true];
    }
    return $response;
  }
}
