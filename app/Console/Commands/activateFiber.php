<?php

namespace App\Console\Commands;

use App\Mail\mailWelcomeFibra;
use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\Bundle;
use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\ClientNetweyBundle;
use App\Models\FiberPaymentForce;
use App\Models\FiberZone;
use App\Models\Installations;
use App\Models\Inventory;
use App\Models\PackPrices;
use App\Models\Periodicities;
use App\Models\Sale;
use App\Models\SellerInventory;
use App\Models\Service;
use App\Models\Sms_notification;
use App\Models\User;
use App\Utilities\Api815;
use App\Utilities\ApiMIT;
use App\Utilities\Common;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class activateFiber extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:activateFiber';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command que realiza el alta asincrono de los servicios de fibra ante 815';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $dateInitialAll = microtime(true);
    $listZone = [];

    $exisActive = Installations::getConnect('R')
      ->where('status', 'EC')
      ->whereNull('msisdn')
      ->whereNull('token_activate')
      ->first();

    if (!empty($exisActive)) {
      $zones = FiberZone::getAllZone();
      $cantZone = count($zones);
      $txt = (env('APP_ENV') == 'production') ? "Productivo" : "Test";
      $this->info("<fg=white;bg=black> Existen  " . $cantZone . " configuradas en ambiente " . $txt);
      $pos = 0;
      foreach ($zones as $item) {
        if (!in_array($item->id, $listZone)) {
          $pos++;
          $dateStartZone = microtime(true);
          //Verifico que este activo el servidor
          $zone = 'Zona ' . $item->id;
          $this->info('<fg=white;bg=red>' . $pos . '. > Verificando ' . $zone);
          $statusActive = FiberZone::chekingZone($item->id);
          if ($statusActive['success']) {
            array_push($listZone, $item->id);
            $texZone = ' > ' . $zone . ' OK!';
          } else {
            $texZone = ' > ' . $zone . ' Fallo! >> ' . $statusActive['msg'];
          }
          $dateEndZone = microtime(true);
          $totalTimeZone = $dateEndZone - $dateStartZone;
          $this->info('<fg=white;bg=red> ' . $texZone . ' Tiempo: ' . $totalTimeZone);
        }
      }
    } else {
      $this->info('<fg=white;bg=red> > No hay activaciones pendientes! <');
      return 0;
    }

    $tokenGroup = "CAF-" . (String) time();

    $AsigneFiber = Installations::getConnect('W')
      ->where('status', 'EC')
      ->whereNull('token_activate')
      ->whereNull('msisdn')
      ->whereIn('id_fiber_zone', $listZone)
      ->update(['token_activate' => $tokenGroup]);
    sleep(3);
    //$this->info("Asigne Token " . (String) json_encode($AsigneFiber));

    if ($AsigneFiber) {

      $UpFiber = Installations::getConnect('W')
        ->where('token_activate', $tokenGroup)
        ->orderBy('id', 'ASC')
      // ->orderBy('cant_attempts', 'ASC')
        ->get();

      $cant = count($UpFiber);
      $this->info("Cant " . $cant);

      if ($cant) {

        $pos = 0;
        foreach ($UpFiber as &$dataIns) {

          $NumCheking = empty($dataIns->cant_attempts) ? 1 : $dataIns->cant_attempts + 1;

          $dateInitialOne = microtime(true);
          $this->info('');
          $pos++;
          $this->info('<fg=white;bg=black> Procesando ' . $pos . ' / ' . $cant . ' >Intento: ' . $NumCheking);

          $Firewall815 = Api815::statusFirewallBD($dataIns->id_fiber_zone);

          if ($Firewall815['success']) {
            //Permite instalar un servicio que se desactivo luego que se agendo la cita
            $service = Service::getService($dataIns->service_id, ['A', 'I']);
            //Permite instalar un paquete que se desactivo luego que se agendo la cita
            $fiber_service = Service::getPKService815($dataIns->service_id, $dataIns->id_fiber_zone, ['A', 'I']);

            if (!empty($fiber_service) && !empty($service)) {

              $service_pk = $fiber_service->service_pk;

              $periodicity = Periodicities::getPeriodicity($service->periodicity_id);
              $date = date("Y-m-d H:i:s");

              //Calculando fecha de expiración del servicio comprado
              $dateExp = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
              $dateExp = $dateExp->addDays((int) $periodicity->days + 1)->format('Y-m-d');

              //Calculando fecha en que entaria en churn o decay
              $dateCD30 = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
              $dateCD30 = $dateCD30->addDays((int) $periodicity->days + 29)->format('Y-m-d');
              $dateCD90 = Carbon::createFromFormat('Y-m-d H:i:s', $date)->startOfDay();
              $dateCD90 = $dateCD90->addDays((int) $periodicity->days + 89)->format('Y-m-d');

              $unique = $dataIns->unique_transaction;

              $conexFiber = null;
              if (!empty($dataIns->config_conex)) {
                //registro del nodo de conexion
                $configConex = json_decode(json_encode($dataIns->config_conex));

                if (isset($configConex->nodo_de_red)) {
                  unset($conexFiber);
                  $conexFiber = new \stdClass;
                  $conexFiber->nodo_de_red = $configConex->nodo_de_red;
                }
              }
              if (is_null($conexFiber)) {

                $infoMsg = new \stdClass;
                $infoMsg->code = "EMP_NOD";
                $infoMsg->message = 'No se puede crear el cliente en 815, no se conoce el nodo de conexion';
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;
                $dataIns->token_activate = null;
                $dataIns->save();
                $this->info("- " . $infoMsg->message);
                break;
              }

              $timeForce = null;
              $dateRemenber = null;
              $dateDefaulter = null;
              if ($service->is_payment_forcer == 'Y') {
                if (!empty($dataIns->payment_force_start)) {
                  $infoQr = FiberPaymentForce::getUrlQr($dataIns->clients_dni, 'START', false, $dataIns->payment_force_start);
                  if (!empty($infoQr) && $infoQr->status == 'A') {
                    $timeForce = 0;
                    $dateRemenber = date("Y-m-d", strtotime($dateExp . "-3 day"));
                    $dateDefaulter = date("Y-m-d", strtotime($dateExp . "+5 day"));
                  } else {
                    $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $infoQr->code_url;

                    $infoMsg = new \stdClass;
                    $infoMsg->code = "FAI_FOR";
                    $infoMsg->message = "Se debe verificar el contrato haya sido aceptado: " . $urlQr;
                    $dataIns->obs_activate = $infoMsg;
                    $dataIns->cant_attempts = $NumCheking;
                    $dataIns->token_activate = null;
                    $dataIns->save();
                    $this->info("- " . $infoMsg->message);
                    break;
                  }
                } else {
                  $infoMsg = new \stdClass;
                  $infoMsg->code = "EMP_FOR";
                  $infoMsg->message = 'El servicio a dar de alta es con contrato, pero no se puede localizar el detalle del mismo para continuar.';
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->cant_attempts = $NumCheking;
                  $dataIns->token_activate = null;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  break;
                }
              }
              if ($service->for_subscription == 'Y' && !empty($dataIns->payment_url_subscription)) {
                $subscription_id = $dataIns->unique_transaction;
              } else {
                if ($service->for_subscription == 'Y') {

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "EMP_FOR";
                  $infoMsg->message = 'El servicio de alta es con pago recurrente pero no se ubica el identificador de pago para continuar.';
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->cant_attempts = $NumCheking;
                  $dataIns->token_activate = null;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  break;
                } else {
                  $subscription_id = null;
                }
              }

              //Buscados segun el id el Dn que se asigno para el alta de fibra
              if (empty($dataIns->inv_detail_fiber_id)) {
                $infoMsg = new \stdClass;
                $infoMsg->code = "EMP_INV";
                $infoMsg->message = 'No se conoce el id del articulo de fibra que se debe dar de alta.';
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;
                $dataIns->token_activate = null;
                $dataIns->save();
                $this->info("- " . $infoMsg->message);
                break;
              }

              $InfoDN = Inventory::getDnsById($dataIns->inv_detail_fiber_id);

              if (empty($InfoDN)) {
                $infoMsg = new \stdClass;
                $infoMsg->code = "EMP_INV";
                $infoMsg->message = 'No se encontro el articulo de fibra que se debe dar de alta.';
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;
                $dataIns->token_activate = null;
                $dataIns->save();
                $this->info("- " . $infoMsg->message);
                break;
              } else {
                $msisdn = $InfoDN->msisdn;
              }

              //Creando cliente
              $dataClient = [
                'msisdn' => $msisdn,
                'clients_dni' => $dataIns->clients_dni,
                'service_id' => $dataIns->service_id,
                'type_buy' => 'CO',
                'periodicity' => !empty($periodicity) ? $periodicity->periodicity : '',
                'num_dues' => 0,
                'paid_fees' => 0,
                'unique_transaction' => $unique,
                'date_buy' => $date,
                'date_reg' => $date,
                'dn_type' => 'F',
                'type_client' => 'C',
                'status' => 'A',
                'is_band_twenty_eight' => 'N',
                'date_expire' => $dateExp,
                'date_cd30' => $dateCD30,
                'date_cd90' => $dateCD90,
                'type_cd90' => 'D',
                'id_fiber_zone' => $dataIns->id_fiber_zone,
                'conex_fiber' => json_encode($conexFiber),
                'subscription_id' => $subscription_id,
                'origin_active' => 'SELLER',
                'date_remember_payment' => $dateRemenber,
                'date_defaulter_payment' => $dateDefaulter,
                'time_in_forcer' => $timeForce,
                'referred_dn' => $dataIns->referred_dn,
                'client_netweys_bundle_id' => (!empty($dataIns->client_bundle_id)) ? $dataIns->client_bundle_id : null];

              try {
                ClientNetwey::getConnect('W')->insert($dataClient);
              } catch (Exception $e) {
                $txMsg = 'Error al insertar el cliente fibra en netwey ' . $msisdn . " (CRF305) - " . (String) json_encode($e->getMessage());
                Log::error($txMsg);

                $infoMsg = new \stdClass;
                $infoMsg->code = "BD_FAIL";
                $infoMsg->message = $txMsg;
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;
                $dataIns->token_activate = null;
                $dataIns->save();
                $this->info("- " . $infoMsg->message);
                break;
              }

              // $artic = Product::getProductById($dataIns->inv_article_id);
              //Ejecutando alta con api intermedia de 815
              $res815 = Api815::doRegistration(['msisdn' => $msisdn]);

              if ($res815['success']) {
                $this->info('<fg=white;bg=black> * Registrado en 815 ' . $msisdn);

                //Si el cliente es de un bundle se actualiza el registro de clientes bundle
                if (!empty($dataIns->client_bundle_id)) {

                  $registerBundle = ClientNetweyBundle::updateBundle($dataIns->client_bundle_id, $msisdn, $dateExp);
                  if (!$registerBundle['success']) {
                    Log::error($registerBundle['msg']);
                  }
                  $this->info('<fg=white;bg=black> * Actualizado datos del bundle');
                }
                //Marcando artículo como vendido
                $mrkInv = Inventory::markArticleSale($dataIns->inv_detail_fiber_id);
                if (!$mrkInv['success']) {

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "ERR_DB";
                  $infoMsg->message = $mrkInv['msg'];
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  //break;
                }
                $this->info('<fg=white;bg=black> * Articulo de fibra vendido');

                //Limpiando asignaciones del articulo (DEPRECADO EN ESTE CASO DE FIBRA)
                //SellerInventory::cleanAssign($artiDetail->id, session('user'));

                //Marcando como vendida la asignacion del articulo
                $mrkAs = SellerInventory::markSale($dataIns->inv_detail_fiber_id, $dataIns->installer);

                if (!$mrkAs['success']) {

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "ERR_DB";
                  $infoMsg->message = $mrkAs['msg'];
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  // break;
                }
                $this->info('<fg=white;bg=black> * Asignacion de inventario de fibra procesado');

                //obtengo configuracion de aprovisionamiento de la zona
                $zoneinfo = FiberZone::getInfoZone($dataIns->id_fiber_zone);
                $configuration = json_decode(json_encode($zoneinfo->configuration));
                $provisioning = 'N';
                if (isset($configuration->provisioning)) {
                  if (!empty($configuration->provisioning)) {
                    $provisioning = $configuration->provisioning;
                  }
                }

                //Marcando la instalación como procesada y guardo el Dn que se activo
                $Instalation = Installations::markAsInstalled($dataIns->id, $msisdn, $provisioning);

                if (!$Instalation['success']) {

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "ERR_DB";
                  $infoMsg->message = $Instalation['msg'];
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  // break;
                }
                $this->info('<fg=white;bg=black> * Instalacion de fibra marcada como procesada');

                //Preguntar si el vendedor/instalador se reemplaza por su coordinador en caso de que el usuario ya no esta activo
                $user = User::getUserByEmail($dataIns->installer);

                //Se infiere desde un comienzo que se debe el dinero
                $conciliate = ($dataIns->price == 0) ? 'Y' : 'N';
                $statusSale = 'E';
                if ($user->platform != 'vendor' || $dataIns->price == 0) {
                  $statusSale = 'A';
                  $conciliate = 'Y';
                }

                //Se infiere desde el comienzo que pago en efectivo
                $typePayment = "CONTADO";
                if (!empty($dataIns->payment_url_subscription) &&
                  $dataIns->type_payment == 'CARD') {
                  $typePayment = $dataIns->type_payment;
                  $statusSale = 'A';
                  $conciliate = 'Y';
                }

                //Consultando usuario para saber si esta activo
                // $userSeller = User::getUserByEmail($dataIns->seller);

                //Datos comunes de la venta y el alta
                $dataSale = [
                  'services_id' => $dataIns->service_id,
                  'inv_arti_details_id' => $dataIns->inv_detail_fiber_id,
                  'concentrators_id' => 1,
                  'api_key' => env('API_KEY_ALTAM'),
                  'packs_id' => $dataIns->pack_id,
                  'unique_transaction' => $dataIns->unique_transaction,
                  'codeAltan' => $service_pk,
                  'id_point' => 'VENDOR',
                  'com_amount' => 0,
                  'msisdn' => $msisdn,
                  'date_reg' => $date,
                  'status' => $statusSale,
                  'sale_type' => 'F',
                  'from' => 'S',
                  'is_migration' => $dataIns->is_migration,
                  'conciliation' => $conciliate,
                  'date_init815' => date('Y-m-d'),
                  'date_end815' => $dateExp,
                  'date_process815' => $date];

                //Creando venta tipo V(Venta)
                //NOTA: El vendedor no cobra el dinero
                $dataSale['users_email'] = $dataIns->seller;
                $dataSale['type'] = 'V';
                $dataSale['description'] = 'ARTICULO';
                $dataSale['amount'] = 0;
                $dataSale['amount_net'] = 0;

                try {
                  Sale::getConnect('W')->insert($dataSale);
                  $this->info('<fg=white;bg=black> * Procesado registro de venta');

                } catch (Exception $e) {
                  $txMsg = 'No se pudo registrar la venta tipo V para el DN ' . $msisdn . " (CRF437) - " . (String) json_encode($e->getMessage());
                  Log::error($txMsg);

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "ERR_DB";
                  $infoMsg->message = $txMsg;
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  // break;
                }

                //Creando venta tipo P(Alta)
                //NOTA: El instalador es quien cobra y se queda con el dinero en efectivo
                $dataSale['users_email'] = $dataIns->installer;
                $dataSale['type'] = 'P';
                $dataSale['typePayment'] = $typePayment;
                $dataSale['description'] = 'ALTA';
                $dataSale['amount'] = $dataIns->price;
                $dataSale['amount_net'] = ($dataIns->price / env('TAX'));
                $dataSale['order_altan'] = '0000';
                $dataSale['lat'] = $dataIns->lat;
                $dataSale['lng'] = $dataIns->lng;
                $dataSale['position'] = DB::raw("(GeomFromText('POINT(" . $dataIns->lat . " " . $dataIns->lng . ")'))");

                try {
                  Sale::getConnect('W')->insert($dataSale);
                  $this->info('<fg=white;bg=black> * Procesado registro de alta');

                } catch (Exception $e) {
                  $txMsg = 'No se pudo registrar la venta tipo P para el DN ' . $msisdn . " (CRF467) - " . (String) json_encode($e->getMessage());
                  Log::error($txMsg);

                  $infoMsg = new \stdClass;
                  $infoMsg->code = "ERR_DB";
                  $infoMsg->message = $txMsg;
                  $dataIns->obs_activate = $infoMsg;
                  $dataIns->save();
                  $this->info("- " . $infoMsg->message);
                  //break;
                }

                //Asignando entrega de efectivo en caso de que sea un coordinador el que hizo la venta
                if ($user->platform != 'vendor' || $dataIns->price == 0 && ($dataIns->type_payment == "CASH")) {
                  $dataAssigSale = array(
                    'parent_email' => $dataIns->installer,
                    'users_email' => $dataIns->installer,
                    'amount' => $dataIns->price,
                    'amount_text' => $dataIns->price,
                    'date_reg' => $date,
                    'date_accepted' => $date,
                    'status' => $dataIns->price == 0 ? 'A' : 'P',
                  );

                  try {
                    $idAssig = AssignedSales::getConnect('W')->insertGetId($dataAssigSale);
                    $this->info('<fg=white;bg=black> * Procesado registro de entrega de efectivo');

                  } catch (Exception $e) {
                    $txMsg = 'No se pudo registrar la asignacion de venta. (CRF496) ' . (String) json_encode($e->getMessage());
                    Log::error($txMsg);

                    $infoMsg = new \stdClass;
                    $infoMsg->code = "ERR_DB";
                    $infoMsg->message = $txMsg;
                    $dataIns->obs_activate = $infoMsg;
                    $dataIns->save();
                    $this->info("- " . $infoMsg->message);
                    //break;
                  }

                  $dataDetailAssig = array(
                    'asigned_sale_id' => $idAssig,
                    'amount' => $dataIns->price,
                    'amount_text' => $dataIns->price,
                    'unique_transaction' => $dataIns->unique_transaction,
                  );

                  try {
                    AssignedSalesDetail::getConnect('W')->insert($dataDetailAssig);
                    $this->info('<fg=white;bg=black> * Procesado registro de detalle de entrega de efectivo');

                  } catch (Exception $e) {
                    $txMsg = 'No se pudo registrar el detalle de asignacion de venta. (CRF520) ' . (String) json_encode($e->getMessage());
                    Log::error($txMsg);

                    $infoMsg = new \stdClass;
                    $infoMsg->code = "ERR_DB";
                    $infoMsg->message = $txMsg;
                    $dataIns->obs_activate = $infoMsg;
                    $dataIns->save();
                    $this->info("- " . $infoMsg->message);
                    //break;
                  }
                }

                //Espero 2s que se guarden los datos y su status
                $dataIns->inv_detail_fiber_id = null;
                $dataIns->save();
                $this->info('<fg=white;bg=black> * Procesado seteado de id_inv_detail fibra');
                sleep(2);

                //
                //Si se trata de un plan con PAGO RECURRENTE se debe cambiar ante MIT el servicio que quedara recargando. Ademas de notificar el DN
                //
                if (!empty($dataIns->payment_url_subscription) &&
                  !empty($dataIns->payer_email) &&
                  $dataIns->type_payment == 'CARD' &&
                  (!empty($dataIns->pack_price_id) || !empty($dataIns->bundle_id_payment))
                ) {

                  $service_recharge = null;
                  if (!empty($dataIns->pack_price_id)) {
                    $this->info('<fg=white;bg=black> * Busqueda de servicio de recarga de una susbcripcion de fibra individual');

                    $infPackPrice = PackPrices::getPackPriceDetail($dataIns->pack_price_id, ['A', 'I']);
                    if (!empty($infPackPrice)) {
                      //info del paquete de recarga
                      $infoServiceRecharge = Service::getService($infPackPrice->service_id, ['A', 'I']);

                      if (!empty($infoServiceRecharge)) {
                        if (!empty($infoServiceRecharge->service_recharge)) {
                          $service_recharge = $infoServiceRecharge->service_recharge;
                        } else {
                          $txMsg = "FAIL en busqueda del servicio de recarga de susbscripcion de la instalacion de fibra: " . $dataIns->id . " (CRF561)";
                          $this->info('<fg=black;bg=white> * ' . $txMsg);
                          $infoMsg = new \stdClass;
                          $infoMsg->code = "EMP_SER";
                          $infoMsg->message = $txMsg;
                          $dataIns->obs_activate = $infoMsg;
                          $dataIns->save();
                          Log::alert($txMsg);
                        }
                      } else {
                        $txMsg = "FAIL en busqueda del servicio pagado en el alta de la instalacion de fibra: " . $dataIns->id . " (CRF571)";
                        $this->info('<fg=black;bg=white> * ' . $txMsg);
                        $infoMsg = new \stdClass;
                        $infoMsg->code = "EMP_SER";
                        $infoMsg->message = $txMsg;
                        $dataIns->obs_activate = $infoMsg;
                        $dataIns->save();
                        Log::alert($txMsg);
                      }
                    } else {
                      $txMsg = "FAIL en busqueda de packPrice de la instalacion de fibra: " . $dataIns->id . " (CRF581)";
                      $this->info('<fg=black;bg=white> * ' . $txMsg);
                      $infoMsg = new \stdClass;
                      $infoMsg->code = "EMP_SER";
                      $infoMsg->message = $txMsg;
                      $dataIns->obs_activate = $infoMsg;
                      $dataIns->save();
                      Log::alert($txMsg);
                    }
                  } else {
                    $this->info('<fg=white;bg=black> * Busqueda de combinatoria de recarga de una susbcripcion de fibra bundle');
                    $infoBundle = Bundle::getComponentBundle($dataIns->bundle_id_payment, ['A', 'I']);
                    if (!empty($infoBundle)) {
                      if (!empty($infoBundle->recharge_susbcription)) {
                        $service_recharge = $infoBundle->recharge_susbcription;
                      } else {
                        $txMsg = "FAIL en busqueda del identificador de combinatoria de recarga del bundle: " . $dataIns->bundle_id_payment . " (CRF597) para instalacion: " . $dataIns->id;
                        $this->info('<fg=black;bg=white> * ' . $txMsg);
                        $infoMsg = new \stdClass;
                        $infoMsg->code = "EMP_SER";
                        $infoMsg->message = $txMsg;
                        $dataIns->obs_activate = $infoMsg;
                        $dataIns->save();
                        Log::alert($txMsg);
                      }
                    } else {
                      $txMsg = "FAIL en busqueda de bundle: " . $dataIns->bundle_id_payment . " (CRF581) para instalacion: " . $dataIns->id;
                      $this->info('<fg=black;bg=white> * ' . $txMsg);
                      $infoMsg = new \stdClass;
                      $infoMsg->code = "EMP_SER";
                      $infoMsg->message = $txMsg;
                      $dataIns->obs_activate = $infoMsg;
                      $dataIns->save();
                      Log::alert($txMsg);
                    }
                  }

                  if (!empty($service_recharge)) {
                    //servicio con el quedara recargando el cliente que pago subscripcion desde el alta
                    //
                    //Envio:
                    //reference: unique_transaction del cliente
                    //services: Array que contiene los elementos de la subscripcion que seran actualizados
                    //  => [
                    //  'installation' => ID de la cita de instalacion(se usa como filtro),
                    //  'service' => Servicio con el que esta de alta (se usa de filtro para fibra solo y fibra bundle, la diferencia radicaria que si es fibra normal el valor apuntaria  a la tabla service de lo contrario apuntaria  a la tabla de combinatoria de bundle)
                    //  'bundle_id' => Bundle con el que esta de alta (se usa de filtro para fibra bundle)
                    //  'msisdn_change' => Dn que notificaremos que se dio de alta desde el seller (fibra en caso de ser solo fibra, o de los diferentes productos en el array en caso de bundle)
                    //  'service_change' => id del servicio de recarga
                    //  'bundle_change' => No se pa' que lo creo guzman... (Solo se usa cuando es bundle la subscripcion)
                    //  'type' => 'R' (Recarga)
                    //  'status' => 'A' se debe enviar en mi casa para que mit lo procese
                    //  'date_next_charge' => Fecha en que se debe hacer la recarga( fecha actual + peridicidad del servicio de alta)
                    //  ]

                    //Se debe calcular la periodicidad del plan que se dio de alta y obtener la fecha en la que se debe activar el servicio de recarga
                    //
                    $next_date_payment = $dateExp;

                    $DataSend = [
                      'reference' => $dataIns->unique_transaction,
                      'status' => 'A',
                      'services' => [
                        [
                          'installation' => $dataIns->id,
                          'type' => 'P',
                          'date_next_charge' => $next_date_payment,
                          'msisdn_change' => $msisdn,
                          'service_change' => $service_recharge,
                        ]]];

                    if (!empty($dataIns->bundle_id_payment)) {
                      //Es una subscripcion desde el alta
                      $DataSend['services'][0]['bundle_id'] = $dataIns->bundle_id_payment;
                      $DataSend['services'][0]['bundle_change'] = null;
                    } else {
                      $DataSend['services'][0]['service'] = $dataIns->service_id;
                    }

                    $updateSubscription = ApiMIT::sendRequest('subscriptions/update', $DataSend, 'PUT');

                    if ($updateSubscription['success']) {
                      $this->info('<fg=white;bg=black> * Procesado cambio de servicio de recarga ante MIT');
                      Installations::notifyChangerMP($dataIns->id, 'Y');
                    } else {
                      $this->info('<fg=black;bg=white> * FAIL cambio de servicio de recarga ante MIT');

                      $txMsg = $updateSubscription['msg'] . " " . $updateSubscription['msg-MIT'] . " DN de fibra: " . $msisdn . " Datos enviados: " . (String) json_encode($DataSend) . " (CRF668)";
                      Log::error($txMsg);

                      $infoMsg = new \stdClass;
                      $infoMsg->code = "ERR_MIT";
                      $infoMsg->message = "No se pudo actualizar pago recurrente ante la pasarela de pago MIT";
                      $dataIns->obs_activate = $infoMsg;
                      $dataIns->save();
                      $this->info("- " . $infoMsg->message);
                      Installations::notifyChangerMP($dataIns->id, 'N');
                    }
                  } else {
                    $this->info('<fg=black;bg=white> * FAIL en la busqueda de servicio de recarga de pago recurrente');
                    $txMsg = "El servicio de recarga recurrente no se encontro (CRF681) para la cita de instalacion de fibra: " . $dataIns->id;
                    Log::error($txMsg);
                  }
                }
                /*Envio email al cliente de bienvenida luego de ser instalado el servicio en su casa*/

                $infomail = Installations::getAddressInstalation($dataIns->id);
                //busco nombres, telefono y correo con el dni
                //
                //Para temas de historial la tabla instalacion guarda fecha de instalacion
                //
                if (!empty($infomail)) {
                  $mailData = array(
                    'name' => $infomail->name,
                    'lastname' => $infomail->last_name,
                    'dn' => $msisdn,
                    'phone1' => $infomail->phone_home,
                    'email' => $infomail->email,
                    'address' => $infomail->address_instalation,
                  );
                  if (!empty($infomail->email)) {
                    try {
                      Mail::to($infomail->email)->send(new mailWelcomeFibra($mailData));
                    } catch (\Exception $e) {
                      Log::error('No se pudo enviar el correo de bienvenida de fibra a: ' . $infomail->email . ' (CRF705) Error: ' . (String) json_encode($e->getMessage()));
                    }
                  }

                  //Envio de msj con los detalles de la instalacion
                  //
                  $infClient = Client::getClientINEorDN($dataIns->clients_dni);

                  if (!empty($infClient)) {
                    if (!empty($infClient->phone_home)) {

                      $Infomsj = FiberZone::getInfoZone($dataIns->id_fiber_zone);
                      $configuration = json_decode(json_encode($Infomsj->configuration));
                      $smsInstall = '';
                      if (isset($configuration->smsInstall)) {
                        if (!empty($configuration->smsInstall)) {
                          $smsInstall = $configuration->smsInstall;
                        }
                      }
                      //Envio SMS de alta
                      /* Altan::sendSms([
                      "msisdn" => $infClient->phone_home,
                      "service" => $service->title,
                      "pack" => $dataIns->pack_id,
                      "concentrator" => 1,
                      "sms_attrib" => "SMSINSTALLFIBRA",
                      "type_sms" => "O"]);*/

                      if (!empty($smsInstall)) {

                        //Se realiza personalizacion del mensaje a ser enviado

                        $smsInstall = customerSMS(
                          $smsInstall,
                          false,
                          $infomail->name,
                          $msisdn,
                          $dataIns->service_id,
                          $dateExp
                        );

                        Sms_notification::Send_sms(
                          $msisdn,
                          $infClient->phone_home,
                          'F',
                          '1',
                          $service->title,
                          "SMSFIBRAWELCOME",
                          $smsInstall);
                        $this->info('<fg=white;bg=black> * Registrado msj de bienvenida a fibra');

                      } else {
                        Log::error("No se envio sms de notificacion de instalacion al cliente (" . $dataIns->clients_dni . "), la zona (" . $dataIns->id_fiber_zone . ") no lo tiene configurado. (CRF757)");
                      }
                    } else {
                      Log::error("El cliente (" . $dataIns->clients_dni . ") no tiene configurado un telefono de contacto para el envio de sms luego de ser instalador el servicio de fibra. (CRF760)");
                    }
                  }
                } else {
                  Log::alert('No se pudo localizar la informacion de instalacion para el correo para del msisdn de fibra: ' . $msisdn . ' y dni: ' . $dataIns->clients_dni . ' (CRF764)');
                }
                //Se llevo a cabo el alta ante 815 y netwey sin problemas

                $dateEndOne = microtime(true);
                $totalOne = $dateEndOne - $dateInitialOne;
                $this->info('<fg=black;bg=white> >> Instalacion de fibra (' . $dataIns->id . ') procesada.! Tiempo: ' . $totalOne . ' seg');
                $this->info('');

                ///Guardo el tiempo de duracion
                $infoMsg = new \stdClass;
                $infoMsg->code = "OK";
                $infoMsg->message = "Activación en 815 del servicio de fibra exitoso!";
                $infoMsg->time_item = round($totalOne, 2) . ' seg';
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;

                $processCron = new \stdClass;
                $processCron->end = date("Y-m-d H:i:s");

                if (!empty($dataIns->dateProcess)) {
                  $initCron = json_decode(json_encode($dataIns->dateProcess));
                  $processCron->start = $initCron->start;
                  $fechaSegundos_start = strtotime($initCron->start); //Convertimos a segundos

                  $timeInSeg = strtotime($processCron->end) - strtotime($processCron->start);
                  $dataIns->processing_time_cron = Common::transforTime($timeInSeg);
                } else {
                  //No tengo registro de insercion en cron
                  $processCron->start = '';
                  $dataIns->processing_time_cron = Common::transforTime($totalOne);
                  // $dataIns->processing_time_cron = round($totalOne, 2);
                }
                $dataIns->dateProcess = $processCron;
                $dataIns->save();

              } else {
                try {
                  ClientNetwey::where('msisdn', $msisdn)->delete();
                } catch (Exception $e) {
                  Log::error('No se pudo eliminar el pre-registro de cliente netwey. Detalles: ' . (String) json_encode($e->getMessage()) . ' CR804)');
                }

                $infoMsg = new \stdClass;
                $infoMsg->code = $res815['code'];
                $infoMsg->message = $res815['msg'];
                if ($res815['code'] == "FAIL_MAC" ||
                  $res815['code'] == "EMPTY_IP") {
                  $dataIns->status = 'E';
                }
                $dataIns->token_activate = null;
                $dataIns->obs_activate = $infoMsg;
                $dataIns->cant_attempts = $NumCheking;
                $dataIns->save();
                $this->info('<fg=black;bg=yellow> >>> Hubo un problema con 815 al activar el DN ' . $msisdn . ' de la instalacion ' . $dataIns->id);
              }
            } else {
              $infoMsg = new \stdClass;
              $infoMsg->code = "FAIL_PK";
              $infoMsg->message = 'Service_PK no válido';
              $dataIns->obs_activate = $infoMsg;
              $dataIns->cant_attempts = $NumCheking;
              $dataIns->save();
              $this->info("- " . $infoMsg->message);
            }
          } else {
            //Bd bloqueada
            $infoMsg = new \stdClass;
            $infoMsg->code = "BD_BLOCK";
            $infoMsg->message = "Base de datos 815 bloqueada";
            $UpFiber->obs_activate = $infoMsg;
            $dataIns->cant_attempts = $NumCheking;
            $dataIns->save();
            $this->info("- " . $infoMsg->message);
          }
        }
        unset($dataIns); // rompe la referencia con el último elemento

      } else {
        $this->info('<fg=black;bg=yellow> > No se encontraron las instalaciones de fibra marcadas para activar!');
        // Log::info('No hay instalaciones de fibra por activar!');
      }
    } else {
      $this->info('<fg=white;bg=red> > No hay instalaciones de fibra por activar en las OLT activas!');
    }
    $dateEndAll = microtime(true);
    $totalTime = $dateEndAll - $dateInitialAll;
    //$this->info('<error> Comando finalizado... <error>');
    $this->info('');
    $this->info('<fg=black;bg=white> Comando finalizado... Duracion: ' . $totalTime . ' seg');
    return 0;
  }
}
