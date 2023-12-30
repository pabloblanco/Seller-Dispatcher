<?php

namespace App\Http\Controllers;

use App\Mail\mailContract;
use App\Mail\mailInstall;
use App\Mail\Mail_paymentQr;
use App\Mail\Mail_tycfQr;
use App\Models\ArticlePack;
use App\Models\Bundle;
use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\ClientNetweyBundle;
use App\Models\Client_document;
use App\Models\FiberArticleZone;
use App\Models\FiberCity;
use App\Models\FiberCityZone;
use App\Models\FiberHoliday;
use App\Models\FiberPaymentForce;
use App\Models\FiberQuestionResult;
use App\Models\FiberQuestions;
use App\Models\FiberTypification;
use App\Models\FiberZone;
use App\Models\History_control;
use App\Models\Installations;
use App\Models\InstallationsBundle;
use App\Models\Inventory;
use App\Models\Pack;
use App\Models\PackPrices;
use App\Models\Sale;
use App\Models\SellerInventory;
use App\Models\Service;
use App\Models\Sms_notification;
use App\Models\TelephoneCompany;
use App\Models\User;
use App\Models\Verify_contact_client;
use App\Utilities\ApiMIT;
use App\Utilities\Common;
use App\Utilities\Google;
use App\Utilities\ProcessRegAlt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Validator;

class SellerFiberController extends Controller
{
  public function index(Request $request)
  {
    $lock = User::getOnliyUser(session('user'));

    return view('fiber.index', compact('lock'));
  }

  private static function getArrayForInstalationsNotPaid(&$registers)
  {
    foreach ($registers as $register) {

      if (!empty($register->date_install)) {
        $register->date_install = getFormatDate($register->date_install, 'd-m-Y H:i');
      }

      switch ($register->status) {
        case 'A':$register->status = 'Por Instalar';
          break;
        case 'P':$register->status = 'Instalado';
          break;
        case 'R':$register->status = 'Reprogramado';
          break;
      }
    }
  }

  private function _getPaginateData($page = 1, $search = '', $collection, $routeName, $function)
  {
    //$search = trim($search);
    $firstpg = '#';
    $lastpg = '#';

    $limitPgNumber = 4; //limite del paginador, (1,2..4)
    $take = 4; //Limite de la data
    $skip = $page == 1 ? 0 : ($take * ($page - 1)); //Datos a saltar

    $totalRegisters = $collection->count();

    $registers = $collection->skip($skip)
      ->take($take)
      ->get();

    //Calculando paginas totales
    $pages = ceil($totalRegisters / $take);

    $dataPages = []; //array que almacena informacion del paginado

    if ($pages > 1) {
      //numero de vueltas que debe dar el ciclo
      $loop = $pages > $limitPgNumber ? $limitPgNumber : $pages;
      //Calculando el numero en el que debe comenzar el ciclo

      if ($pages <= 4) {
        $start = 0;
      } else {
        if (($page - 1) <= ($pages - $limitPgNumber)) {
          $start = $page - 1;
        } else {
          $start = $pages - $limitPgNumber;
        }
      }

      //cliclo que arma el array de paginado
      for ($i = $start; $i < ($loop + $start); $i++) {
        //parametro para armar url del paginado
        $params = array('page' => ($i + 1));
        if (!empty($search)) {
          $params['search'] = $search;
        }

        $dataPages[] = array(
          'active' => $i == ($page - 1) ? true : false, //pagina activa
          'url' => route($routeName, $params), //URL de la pagina
          'number' => ($i + 1), //Numero a mostrar en el paginado
        );
      }

      //Calculando pagina a la que debe apuntar la flecha "inicio" de retroceso en el paginado
      $params = array('page' => 1);
      if (($loop + $start) > $limitPgNumber) {
        $params = array('page' => ($loop + $start) - $limitPgNumber);
      }

      //agregando parametro de busqueda en caso de que exista
      if (!empty($search)) {
        $params['search'] = $search;
      }

      //url a la que debe apuntar la flecha "ultimo"
      $paramslast = array('page' => $pages);
      if (!empty($search)) {
        $paramslast['search'] = $search;
      }

      $firstpg = route($routeName, $params);
      $lastpg = route($routeName, $paramslast);
    }

    call_user_func_array(self::class . '::' . $function, array(&$registers));

    $data = array(
      "recordsTotal" => $totalRegisters,
      "totalPAges" => $pages,
      "actualPage" => $page,
      "registers" => $registers,
      "limit" => $take,
      "pages" => $dataPages,
      "first" => $firstpg,
      "last" => $lastpg,
    );

    return $data;
  }

  public function payPendingAjax($page = 1, $search = '')
  {
    $filters = explode(',', $search);

    foreach ($filters as $filter) {
      $filter = explode('=', $filter);
      if (count($filter) == 2) {
        if ($filter[0] == 'status') {
          $status = $filter[1];
        }
      }
    }

    $instalationsNotPaid = Installations::getPendingPay(session('user'), [
      'status' => !empty($status) ? $status : null,
    ]);

    $data = $this->_getPaginateData(
      $page,
      trim($search),
      $instalationsNotPaid,
      'sellerFiber.payPendingAjax',
      'getArrayForInstalationsNotPaid'
    );

    return response()->json($data);
  }

  public function payPending(Request $request, $page = 1, $search = "")
  {
    $lock = User::getOnliyUser(session('user'));

    $filters = explode(',', $search);

    foreach ($filters as $filter) {
      $filter = explode('=', $filter);
      if (count($filter) == 2) {
        if ($filter[0] == 'status') {
          $status = $filter[1];
        }
      }
    }
    $instalationsNotPaid = Installations::getPendingPay(session('user'), [
      'status' => !empty($status) ? $status : null,
    ]);

    $data = $this->_getPaginateData(
      $page,
      trim($search),
      $instalationsNotPaid,
      'sellerFiber.payPendingAjax',
      'getArrayForInstalationsNotPaid'
    );

    return view('fiber.payPendingList', compact('lock', 'data'));
  }

  public function getCompAddress(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $res = ['error' => true, 'msg' => 'No se pudo obtener la dirección desglosada, por favor completala de forma manual.'];

      if (!empty($request->lat) && !empty($request->lng)) {
        $components = Google::getDataFromPosGoogle($request->lat, $request->lng);

        if ($components['success']) {
          $compFormat = Google::getFormatedAddress($components['data']['components']);

          if ($compFormat['success']) {
            return response()->json(['error' => false, 'data' => $compFormat['data']]);
          }
        }
      }
      return response()->json($res);
    }
    return redirect()->route('dashboard');
  }

  public function showClient(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dni)) {
        $client = Client::getClientByDNI($request->dni);

        if (!empty($client)) {
          $client->status = true;
          $client->phoneFail = 'OK'; //No tiene fallo el telefono principal

          if (!empty($client->phone_home)) {
            //Posee telefono se evalua que tenga 10 digitos
            $exp_tlf = "/^[0-9]{10}$/";
            if (!preg_match($exp_tlf, $client->phone_home)) {
              $client->phoneFail = 'FAIL';
            }
          } else {
            //No tiene telefono de contacto, se requiere
            $client->phoneFail = 'EMPTY';
          }

          $client->isVerifyPhone = "";
          if ($client->phoneFail == 'OK') {
            if (!empty($client->verify_phone_id)) {
              //Se evalua si se tiene verify_phone_id que significa que tiene verificado el celular principal
              $codePhone = Verify_contact_client::getRegisterVerify($client->verify_phone_id);
              if (!empty($codePhone)) {
                $client->isVerifyPhone = $codePhone->status;
              }
            }
            //enum('CREATE','VERIFIED','REJECTED','APPLICATION','AUTHORIZED','T')
            //IS_PHONE_CLIENT,IS_PHONE_USER,IS_PHONE_USER_SELLER,PHONE_EMPTY
            //VERIFIED es el tipo que nos interesa para marcar el checking
          }

          $client->mailFail = 'OK'; //Email con formato correcto
          if (!empty($client->email)) {
            //Posee email se evalua que sea valido
            //$exp_email = "/^[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}$/";
            $exp_email = "/^[A-Za-z0-9][A-Za-z0-9._-]*[A-Za-z0-9]@[A-Za-z0-9]+(\.[A-Za-z0-9]+)*\.[a-zA-Z]{2,4}$/";
            if (!preg_match($exp_email, $client->email)) {
              $client->mailFail = 'FAIL';
            }
          } else {
            //No tiene correo de contacto, se requiere
            $client->mailFail = 'EMPTY';
          }

          //Consultando ciudades de 815
          $states = FiberCity::getStates();

          if (count($states) > 0) {
            $cantState = true;
          } else {
            $cantState = false;
          }
          $html = view('fiber.client', compact('client', 'states', 'cantState'))->render();
          return response()->json(['error' => false, 'html' => $html, 'dni' => $client->dni]);
        }
      }
      return response()->json(['error' => true]);
    }
    return redirect()->route('dashboard');
  }

  public function getCitys(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $cities = FiberCity::getCitys($request->stateId);
      $html = view('fiber.Select_Citys',
        compact(
          'cities',
        )
      )->render();

      return response()->json(array('success' => true, 'msg' => $html, 'error' => false));
    }
    return redirect()->route('dashboard');
  }

  public function getOlts(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $olts = FiberCity::getOlts($request->cityid);
      //reviso que existan instaladores en la zona
      foreach ($olts as $itemOlt) {
        $isInstall = User::getInstallerZone($itemOlt->zone_id);
        if (count($isInstall) > 0) {
          //zona con instaladores registrados
          $itemOlt->installer = 'Y';
        } else {
          //No hay instaladores registrados
          $itemOlt->installer = 'N';
        }
      }

      $html = view('fiber.Select_Olts',
        compact(
          'olts',
        )
      )->render();

      return response()->json(array('success' => true, 'msg' => $html, 'error' => false));
    }
    return redirect()->route('dashboard');
  }

  public function getNodesRed(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->oltid)) {
        $infozone = FiberZone::getInfoZone($request->oltid);
        $param = json_decode(json_encode($infozone->param));

        $NodoRed = $param->nodo_de_red;
        $html = view('fiber.Select_nodosRed',
          compact(
            'NodoRed',
          )
        )->render();

        return response()->json(array('success' => true, 'msg' => $html, 'error' => false));
      }
      return response()->json(array('success' => false, 'message' => "Faltan datos para continuar", 'error' => true));
    }
    return redirect()->route('dashboard');
  }

/**
 * [getMapCoverage Se obtiene el mapa de cobertura de una ciudad de una olt en especifico]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getMapCoverage(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      //Obtengo el poligono del area para la OLT y la ciudad seleccionada
      //
      $DataPoligono = null;
      $PoligonoCental = null;
      if (!empty($request->cityid) &&
        !empty($request->oltid)) {
        $DataPoligono = FiberCityZone::getCoordenada($request->cityid, $request->oltid);
        if (!empty($DataPoligono)) {
          //Obtengo informacion del poligono
          $DataPoligono = $DataPoligono['poligono'];
          //punto central del poligono
          $PoligonoCental = FiberCityZone::getCoordCenter($DataPoligono['poligono']);
        }
      }
      //cargo el input para escribir la direccion
      $html2 = view('fiber.inputAddress')->render();

      return response()->json(array('success' => true, 'inputAddress' => $html2, 'dataPoligono' => $DataPoligono, 'point_center' => $PoligonoCental, 'error' => false));
    }
    return redirect()->route('dashboard');
  }

/**
 * [getPlanes Retorna la lista de planes en la OLT en la que el cliente desea ser conectado]
 * NOTA: Este metodo se bifurca en dos tiempo, en el primer render al seleccionar la OLT se trae la info base de los planes y la segunda fase seria cuando se cambia el plan
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getPlanes(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $init = ($request->type === "init") ? true : false;

      if (!empty($request->dni)) {
        //Consultando si el cliente tiene un dn hbb a asociado

        $isMig = false;

        if (hasPermit('MIG-FIB')) {
          $isMig = ClientNetwey::isClientByTypes($request->dni, ['H', 'M', 'MH']) > 0 ? true : false;
        }

        //Filtro de planes tradicionales / forzados
        if (!empty($request->packforcer)) {
          $Packforcer = $request->packforcer;
        } else {
          $Packforcer = 'N';
        }
        //Filtro de planes sin suscripcion / con sucripcion
        if (!empty($request->packSuscrip)) {
          $PackSuscrip = $request->packSuscrip;
        } else {
          $PackSuscrip = 'N';
        }

        //Filtro de planes sin bundle / con bundle (Fibra+simcard)
        if (!empty($request->packBundler)) {
          $PackBundler = $request->packBundler;
        } else {
          $PackBundler = 'N';
        }

        if ($init) {
          //Consultando packs
          $packs = Pack::getFiberPacks($request->oltid, $isMig);
        } else {
          $packs = Pack::getFiberPacks($request->oltid, $isMig, $Packforcer, $PackSuscrip, $PackBundler);
        }

        //Si es migración pero no se consigue ningun pack para migración se buscan los packs normales
        if ($isMig && $packs->count() == 0) {
          if ($init) {
            $packs = Pack::getFiberPacks($request->oltid, false);
          } else {
            $packs = Pack::getFiberPacks($request->oltid, false, $Packforcer, $PackSuscrip, $PackBundler);
          }
        }

        $packs = $packs->filter(function ($val, $key) {
          if (!empty($val->date_ini) && !empty($val->date_end)) {
            if (strtotime($val->date_ini) <= time() && strtotime($val->date_end) >= time()) {
              return true;
            }
          } elseif (!empty($val->date_ini)) {
            if (strtotime($val->date_ini) <= time()) {
              return true;
            }
          } elseif (!empty($val->date_end)) {
            if (strtotime($val->date_end) >= time()) {
              return true;
            }
          } elseif (empty($val->date_ini) && empty($val->date_end)) {
            return true;
          }
        });

        $htmlDocument = '';

        foreach ($packs as $pack) {
          if ($pack->is_payment_forcer == 'Y') {
            $typeDocument = Common::getOptionColumn('islim_client_document', 'type');

            $htmlDocument = view('fiber.identification',
              compact('typeDocument'))->render();
            break;
          }
        }
        if ($init) {
          //Planes normales
          $packsN = $packs->filter(function ($val, $key) {
            if ($val->is_payment_forcer == 'N') {
              return true;
            }
          });

          //Planes forzados
          $packsF = $packs->filter(function ($val, $key) {
            if ($val->is_payment_forcer == 'Y') {
              return true;
            }
          });

          //Planes sin suscripcion
          $packsSS = $packs->filter(function ($val, $key) {
            if ($val->for_subscription == 'N') {
              return true;
            }
          });

          //Planes con suscripcion
          $packsCS = $packs->filter(function ($val, $key) {
            if ($val->for_subscription == 'Y') {
              return true;
            }
          });

          //Planes con bundle
          $packsBun = $packs->filter(function ($val, $key) {
            if ($val->is_bundle == 'Y') {
              return true;
            }
          });
          $html = view('fiber.Select_block_plan',
            compact('packsN', 'packsF', 'packsSS', 'packsCS', 'packsBun', 'htmlDocument'))->render();
        } else {
          $view = "seller";
          $html = view('fiber.Select_plan',
            compact('packs', 'view'))->render();
        }
        return response()->json(array('success' => true, 'msg' => $html, 'error' => false, 'htmlDocument' => $htmlDocument));
      }
      return response()->json(array('success' => false, 'message' => "Faltan datos para procesar la lista de planes", 'error' => false));
    }
    return redirect()->route('dashboard');
  }

/**
 * [getPlan Retorna la informacion de un plan seleccionado]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getPlan(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $res = ['sucess' => false, 'msg' => 'No se pudo obtener el detalle del plan'];

      if (!empty($request->plan)) {
        $bundle = Common::decodificarBase64($request->isbundle);
        $option = array("Y", "N");

        if (in_array($bundle, $option)) {
          $idplan = intval(Common::decodificarBase64($request->plan));

          if ($bundle == 'N') {
            $plan = Pack::getPlanDetailFiber($idplan);

            if (!empty($plan)) {

              $html = view('fiber.plan', compact('plan'))->render();

              $res = ['sucess' => true, 'html' => $html];
            }
          } else {
            //Se tiene el id del bunbler
            //Verificamos la categoria de los productos del bundle
            //
            $plan = Bundle::getDetailBundleAlta($idplan);

            if ($plan['success']) {
              $plan = json_decode(json_encode($plan['data']));
              $html = view('fiber.plan', compact('plan', 'bundle'))->render();

              $res = ['sucess' => true, 'html' => $html];
            } else {
              $res = ['sucess' => false, 'msg' => $plan['data']];
            }
          }
        } else {
          $res = ['sucess' => false, 'msg' => "Los datos enviados tiene una incongruencia (CF565)"];
        }
      }
      return response()->json($res);
    }
    return redirect()->route('dashboard');
  }

  private function verifyInventoryBundle($listInstall, $pack_id, $permit_boss, $jefe, $limit = 20)
  {
    //Se mira la lista de instaladores que puden instalar teniendo el inventario
    $installerDispose = User::FilterInstallerInventary($listInstall, $pack_id, $limit);
    $listInstall = [];
    foreach ($installerDispose as $item) {
      array_push($listInstall, $item->email);
    }
    //Se mira el jefe si tiene el inventario para autoasignarse la cita

    if (filter_var($permit_boss, FILTER_VALIDATE_BOOLEAN)) {
      $installerBoss = User::FilterInstallerInventary($jefe, $pack_id, $limit);
      if (count($installerBoss) == 0) {
        $permit_boss = false;
      }
    }
    return array('installerDispose' => $installerDispose, 'permit_boss' => $permit_boss, 'listInstall' => $listInstall);
  }

/**
 * [getListInstaller Lista los instaladores con disponibilidad de horario para un turno especifico]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getListInstaller(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->pack) && !empty($request->cita)) {
        //Se obtiene la zona, la fecha y el turno de instalacion
        //
        $dataInstall = Installations::getAddressInstalation($request->cita);
        if (!empty($dataInstall)) {

          //basado en la zona, la fecha y el turno se lista los instaladores que tiene citas
          //
          $InstallAsigned = Installations::getInstallerAsignedUser($dataInstall->id_fiber_zone, $dataInstall->date_instalation);

          //Obtengo la capacidad por instalador dada la zona
          $infoZone = FiberZone::getInfoZone($dataInstall->id_fiber_zone);
          $configuration = json_decode(json_encode($infoZone->configuration));

          if (!isset($configuration->capacity_installer)) {
            //Si no esta definido en el json por defecto es 1
            $configuration->capacity_installer = 1;
          }
          $notUser = [];
          foreach ($InstallAsigned as $citaAsigne) {

            if ($citaAsigne->schedule == $dataInstall->schedule &&
              $citaAsigne->cant_turno >= $configuration->capacity_installer) {
              array_push($notUser, $citaAsigne->installer);
            }
          }
          //Busco los usuarios instaladores con politica
          $installerDispose = User::searchInstallerFree(false, $notUser, $dataInstall->id_fiber_zone, session('user'), 20);

          //Se evalua las auto asignaciones que se hace el jefe de instaladores para saber si puede tomar la cita
          $InstallAutoAsigned = Installations::getInstallerAsignedUser($dataInstall->id_fiber_zone, $dataInstall->date_instalation, session('user'));

          $permit_boss = true;

          foreach ($InstallAutoAsigned as $autoAsigne) {
            if ($autoAsigne->schedule == $dataInstall->schedule &&
              $autoAsigne->cant_turno >= $configuration->capacity_installer) {
              $permit_boss = false;
              //Log::info('No me puedo auto asignar la cita');
            }
          }

          $pack_id = $request->pack;
          $cita_id = $request->cita;
          $isBundle = $request->isBundle;

          if ($isBundle == 'Y' && !empty($dataInstall->bundle_id)) {
            //Como se trata de bundle se debe verificar inventario

            $infoBun = Bundle::getDetailBundleAlta($dataInstall->bundle_id);

            if ($infoBun['success']) {
              $plan = json_decode(json_encode($infoBun['data']));
              $listInstall = [];
              $jefe = [session('user')];
              foreach ($installerDispose as $item) {
                array_push($listInstall, $item->email);
              }

              if ($plan->general->containt_H == 'Y') {

                $infoInv = self::verifyInventoryBundle($listInstall, $plan->info_H->id, $permit_boss, $jefe);
                $installerDispose = $infoInv['installerDispose'];
                $listInstall = $infoInv['listInstall'];
                $permit_boss = $infoInv['permit_boss'];
              }

              if ($plan->general->containt_M == 'Y') {

                $infoInv = self::verifyInventoryBundle($listInstall, $plan->info_M->id, $permit_boss, $jefe);
                $installerDispose = $infoInv['installerDispose'];
                $listInstall = $infoInv['listInstall'];
                $permit_boss = $infoInv['permit_boss'];
              }

              if ($plan->general->containt_MH == 'Y') {

                $infoInv = self::verifyInventoryBundle($listInstall, $plan->info_MH->id, $permit_boss, $jefe);
                $installerDispose = $infoInv['installerDispose'];
                $listInstall = $infoInv['listInstall'];
                $permit_boss = $infoInv['permit_boss'];
              }

              if ($plan->general->containt_T == 'Y') {

                $infoInv = self::verifyInventoryBundle($listInstall, $plan->info_T->id, $permit_boss, $jefe);
                $installerDispose = $infoInv['installerDispose'];
                $listInstall = $infoInv['listInstall'];
                $permit_boss = $infoInv['permit_boss'];

              }
            } else {
              $res = ['sucess' => false, 'msg' => $infoBun['data']];
              return response()->json($res);
            }
          }
          $html = view('dashboard.select_installer', compact('installerDispose', 'permit_boss', 'pack_id', 'cita_id', 'isBundle'))->render();
          $res = ['sucess' => true, 'html' => $html];
        } else {
          $res = ['sucess' => false, 'msg' => "No se encontro los detalles de la cita de instalación (CF699)"];
        }
      } else {
        $res = ['sucess' => false, 'msg' => "Hacen falta datos para procesar la lista de instaladores (CF702)"];
      }
      return response()->json($res);
    }
    return redirect()->route('dashboard');
  }

/**
 * [findInstaller busca el instalador con inventario asignado]
 * @param  Request $request [description]
 * @param  boolean $search  [description]
 * @return [type]           [description]
 */
  public function findInstaller(Request $request, $search = false)
  {
    if (!empty($request->search) && !empty($request->pack_id)) {
      return response()->json(User::searchInstaller($request->search, $request->pack_id, 20));
    }
    return response()->json([]);
  }

/**
 * [regInstall Se registra en BD de instalaciones una nueva cita de fibra]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function regInstall(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = [
        'success' => false,
        'msg' => 'Por favor verifique los datos.',
        'code' => 'FAIL'];

      $messages = [
        'client.required' => 'El id del prospecto es requerido (IVF737)',
        'city_id.required' => 'El id de Ciudad es requerido (IVF738)',
        'city_id.regex' => 'El id de Ciudad no es un valor valido (IVF739)',
        'state_id.required' => 'El id de Estado es requerido (IVF740)',
        'state_id.regex' => 'El id de Estado no es un valor valido (IVF740)',
        'reference.required' => 'El referencia de dirección de instalación de fibra es requerido (IVF742)',
        'pack.required' => 'El id del paquete de fibra es requerido (IVF743)',
        'date.required' => 'El fecha de instalación de fibra es requerido (IVF744)',
        'hour.required' => 'El turno de instalación de fibra es requerido (IVF745)',
        'pay.required' => 'El indicativo de pago de instalación de fibra es requerido (IVF746)',
        'pay.regex' => 'El indicativo de pago de instalación de fibra es invalido (IVF747)',
        'zone_id.required' => 'El fecha de instalación de fibra es requerido (IVF748)',
        'zone_id.regex' => 'El id de zona de instalación de fibra es requerido (IVF749)',
        'isbundle.required' => 'El indicativo  de instalación de fibra bundle es requerido (IVF750)',
        'isbundle.regex' => 'El indicativo de instalación de fibra bundle no es valido (IVF751)',
      ];

      $requisitos = [
        'client' => array(
          'required',
        ),
        'city_id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'state_id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'reference' => array(
          'required',
        ),
        'pack' => array(
          'required',
        ),
        'date' => array(
          'required',
        ),
        'hour' => array(
          'required',
        ),
        'pay' => array(
          'required',
          'regex:/(^([N]|[Y])$)/u',
        ),
        'zone_id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'isbundle' => array(
          'required',
          'regex:/(^([N]|[Y])$)/u',
        ),
      ];

      if (isset($request->isReferred) && !empty($request->isReferred)) {
        $messages['isReferred.regex'] = 'El dn de referencia no posee un valor valido (IVF793)';
        $requisitos['isReferred'] = array(
          'regex:/(^[0-9]{10}$)/u',
        );
      }

      if (isset($request->imeiPhone) && !empty($request->imeiPhone)) {
        $messages['imeiPhone.regex'] = 'El IMEI de celular no posee un valor valido (IVF800)';
        $requisitos['imeiPhone'] = array(
          'regex:/(^[0-9]{15}$)/u',
        );
        $messages['isBandTE.regex'] = 'El indicativo de la banda del celular no posee un valor valido (IVF804)';
        $requisitos['isBandTE'] = array(
          'regex:/(^([N]|[Y])$)/u',
        );
      }

      //adress
      //route
      //numberhouse
      //colony
      //city
      //muni
      //state
      //lat
      //lng
      //photo
      //photo_recibo
      //tyc_start
      //imeiBrand
      //imeiModel

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }

      if (isset($request->isReferred) && !empty($request->isReferred)) {
        //Se debe verificar que el dn a referenciar exista
        $clientRef = ClientNetwey::getClientByDN($request->isReferred);
        if (empty($clientRef)) {
          return response()->json(['success' => false, 'code' => 'ERR_REF', 'msg' => "El missdn " . $request->isReferred . " el cual desea referenciar no es encuentra en sistema (CVF840)"]);
        }
      }

      $isCoverageFiber = true;
      $respCoverage = null;
      if (!empty($request->lat) && !empty($request->lng)) {
        //verifico que sea valida la lat y la lng
        //en caso de mexico es positiva
        $exp_lat = "/^(0(\.\d{1,20})?|([1-9](\d)?)(\.\d{1,20})?|1[0-7]\d{1}(\.\d{1,20})?|180\.0{1,20})$/";
        if (!preg_match($exp_lat, $request->lat)) {
          return response()->json(['success' => false, 'code' => 'ERR_COORD', 'msg' => "La latitud (" . $request->lat . ") no es valida. Verifica de nuevo la servicialidad! (CVF851)"]);
        }

        //en caso de mexico es negativa
        $exp_lng = "/^[\-](0(\.\d{1,20})?|([1-9](\d)?)(\.\d{1,20})?|1[0-7]\d{1}(\.\d{1,20})?|180\.0{1,20})$/";
        if (!preg_match($exp_lng, $request->lng)) {
          return response()->json(['success' => false, 'code' => 'ERR_COORD', 'msg' => "La longitud (" . $request->lng . ") no es valida. Verifica de nuevo la servicialidad! (CVF857)"]);
        }

        //Confirmamos que la coordenada tenga cobertura
        $respCoverage = self::chekingCoverageFiber($request, true);
        if (!$respCoverage['success']) {
          $isCoverageFiber = false;
        }
      }
      if ($isCoverageFiber) {
        $request->pack = intval(Common::decodificarBase64($request->pack));
        $request->hour = Common::decodificarBase64($request->hour);

        if (!empty($request->tyc_start)) {
          $request->tyc_start = intval(Common::decodificarBase64($request->tyc_start));
        } else {
          $request->tyc_start = null;
        }

        //Valido que la fecha y el turno sea valido y no este bloqueado por feriado
        //
        $BlockDay = [];
        $BlockDay = FiberHoliday::getDayFeriado($BlockDay, false);

        if (!empty($BlockDay)) {
          foreach ($BlockDay as $dayFestive) {
            if (strtotime($dayFestive->fecha) == strtotime($request->date)) {
              if ($dayFestive->type == 'media' && $request->hour == "14:00 - 18:00") {
                return response()->json(['success' => false, 'code' => 'ERR_DATE', 'msg' => "Debes ingresar otro turno de instalación ya que el día " . $dayFestive->fecha . " por la tarde es informado como feriado! (CVF885)"]);
              } elseif ($dayFestive->type == 'full') {
                return response()->json(['success' => false, 'code' => 'ERR_DATE', 'msg' => "Debes ingresar otro día de instalación ya que el día " . $dayFestive->fecha . " es informado como feriado! (CVF887)"]);
              }
            }
          }
        }
        //Valido que la fecha y el turno sea valido y no reservado por otra cita confirmada
        $validateDate = self::getClock($request, true);
        if (!$validateDate['availableTime']) {
          return response()->json(['success' => false, 'code' => 'ERR_DATE', 'msg' => "Debes ingresar otro día de instalación ya que la disponibilidad del día " . $request->date . " fue agotada! (CVF895)"]);
        } else {
          foreach ($validateDate['listTurno'] as $turno) {
            if ($turno['habilite'] == 'N' && $turno['hour'] == $request->hour) {
              return response()->json(['success' => false, 'code' => 'ERR_DATE', 'msg' => "Debes ingresar otro turno de instalación ya que la disponibilidad del día " . $request->date . " fue agotada! (CVF899)"]);
            }
          }
        }
        //End validacion de fecha

        $client = Client::getClientByDNI($request->client);
        if (!empty($client)) {
          //Consultando pack para validar si esta activo
          if ($request->isbundle == 'N') {
            $dataPack = Pack::getActivePackById($request->pack);
          } else {
            //Agendamiento de bundle
            $infPack = Bundle::getDetailBundleAlta($request->pack);
            $infoAllBundle = json_decode(json_encode($infPack['data']));
            if ($infPack['success'] && $infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {
              $request->bundle_id = $request->pack;
              $request->pack = $infoAllBundle->info_F->id;
              $dataPack = Pack::getActivePackById($request->pack);
            } else {
              return response()->json(['success' => false, 'code' => $infPack['code'], 'msg' => $infPack['data']]);
            }
          }

          if (!empty($dataPack) && ((hasPermit('MIG-FIB') && $dataPack->is_migration == 'Y') || $dataPack->is_migration == 'N')) {
            //consultando servicio
            $packprice = PackPrices::getPackPriceByPackId($request->pack);
            if (!empty($packprice)) {
              //consultando artículo
              $articpack = ArticlePack::getArticPackByPackId($request->pack);
              if (!empty($articpack)) {
                $seller = User::getUserByEmail(session('user'));
                // $installer = User::getUserByEmail($request->installer);

                if (!empty($seller) /*&& !empty($installer)*/) {
                  $addressFormated = $request->state . ', ' . $request->city . ', ' . $request->muni . ', ' . $request->colony . ', ' . $request->route . ', ' . $request->numberhouse . '. Referencia: ' . $request->reference;
                  $addressFormated = str_replace(' ,', '', $addressFormated);
                  //Actualizando dirección del cliente si no tiene una registrada
                  if (empty($client->address)) {
                    try {
                      Client::getConnect('W')
                        ->where('dni', $request->client)
                        ->update([
                          'address' => $addressFormated]);
                    } catch (Exception $e) {
                      $Txmsg = 'Error al actualizar la direccion del cliente (CF948). ' . (String) json_encode($e->getMessage());
                      Log::error($Txmsg);
                      return response()->json(['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg]);
                    }
                  }

                  $listPhoto = [];
                  if (!empty($request->photo)) {
                    //verificando si viene imagen de referencia
                    array_push($listPhoto, 'photo');
                  }
                  if (!empty($request->photo_recibo)) {
                    //verificando si viene imagen de recibo
                    array_push($listPhoto, 'photo_recibo');
                  }

                  $path = 'installations/reference-photo/';
                  $urlPhoto = '';
                  $urlPhotoRecibo = '';

                  foreach ($listPhoto as $photo) {

                    $photoIdent = $request->file($photo);
                    $photoPath = $path . uniqid() . time() . '.' . $photoIdent->getClientOriginalExtension();

                    Storage::disk('s3')->put(
                      $photoPath,
                      file_get_contents($photoIdent->getPathname()),
                      'public'
                    );

                    if ($photo == "photo") {
                      $urlPhoto = (String) Storage::disk('s3')->url($photoPath);
                    } elseif ($photo == "photo_recibo") {
                      $urlPhotoRecibo = (String) Storage::disk('s3')->url($photoPath);
                    }
                  }

                  $dateIns = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay();

                  try {
                    $install = new Installations;
                    $install->clients_dni = $request->client;
                    $install->address_instalation = $addressFormated;
                    $install->pack_id = $request->pack;
                    $install->service_id = $packprice->service_id;
                    $install->inv_article_id = $articpack->inv_article_id;
                    if ($request->isbundle == 'N') {
                      $install->price = $packprice->total_price;
                    } else {
                      if (isset($infoAllBundle)) {
                        $mount = 0;
                        //Log::info((String) json_encode($infoAllBundle));
                        if ($infoAllBundle->general->containt_H == 'Y') {
                          if (isset($infoAllBundle->info_H->total_price)) {
                            $mount += $infoAllBundle->info_H->total_price;
                          } else {
                            return response()->json(['success' => false, 'code' => 'EMP_PAY', 'msg' => "No se cuenta con el precio del bundle de Hogar (CF1006)"]);
                          }
                        }

                        if ($infoAllBundle->general->containt_M == 'Y') {
                          if (isset($infoAllBundle->info_M->total_price)) {
                            $mount += $infoAllBundle->info_M->total_price;
                          } else {
                            return response()->json(['success' => false, 'code' => 'EMP_PAY', 'msg' => "No se cuenta con el precio del bundle de Mifi (CF1014)"]);
                          }
                        }

                        if ($infoAllBundle->general->containt_MH == 'Y') {
                          if (isset($infoAllBundle->info_MH->total_price)) {
                            $mount += $infoAllBundle->info_MH->total_price;
                          } else {
                            return response()->json(['success' => false, 'code' => 'EMP_PAY', 'msg' => "No se cuenta con el precio del bundle de Mifi Huella (CF1022)"]);
                          }
                        }

                        if ($infoAllBundle->general->containt_T == 'Y') {
                          if (isset($infoAllBundle->info_T->total_price)) {
                            $mount += $infoAllBundle->info_T->total_price;
                          } else {
                            return response()->json(['success' => false, 'code' => 'EMP_PAY', 'msg' => "No se cuenta con el precio del bundle de Telefonia (CF1030)"]);
                          }
                        }

                        if ($infoAllBundle->general->containt_F == 'Y') {
                          if (isset($infoAllBundle->info_F->total_price)) {
                            $mount += $infoAllBundle->info_F->total_price;
                          } else {
                            return response()->json(['success' => false, 'code' => 'EMP_PAY', 'msg' => "No se cuenta con el precio del bundle de Fibra (CF1038)"]);
                          }
                        }
                        $install->price = $mount;
                      } else {
                        return response()->json(['success' => false, 'code' => 'EMP_BUN', 'msg' => "No se pudo obtener informacion del bundle (CF1043)"]);
                      }
                    }
                    $install->seller = session('user');
                    //$install->installer = $request->installer;
                    $install->date_instalation = $dateIns->format('Y-m-d');
                    $install->paid = !empty($request->pay) ? $request->pay : 'N';
                    $install->photo = !empty($urlPhoto) ? $urlPhoto : null;
                    $install->photo_document = !empty($urlPhotoRecibo) ? $urlPhotoRecibo : null;
                    $install->is_migration = $dataPack->is_migration;
                    $install->status = 'A';
                    $install->date_reg = date('Y-m-d H:i:s');
                    $install->user_mod = session('user');
                    $install->date_mod = date('Y-m-d H:i:s');
                    $install->date_paid = (!empty($request->pay) && $request->pay == 'Y') ? date('Y-m-d H:i:s') : null;

                    if (!empty($request->route)) {
                      $install->route = $request->route;
                    }

                    if (!empty($request->numberhouse)) {
                      $install->house_number = $request->numberhouse;
                    }

                    if (!empty($request->colony)) {
                      $install->colony = $request->colony;
                    }

                    if (!empty($request->muni)) {
                      $install->municipality = $request->muni;
                    }

                    if (!empty($request->reference)) {
                      $install->reference = $request->reference;
                    }

                    if (!empty($request->lat)) {
                      $install->lat = $request->lat;
                    }

                    if (!empty($request->lng)) {
                      $install->lng = $request->lng;
                    }
                    //
                    $install->group_install = uniqid($request->client) . time();
                    $install->schedule = $request->hour;
                    $install->id_fiber_zone = $request->zone_id;
                    /*$configConex = new \stdClass;
                    $configConex->nodo_de_red = $request->node_red;
                    $configConex->nodo_de_red_name = $request->node_red_name;
                    $install->config_conex = $configConex;*/
                    $install->id_state = $request->state_id;
                    $install->id_fiber_city = $request->city_id;
                    $install->payment_force_start = $request->tyc_start;
                    $install->bundle_id = isset($request->bundle_id) ? $request->bundle_id : null;
                    $install->referred_dn = (isset($request->isReferred) && !empty($request->isReferred)) ? $request->isReferred : null;
                    $install->save();
                  } catch (Exception $e) {
                    $Txmsg = 'Error al insertar una nueva cita de instalación (CF1101). ' . (String) json_encode($e->getMessage());
                    Log::error($Txmsg);
                    return response()->json(['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg]);
                  }

                  //Para temas de historial la tabla instalacion guarda fecha de registro
                  //
                  //Envio correo
                  $dataBody = [
                    'name' => $client->name,
                    'lastname' => $client->last_name,
                    'seller' => $seller->name . ' ' . $seller->last_name,
                    'sellerPhone' => $seller->phone,
                    //'installer' => $installer->name . ' ' . $installer->last_name,
                    'installer' => "Por establecer...",
                    //'installerPhone' => $installer->phone,
                    'installerPhone' => "Por establecer...",
                    'address' => $addressFormated . ' Referencia: ' . $request->reference,
                    'date' => $dateIns->format('d-m-Y') . ' / ' . $request->hour];

                  if (!empty($client->email)) {
                    try {
                      Mail::to($client->email)->send(new mailInstall($dataBody));
                    } catch (\Exception $e) {
                      Log::error('No se pudo enviar el correo de solicitud de instalación a: ' . $client->email . ' Error: ' . (String) json_encode($e->getMessage()) . '(CF1125)');
                    }
                  }

                  if (!empty($client->email) && !empty($request->tyc_start)) {

                    $dataPaymentForce = FiberPaymentForce::find($request->tyc_start);

                    if (!empty($dataPaymentForce)) {
                      $url_contract = $dataPaymentForce->url_contract;

                      $data_contract = [
                        'url_contract' => $url_contract,
                        'full_date' => $dateIns->format('d-m-Y') . ' / ' . $request->hour,
                        'pack_title' => $dataPack->title,
                        'pack_price' => $packprice->total_price,
                        'phone' => $client->phone_home,
                        'email' => $client->email];

                      try {
                        Mail::to($client->email)->send(new mailContract($data_contract));
                      } catch (\Exception $e) {
                        Log::error('No se pudo enviar el correo de Contrato a: ' . $client->email . ' Error: ' . (String) json_encode($e->getMessage()) . ' (CF1147)');
                      }
                    } else {
                      Log::error('No se pudo enviar el correo de Contrato a: ' . $client->email . ' Error: PaymentForceStrat no existe');
                    }
                  }

                  if ($request->isbundle == 'Y') {
                    //Es una cita con bundle, se registran los hijos de dicho bundle
                    if (isset($infoAllBundle)) {

                      $articlesBundle = [
                        'H',
                        'T',
                        'M',
                        'MH'];

                      foreach ($articlesBundle as $value) {
                        $containt = "containt_" . $value;
                        $info = "info_" . $value;

                        if ($infoAllBundle->general->$containt == 'Y' && isset($infoAllBundle->$info)) {

                          if ($value == "T") {
                            $objImei = new \stdClass;
                            $objImei->imei = $request->imeiPhone;
                            $objImei->brand = $request->imeiBrand;
                            $objImei->model = $request->imeiModel;
                          } else {
                            $objImei = null;
                          }
                          $isBandTE = ($value == "T") ? $request->isBandTE : null;

                          $data = [
                            'installations_id' => $install->id,
                            'dn_type' => $value,
                            'msisdn_parent' => null,
                            'pack_id' => $infoAllBundle->$info->id,
                            'service_id' => $infoAllBundle->$info->service_id,
                            'inv_article_id' => $infoAllBundle->$info->product_id,
                            'info_imei' => !empty($objImei) ? (String) json_encode($objImei) : $objImei,
                            'isBandTE' => $isBandTE,
                            'date_reg' => date('Y-m-d H:i:s'),
                          ];
                          InstallationsBundle::registerChildrenBundler($data);
                        }
                      }
                    } else {
                      $Txmsg = 'La cita de instalación fue registrada pero se presento un problema en obtener la información de los componentes del bundle. (CF1195)';
                      Log::error($Txmsg);
                      return response()->json(['success' => false, 'code' => 'EMP_BUN', 'msg' => $Txmsg]);
                    }
                  }
                  //Envio SMS informativo por zona
                  //
                  if (!empty($client->phone_home)) {
                    $Infomsj = FiberZone::getInfoZone($request->zone_id);
                    $configuration = json_decode(json_encode($Infomsj->configuration));
                    $smsCita = '';
                    if (isset($configuration->smsCita)) {
                      if (!empty($configuration->smsCita)) {
                        $smsCita = $configuration->smsCita;
                      }
                    }

                    /*Altan::sendSms([
                    "msisdn" => $client->phone_home,
                    "phone_sms" => $client->phone_home,
                    "service" => "Cita de instalacion",
                    "concentrator" => 1,
                    "type_sms" => "O",
                    "sms_attrib" => "SMSCITAFIBRA",
                    "smsCita" => $smsCita]);*/
                    if (!empty($smsCita)) {

                      $smsCita = customerSMS(
                        $smsCita,
                        false,
                        $client->name,
                        false,
                        $packprice->service_id,
                        false,
                        $dateIns->format('d-m-Y') . ' en horario ' . $request->hour
                      );
                      try {
                        Sms_notification::Send_sms(
                          $client->phone_home,
                          $client->phone_home,
                          'F',
                          '1',
                          "Cita de instalacion",
                          "SMSCITAFIBRA",
                          $smsCita);
                      } catch (Exception $e) {
                        Log::error('No se pudo enviar el msj de guardado de cita al telefono del cliente: ' . $client->phone_home . ' Error: ' . (String) json_encode($e->getMessage()));
                      }

                    } else {
                      Log::error("No se envio sms de notificacion de cita al cliente (" . $request->client . "), la zona (" . $request->zone_id . ") no lo tiene configurado.");
                    }
                  } else {
                    Log::error("El cliente (" . $request->client . ") no tiene configurado un telefono de contacto para el envio de sms.");
                  }
                  return response()->json(['success' => true, 'code' => 'OK']);
                } else {
                  $response['msg'] = 'Vendedor o instalador no válido. (CF1252)';
                }
              } else {
                $response['msg'] = 'Plan no válido, no se consiguio el artículo. (CF1255)';
              }
            } else {
              $response['msg'] = 'Plan no válido, no se consiguio el servicio. (CF1258)';
            }
          } else {
            $response['msg'] = 'Plan no válido. (CF1261)';
          }
        } else {
          $response['msg'] = 'Cliente no registrado. (CF1264)';
        }
      } else {
        $textAux = 'Por favor verificar los datos';

        $response['msg'] = !empty($respCoverage['msg']) ? $respCoverage['msg'] . '. ' . $textAux : $textAux;
      }
      $response['code'] = 'ERR';
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [detailInsModal Retorna la informacion en el dasbord del instalador acerca del detalle de una cita por instalar]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function detailInsModal(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo cargar el detalle de la instalación.'];

      if (!empty($request->id) && !empty($request->type)) {
        $data = Installations::getDateDetailByID($request->id);

        if (!empty($data) && ($data->installer == session('user') || $data->installer_boss == session('user'))) {

          $type = $request->type;
          $bundlePack = null;
          $infoPack = null;

          $statusPlan = ['A', 'I'];
          //Se hizo por temas de inactividad del plan luego que se confirmo la cita
          if (!empty($data->bundle_id)) {
            //info del bundle
            $plan = Bundle::getDetailBundleAlta($data->bundle_id, $statusPlan);

            if ($plan['success']) {
              $bundlePack = json_decode(json_encode($plan['data']));

            } else {
              $response = ['error' => true, 'message' => $plan['data']];
              return response()->json($response);
            }
          } else {
            $infoPack = Pack::getPlanDetailFiber($data->pack_id, $statusPlan);
          }
          $view = view('dashboard.detail_install', compact('data', 'type', 'bundlePack', 'infoPack'))->render();

          $response = ['error' => false, 'html' => $view, 'date_ins' => $data->date_instalation, 'register' => $request->id];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function detailPendingPaidInsModal(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo cargar el detalle de la instalación.'];

      if (!empty($request->id)) {
        $data = Installations::getDateDetailByID($request->id);

        if (!empty($data) && $data->seller == session('user')) {
          $view = view('dashboard.detail_pending_paid', compact('data'))->render();
          $response = ['error' => false, 'html' => $view];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function markAsPaidInstall(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo marcar como pagada la instalación.'];

      if (!empty($request->id)) {
        $rtins = Installations::markAsPaid($request->id);
        if (!$rtins['success']) {
          $response = ['error' => true, 'message' => $rtins['msg'], 'id' => $request->id];
        } else {
          $response = ['error' => false, 'message' => 'Solicitud marcada como pagada exitosamente.', 'id' => $request->id];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [saveDetailInsModal Guarda los detalles de la cita que se actualizaron por parte del jefe de zona]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function saveDetailInsModal(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'msg' => 'La informacion de la cita no pudo ser guardada', 'icon' => 'warning'];

      if (!empty($request->cita) && !empty($request->installer) && !empty($request->vtype)) {
        //Consultando la cita
        $inst = Installations::getInstallToEdit($request->cita);

        if (!empty($inst) && $inst->status == 'A') {

          if (($request->vtype == 'installerAgenda' || $request->vtype == 'installerAsigne') && $request->installer != $inst->installer) {
            try {
              $inst->user_mod = session('user');
              $inst->date_mod = date('Y-m-d H:i:s');
              $inst->installer = $request->installer;
              $inst->status_control = 'AI';
              $inst->save();
            } catch (Exception $e) {
              $txMsg = 'Error al actualizar la cita de instalacion. (1037) ' . (String) json_encode($e->getMessage());
              Log::error($txMsg);
              $response = ['success' => false, 'msg' => $txMsg, 'icon' => 'warning'];
              return response()->json($response);
            }

            //Se guarda el historial del movimiento de cita
            $insH = History_control::insertHistory($request->installer, $request->cita, 'AI');

            if (!$insH['success']) {
              $response = ['success' => false, 'msg' => $insH['msg'], 'icon' => 'warning'];
            } else {
              $response = ['success' => true, 'msg' => 'El instalador ha sigo asignada con exito a la cita', 'icon' => 'success'];
            }
          } elseif ($request->vtype == 'installerCancel') {
            //Se envio a cancelar la cita
            $response = ['success' => true, 'msg' => 'La cita ha sigo cancelada y enviada a la mesa de control con exito', 'icon' => 'success'];

          } elseif ($request->installer == $inst->installer) {
            $response = ['success' => true, 'msg' => '', 'icon' => ''];
          }
        }
/*
$inst2 = $inst->replicate();
$inst2->father_install = $inst->id;
$inst2->num_rescheduling = $inst->num_rescheduling + 1;
$inst2->date_reg = date('Y-m-d H:i:s');
$inst2->status = 'A';

if (!empty($request->installer)) {
$inst2->installer = $request->installer;

if ($request->installer != session('user')) {
$response['delete'] = true;
}
} else {
$inst2->installer = $inst->installer;
}

if (!empty($request->date) && !empty($request->hour)) {
$dateIns = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay();
$inst2->date_instalation = $dateIns->format('Y-m-d');
$inst2->schedule = $request->hour;
$response['date'] = $dateIns->format('d-m-Y');
$response['schedule'] = $request->hour;
} else {
$inst2->date_instalation = $inst->date_instalation;
}

$inst2->save();

$response['prev_id'] = $inst->id;
$response['new_id'] = $inst2->id;
$response['error'] = false;
$response['message'] = 'Cita editada exitosamente.';
} else {
$response['error'] = false;
$response['message'] = 'No hay cambios que aplicar a la cita.';
$response['delete'] = false;
}
} else {
$response['message'] = 'No tienes permisos para editar esta cita.';
}*/
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function deleteInstall(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo eliminar la cita de instalación.'];

      if (!empty($request->id)) {
        $inst = Installations::getInstallToEdit($request->id);

        if (!empty($inst) && $inst->installer == session('user') && $inst->status == 'A' && $inst->paid == 'N') {

          try {
            $inst->status = 'T';
            $inst->reason_delete = $request->reason_delete;
            $inst->user_mod = session('user');
            $inst->date_mod = date('Y-m-d H:i:s');
            $inst->save();

          } catch (Exception $e) {
            $txMsg = 'Error al actualizar la cita de instalacion. (CF1367) ' . (String) json_encode($e->getMessage());
            Log::error($txMsg);
            $response = ['error' => true, 'message' => $txMsg, 'icon' => 'warning', 'id' => $request->id];
            return response()->json($response);
          }

          try {
            Installations::getConnect('W')
              ->where([
                ['group_install', $inst->group_install],
                ['status', 'A']])
              ->update([
                'status' => 'T']);
          } catch (Exception $e) {
            $txMsg = 'Error al actualizar la cita de instalacion. (1138) ' . (String) json_encode($e->getMessage());
            Log::error($txMsg);
            $response = ['error' => true, 'message' => $txMsg, 'icon' => 'warning', 'id' => $request->id];
            return response()->json($response);
          }
          $response = ['error' => false, 'message' => 'Cita eliminada exitosamente.', 'icon' => 'success', 'id' => $request->id];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function loadMoredetailInsModal(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo cargar la información.'];

      if (!empty($request->date) && !empty($request->dateB)) {
        $dates = Installations::getPendingDatesForInstaller(session('user'), $request->dateB, $request->date);

        if ($dates->count()) {
          $html = view('dashboard.datesInstall', compact('dates'))->render();

          $nextWeek = date('Y-m-d', strtotime('+7 days', strtotime($request->date))) . ' 23:59:59';
          $nextED = Installations::getPendingDatesForInstaller(session('user'), $request->date, $nextWeek);

          $response['showNextInstalations'] = false;
          if ($nextED->count()) {
            $response['showNextInstalations'] = $nextWeek;
            $response['datesInstalationsB'] = $request->date;
          }
          $response['error'] = false;
          $response['html'] = $html;
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [doInstall Vista que instalador usa para ingresar la configuracion del equipo a ser instalado y donde se agrega la posibilidad de cambiar el plan a suscripcion si desde la cita no se lo configuraron]
 * @param  boolean $id [description]
 * @return [type]      [description]
 * Es un llamado GET
 */
  public function doInstall($id = false)
  {
    if ($id) {
      $data = Installations::getDateDetailByID($id);
      if (!empty($data)) {
        $tempZone = FiberZone::getInfoZone($data->id_fiber_zone);

        $data->fiber_zone_name = $tempZone->name;
        $configuration = json_decode(json_encode($tempZone->configuration));

        $data->owner = $configuration->owner;
        $data->collector = $configuration->collector;
        // $data->sms    = $tempZone->configuration->sms;

        //Articulo de fibra asignado para instalar en la zona
        $arti_install_zone = FiberArticleZone::getArticleZone($data->id_fiber_zone, $data->inv_article_id);

        if (!empty($data) && $data->installer == session('user') and
          $data->status == 'A') {
          $countDns = Inventory::getDNsByArticAndAssign($data->inv_article_id, $data->id_fiber_zone, session('user'))->count();

          $infozone = FiberZone::getInfoZone($data->id_fiber_zone);
          $param = json_decode(json_encode($infozone->param));
          $NodoRed = $param->nodo_de_red;

          //Se revisa que tipo de servicio se marco en la cita para conocer si se puede ofrecer un servicio con pago recurrente. Si se puede ofrecer suscripcion verificar si hay alguno configurado con la modalidad de contrato y la olt donde sera instalador.
          //
          //En caso de que el servicio se desactivo despues de creada la cita permita instalarla
          $service = Service::getService($data->service_id, ['A', 'I']);
          $htmlPack = ''; //Select de planes de subscription
          $QrPayment = ''; //Qr de pago
          $QrPaymentClass = ""; //Clases del qr en caso de error
          $htmlPackSelect = ''; //Se selecciono un plan subscrito que aun no se ha cobrado
          $QrPaymentCode = "OK";

          $plan = null;
          $bundle = 'N';
          $habilityPortability = false;
          $companys = null;
          $disposeInvT = [];
          $obj_bundle = null;

          if (!empty($service)) {

            if ($service->for_subscription == 'N') {
              //El instalador tiene posibilidad de dar un plan subscrito
              if (!empty($data->payment_url_subscription)) {
                //Ya inicio un proceso de pago con un plan que trato de cambiar, reviso que plan era
                //
                if ($service->is_bundle == 'N') {
                  //Mostramos el plan que viene desde la cita de fibra normal
                  $plan = Pack::getPlanDetailFiber($data->pack_id, ['A', 'I']);

                } else {
                  //Mostramos el plan que viene desde la cita de fibra bundle
                  $InfoBundle = Bundle::getDetailBundleAlta($data->bundle_id, ['A', 'I']);
                  if ($InfoBundle['success']) {
                    $plan = json_decode(json_encode($InfoBundle['data']));
                    //Log::info('>> bundler configurado ' . (String)json_encode($plan));
                    $bundle = 'Y';
                    $obj_bundle = "";
                  } else {
                    $response = ['error' => true, 'message' => $InfoBundle['data']];
                    $searchPlan = false;
                    $htmlPackSelect = "Se presento un problema en obtener el detalle del bundle de susbcripcion ofrecido. (CF1599) Info: " . $InfoBundle['data'];
                  }
                }

                if (!empty($data->pack_price_id) ||
                  !empty($data->bundle_id_payment)) {

                  $searchPlan = true;
                  $packprice = null;

                  if (!empty($data->pack_price_id)) {
                    //Es un plan normal a pagar y que refresco solo la vista
                    //En caso de que el servicio se desactivo despues de creada la cita permita instalarla
                    $packprice = PackPrices::getPackPriceDetail($data->pack_price_id, ['A', 'I']);

                    if (!empty($packprice)) {
                      $plan_payment = Pack::getPlanDetailFiber($packprice->pack_id, ['A', 'I']);
                      if (empty($plan_payment)) {
                        $searchPlan = false;
                        $htmlPackSelect = "Se presento un problema en obtener el del nuevo plan de subscripción ofrecido (CF1618)";
                      }
                    } else {
                      $searchPlan = false;
                      $htmlPackSelect = "Se presento un problema en obtener el detalle del nuevo plan de subscripción ofrecido (CF1622)";
                    }
                  } elseif (!empty($data->bundle_id_payment)) {
                    //Es un plan bundle que refresco solo la vista
                    $InfoBundle = Bundle::getDetailBundleAlta($data->bundle_id_payment, ['A', 'I']);

                    if ($InfoBundle['success']) {
                      $plan_payment = json_decode(json_encode($InfoBundle['data']));
                      //Log::info('>> bundler a pagar ' . (String) json_encode($plan_payment));
                      $bundle = 'Y';
                      $obj_bundle = "";
                    } else {
                      $response = ['error' => true, 'message' => $InfoBundle['data']];
                      $searchPlan = false;
                      $htmlPackSelect = "Se presento un problema en obtener el detalle del bundle de susbcripcion ofrecido. (CF1636) Info: " . $InfoBundle['data'];
                    }
                  } else {
                    $searchPlan = false;
                    $htmlPackSelect = "Se presento un problema en obtener el detalle del bundle de susbcripcion ofrecido. (CF1640)" . $InfoBundle['data'];
                  }

                  if ($searchPlan) {
                    $request = new \stdClass;
                    $request->id = $id;
                    if (!empty($packprice)) {
                      $request->plan = $packprice->pack_id;
                    } else {
                      $request->plan = null;
                    }
                    $request->bundle_id = $data->bundle_id_payment;

                    $infoQRPayment = self::processUrlPayment($request, ['A', 'I']);

                    //Log::info('CREACION DEL PLAN Y DEL PLAN A COBRAR');
                    // Log::info('PLAN ' . (String) json_encode($plan) . ' PAYMENT ' . (String) json_encode($plan_payment));

                    $htmlPackSelect = view('fiber.plan', compact('plan', 'bundle', 'plan_payment'))->render();
                    $QrPayment = ($infoQRPayment['success']) ? $infoQRPayment['html'] : $infoQRPayment['msg'];
                    $QrPaymentClass = ($infoQRPayment['success']) ? "" : "alert alert-danger";
                    $QrPaymentCode = ($infoQRPayment['success']) ? "" : $infoQRPayment['code'];
                  }
                } else {
                  $htmlPackSelect = "La cita de instalación no posee registrado el paquete de subscripción ofrecido por el instalador";
                }
              } else {
                //Revisamos si hay planes de fibra Activos con subscripcion
                //Los planes que se deben mostrar deben tener la misma configuracion del plan que se tiene actualmente: es decir si es bundle que contenga los mismo articulos y que sea del mismo tipo de contrato e indiscutiblemente sea de subscripcion
                $packs = Pack::getFiberPacks($data->id_fiber_zone, false, $service->is_payment_forcer, 'Y', $service->is_bundle, $data->bundle_id);

                if (!empty($packs)) {
                  $view = "installer";
                  $htmlPack = view('fiber.Select_plan',
                    compact('packs', 'view'))->render();
                }
              }
            } else {
              //Desde la cita definio pago recurrente
              $request = new \stdClass;
              $request->id = $data->id;
              $request->plan = $data->pack_id;
              $request->bundle_id = $data->bundle_id;

              $infoQRPayment = self::processUrlPayment($request, ['A', 'I']);

              if ($infoQRPayment['success']) {
                $QrPayment = $infoQRPayment['html'];
                $QrPaymentClass = "";
                $QrPaymentCode = "";
              } else {
                $QrPayment = $infoQRPayment['msg'];
                $QrPaymentClass = "alert alert-danger";
                $QrPaymentCode = $infoQRPayment['code'];
              }
              //  $QrPayment = "VERE EL QR DE PAGO DESDE CITA";
            }

            if (!isset($plan)) {
              //Se debe evaluar que el plan a instalar corresponde a un bundle
              if (!empty($data->bundle_id)) {
                $InfoBundle = Bundle::getDetailBundleAlta($data->bundle_id, ['A', 'I']);

                if ($InfoBundle['success']) {
                  //Log::info('Es un bundle CF1597');
                  $plan = json_decode(json_encode($InfoBundle['data']));
                  $bundle = 'Y';

                } else {
                  $response = ['error' => true, 'message' => $InfoBundle['data']];
                  $htmlPackSelect = "Se presento un problema en obtener el detalle del bundle ofrecido. Info: " . $InfoBundle['data'];
                }
              } else {
                //Es un pack individual
                //En caso de que el servicio se desactivo despues de creada la cita permita instalarla

                $plan = Pack::getPlanDetailFiber($data->pack_id, ['A', 'I']);
              }
              //end Bundle
            }

            if ($bundle == 'Y' && isset($plan->general->containt_T)) {
              //Log::info('PLAN BUNDLE ' . (String) json_encode($plan));
              $obj_bundle = new \stdClass;
              $obj_bundle->id = $data->bundle_id;
              //Log::info('ID bundle CF1617 ' . $obj_bundle->id);

              if ($plan->general->containt_T == 'Y') {
                //Se obtiene los detalles del equipo de telefonia que verifico compatibilidad

                $habilityPortability = true;
                $companys = TelephoneCompany::getCompanys();

                if (isset($plan->info_T->id)) {
                  //verifico el inventario del instalador para agregar el dn de telefonia
                  $email = [session("user")];
                  $disposeInvT = User::FilterInstallerInventary($email, $plan->info_T->id);
                }

                $childrenT = InstallationsBundle::getChildrenBundle($id, 'T');

                $obj_bundle->children_T = new \stdClass;

                if ($plan->info_T->category_id == 2) {
                  //Simcard (informo el equipo al cual se verifico compatiblidad)
                  $obj_bundle->children_T->imei = '';

                  foreach ($childrenT as $item) {

                    $infoImei = json_decode(json_encode($item->info_imei));

                    //$objImei->imei
                    //$objImei->brand
                    //$objImei->model

                    $cadenaItem = "IMEI no encontrado...";
                    if (isset($infoImei->brand) && isset($infoImei->model) && isset($infoImei->imei)) {
                      $cadenaItem = $infoImei->brand . ' ' . $infoImei->model . ' (' . $infoImei->imei . ')';
                    }

                    $obj_bundle->children_T->imei .= (!empty($obj_bundle->children_T->imei)) ? ', ' . $cadenaItem : $cadenaItem;
                  }
                } else {
                  //SmartPhone
                  //No se muestra el imei verificado ya que el celular que se entrega es compatible
                  $obj_bundle->children_T->imei = null;
                }
              } else {
                $obj_bundle->children_T = null;
              }
            }
          } else {
            Log::alert('No hay servicio en el sistema para procesar la instalacion (CF1765)' . $id);
          }
          return view('fiber.install', compact('data', 'countDns', 'arti_install_zone', 'NodoRed', 'htmlPack', 'QrPayment', 'QrPaymentClass', 'htmlPackSelect', 'QrPaymentCode', 'plan', 'bundle', 'habilityPortability', 'companys', 'disposeInvT', 'obj_bundle'));
        }
      }
      return view('fiber.install', compact('data'));
    }
    return redirect()->route('dashboard');
  }

  public function getMSISDNSFiber(Request $request)
  {
    if (!empty($request->search)) {
      $data = Installations::getInstallById($request->id);

      if (!empty($data) && $data->installer == session('user')) {
        return response()->json(Inventory::getDNsByArticAndAssign($data->inv_article_id, $data->id_fiber_zone, session('user')));
      }
    }
    return response()->json([]);
  }

/**
 * [ChekingBundleSales Verifica si se cuenta en el sistema de netwey con el producto para ser entregado en el alta de tipo bundle]
 * @param [type] $confBundle [description]
 * @param [type] $request    [description]
 * @param [type] $typeArt    [description]
 */
  private function ChekingBundleSales($confBundle, $request, $typeArt)
  {
    //Aca se buscan cada componente del bundle execto el dn de fibra ya que fibra es el master
    $contend = "containt_" . $typeArt;
    if ($confBundle->$contend == 'Y') {
      $total_up = "total_up_" . $typeArt;

      for ($i = 1; $i <= $confBundle->$total_up; ++$i) {
        $inv_bundle = "inv_bundle_" . $typeArt . $i;

        //Log::info($confBundle->$contend . ' ' . $typeArt);
        //el alta posee el articulo de type definido
        if (isset($request->$inv_bundle) &&
          !empty($request->$inv_bundle)) {
          $detailInv_sale = Inventory::getDnsById($request->$inv_bundle);
          if (!empty($detailInv_sale)) {
            //se busca el Dn basado el id del ineventario
            $dnDetail_sale = Inventory::getDataDn($detailInv_sale->msisdn);
            //Se obtiene los ids de los productos que forman el bundle para la instalacion en cuestion
            $childrenBundle_sale = InstallationsBundle::getChildrenBundle($request->id, $typeArt);

            if (count($childrenBundle_sale) > 0) {
              if (!empty($dnDetail_sale) &&
                $dnDetail_sale->inv_article_id == $childrenBundle_sale[0]->inv_article_id &&
                $dnDetail_sale->status == 'A') {
                return ['error' => false, 'message' => "El articulo de tipo '" . $typeArt . "'' es OK", 'code' => 'OK', 'icon' => 'success'];

              } else {
                return ['error' => true, 'message' => "El articulo de tipo '" . $typeArt . "': " . $detailInv_sale->msisdn . " no esta disponible para usarse para el alta del bundle cita: (" . $request->id . ")", 'code' => 'ERR_BUN', 'icon' => 'warning'];
              }
            } else {
              return ['error' => true, 'message' => "El alta corresponde a un bundle pero los dns que conformaran dicho bundle de tipo
              '" . $typeArt . "' no estan registrados (CF1714)", 'code' => 'ERR_BUN', 'icon' => 'warning'];
            }
          } else {
            return ['error' => true, 'message' => "El articulo de tipo
              '" . $typeArt . "' no se encuentra en el sistema (CF1718)", 'code' => 'ERR_BUN', 'icon' => 'warning'];
          }
        } else {
          return ['error' => true, 'message' => "El alta corresponde a un bundle pero no se informo el articulo de tipo '" . $typeArt . "' a entregar (1697)", 'code' => 'ERR_BUN', 'icon' => 'warning'];
        }
      }
    }
    return ['error' => false, 'message' => "El articulo de tipo '" . $typeArt . "' no forma parte del bundle", 'code' => 'OK', 'icon' => 'success'];
  }

/**
 * [ActivateService Procesa el alta de un servicio de bundle]
 */
  private function ActivateService($type, $request, $confBundle, $unique)
  {
    $containt = "containt_" . $type;
    //Log::info('type ' . (String) json_encode($type));
    // Log::info('condicion: ' . $confBundle->general->$containt);
    if ($confBundle->general->$containt == 'Y') {
      //OJO: Si se quieren dar de alta N equipos del mismo tipo esta variable se debe colocar de forma iterativa
      $infoArt = "info_" . $type;
      $total_up = "total_up_" . $type;

      $typeProduct = [
        'T' => 'mov',
        'T' => 'mov-ph',
        'H' => 'home',
        'M' => 'mifi',
        'MH' => 'mifi-h'];

      for ($i = 1; $i <= $confBundle->general->$total_up; ++$i) {
        //tenemos los ids del inventario que el instalador selecciono y seran entregado el cliente
        $inv_bundle = "inv_bundle_" . $type . $i;

        if (isset($request->$inv_bundle)) {

          $detailInv_sale = Inventory::getDnsById($request->$inv_bundle);
          if (!empty($detailInv_sale) && !empty($confBundle->$infoArt)) {
            $infoChildrenBundle = InstallationsBundle::getChildrenBundle($request->id, $type, "one", 'P');

            if (!empty($infoChildrenBundle)) {
              $msisdnSlave = $detailInv_sale->msisdn;

              $plan = Pack::getInfoPack($confBundle->$infoArt->id, $confBundle->$infoArt->service_pay);

              $service = Service::getService($confBundle->$infoArt->service_id);

              $infoInstall = Installations::getInstallById($request->id);

              if (!empty($infoInstall)) {
                $objClient = Client::getClientINEorDN($infoInstall->clients_dni);
              } else {
                $msg = 'El objeto cliente no fue encontrado para el alta bundle';
                Log::error($msg);
                return ['success' => false, 'message' => $msg];
              }

              if (empty($plan) || empty($service)) {
                $msg = 'No se pudo dar el alta de bundle ante altan, faltan datos del servicio o del paquete';
                Log::error($msg);
                return ['success' => false, 'message' => $msg];
              }

              $imeiCel = '';
              if (!empty($infoChildrenBundle->info_imei)) {
                $infoImei = json_decode(json_encode($infoChildrenBundle->info_imei));
                $imeiCel = $infoImei->imei;
                $objImei = false;
              } else {
                $objImei = new \stdClass;
                $objImei->imei = $detailInv_sale->imei;
                $objImei->brand = $detailInv_sale->brand;
                $objImei->model = $detailInv_sale->model;
              }

              $imeiSale = !empty($imeiCel) ? $imeiCel : $detailInv_sale->imei;

              $altaArt = ProcessRegAlt::doProcessRegAlt(
                $typeProduct[$type], /*1*/
                $msisdnSlave, /*2*/
                false, /*3*/
                false, /*4*/
                false, /*5*/
                $service, /*6*/
                $detailInv_sale, /*7*/
                $confBundle->$infoArt->service_pay, /*8*/
                $unique, /*9*/
                $objClient, /*10*/
                $plan, /*11*/
                $request->isPort, /*12*/
                !empty($request->port_nip) ? $request->port_nip : false, /*13*/
                !empty($request->port_dn) ? $request->port_dn : false, /*14*/
                !empty($request->port_supplier_id) ? $request->port_supplier_id : false, /*15*/
                false, /*16*/
                false, /*17*/
                $imeiSale, /*18*/
                'C', /*19*/
                !empty($infoChildrenBundle->isBandTE) ? $infoChildrenBundle->isBandTE : false, /*20*/
                false, /*21*/
                false, /*22*/
                false, /*23*/
                false, /*24*/
                false/*25*/
              );

              if ($altaArt['success']) {
                //Actualizo que se proceso el hijo del bundle
                //$infoChildrenBundle->status = 'P';
                $statusNew = 'P';
                $Obs = false;

                sleep(1);
                //esperamos un segundo que se guardo el registro
                if (ClientNetwey::isClient($msisdnSlave)) {
                  //Buscamos el hijo del bundle para referenciar al padre del bundle
                  $infoMaster = ClientNetwey::getRegisterBundle($request->msisdn);

                  if (!empty($infoMaster)) {
                    $upcli = ClientNetwey::setRegisterBundle($msisdnSlave, $infoMaster);
                    if (!$upcli['success']) {
                      Log::error('El dn ' . $msisdnSlave . ' correspondiente a la instalacion bundle (' . $request->id . ') no pudo ser actualizado el msisdn_master');
                    }
                  } else {
                    Log::error('El dn de fibra ' . $request->msisdn . ' corresponde a una instalacion bundle pero no posee referencia del cliente_bundle para agregarlo a sus hijos');
                  }
                } else {
                  Log::error('El dn ' . $msisdnSlave . ' correspondiente a la instalacion bundle (' . $request->id . ') que se activo no se encuentra registrado aun como cliente');
                }
              } else {
                //El alta del servicio fallo
                //  $infoChildrenBundle->obs = (String) json_encode($altaArt);
                $statusNew = 'A';
                $Obs = (String) json_encode($altaArt);
              }
              try {
                if (filter_var($request->isPort, FILTER_VALIDATE_BOOLEAN)) {
                  $infoPort = new \stdClass;
                  $infoPort->port_dn = $request->port_dn;
                  $infoPort->port_nip = $request->port_nip;
                  $infoPort->port_supplier_id = $request->port_supplier_id;
                } else {
                  $infoPort = false;
                }

                $insertCron = InstallationsBundle::CompletedDataBundle(
                  $infoChildrenBundle->children_id,
                  $request->msisdn,
                  $request->$inv_bundle,
                  $infoPort,
                  $unique,
                  $objImei,
                  $confBundle->$infoArt->service_pay,
                  $statusNew,
                  $Obs
                );
              } catch (Exception $e) {
                $msg = "Se activo ante Altan el servicio (" . $type . ") de la cita bundle (" . $request->id . "), pero no se pudo actualizar los registros. +info: " . (String) json_encode($e->getMessage());
                Log::error($msg);
              }
              return $altaArt;
            } else {
              $msg = "Nota: La cita de instalacion (" . $request->id . ") el servicio de Fibra se dio de alta correctamente, pero no se pudo obtener los servicios bundle (" . $type . ") asociados al alta";
              Log::error($msg);
              return ['success' => false, 'message' => $msg];
            }
          } else {
            $msg = "El servicio de Fibra se dio de alta correctamente, pero el paquete(s) de tipo (" . $type . ") del bundle presento un problema, no se encuentra en inventario el articulo seleccionado o no hay pre-registro de un bundle de instalacion";
            Log::error($msg);
            return ['success' => false, 'message' => $msg];
          }
        } else {
          Log::alert('Se espera recibir la variable ' . $inv_bundle . ' pero no esta definida para la cita de fibra ' . $request->id);
        }
      }
    } else {
      return ['success' => true, 'message' => "OK"];
    }
  }

  private function RegisterChildrenAltaBundle($type, $request, $confBundle, $unique)
  {
    $containt = "containt_" . $type;

    if ($confBundle->general->$containt == 'Y') {
      //OJO: Si se quieren dar de alta N equipos del mismo tipo esta variable se debe colocar de forma iterativa
      $infoArt = "info_" . $type;
      $total_up = "total_up_" . $type;

      $failInsert = "";
      for ($i = 1; $i <= $confBundle->general->$total_up; ++$i) {
        //tenemos los ids del inventario que el instalador selecciono y seran entregado el cliente
        $inv_bundle = "inv_bundle_" . $type . $i;

        if (isset($request->$inv_bundle)) {

          $detailInv_sale = Inventory::getDnsById($request->$inv_bundle);
          if (!empty($detailInv_sale) && !empty($confBundle->$infoArt)) {
            //para este punto la instalacion de fibra fue procesada o esta en espera de cron
            $infoChildrenBundle = InstallationsBundle::getChildrenBundle($request->id, $type, "one", ['P', 'EC', 'E', 'PA']);

            if (!empty($infoChildrenBundle)) {

              try {
                if (filter_var($request->isPort, FILTER_VALIDATE_BOOLEAN)) {
                  $infoPort = new \stdClass;
                  $infoPort->port_dn = $request->port_dn;
                  $infoPort->port_nip = $request->port_nip;
                  $infoPort->port_supplier_id = $request->port_supplier_id;
                } else {
                  $infoPort = false;
                }

                $imeiCel = "";
                if (isset($infoChildrenBundle->info_imei)) {
                  //Cuando no se cargo el dato del equipo que se verifico se busca en inventario
                  $infoImei = json_decode(json_encode($infoChildrenBundle->info_imei));
                  if (isset($infoImei->imei)) {
                    $imeiCel = $infoImei->imei;
                  }
                }

                if (empty($infoChildrenBundle->info_imei) || empty($imeiCel)) {
                  //El imei de telefonia ya fue cargado en la cita
                  $objImei = new \stdClass;
                  $objImei->imei = $detailInv_sale->imei;
                  $objImei->brand = $detailInv_sale->brand;
                  $objImei->model = $detailInv_sale->model;
                } else {

                  $objImei = false;
                }

                $insertCron = InstallationsBundle::CompletedDataBundle(
                  $infoChildrenBundle->children_id,
                  $request->msisdn,
                  $request->$inv_bundle,
                  $infoPort,
                  $unique,
                  $objImei,
                  $confBundle->$infoArt->service_pay,
                  'EC',
                  false
                );

                if ($insertCron['success']) {
                  //marco el inventario en un status intermedio de venta para evitar movimientos en netwey mientras se ejecuta el cron del bundle
                  Inventory::markArticleSale($request->$inv_bundle, 'E', 'Reservado para activacion en instalacion bundle');
                }

              } catch (Exception $e) {
                $Txmsg = 'Error al actualizar el hijo del bundle de instalación id:' . $request->id . ' (CF1958). ' . (String) json_encode($e->getMessage());
                Log::error($Txmsg);
                $failInsert .= $detailInv_sale->msisdn . ',';
              }

            } else {
              $msg = "Nota: La cita de instalación (" . $request->id . ") el servicio de Fibra se registro para un alta asincrona, pero no se pudo obtener los servicios bundle (" . $type . ") asociados al alta";
              Log::error($msg);
              return ['success' => false, 'message' => $msg];
            }
          } else {
            $msg = "El servicio de Fibra se registro para un alta asincrona, pero el paquete(s) de tipo (" . $type . ") del bundle presento un problema, no se encuentra en inventario el articulo seleccionado o no hay pre-registro de un bundle de instalacion";
            Log::error($msg);
            return ['success' => false, 'message' => $msg];
          }
        } else {
          Log::alert('Se espera recibir la variable ' . $inv_bundle . ' pero no esta definida para la cita de fibra ' . $request->id);
        }
      }
      if (!empty($failInsert)) {
        $failInsert = "Fallo el registo en BD de: " . $failInsert;
      }
      return ['success' => (empty($failInsert)) ? true : false, 'message' => (empty($failInsert)) ? 'OK' : $failInsert];
    } else {
      return ['success' => true, 'message' => "Registro de alta asincrona de fibra exitosa!"];
    }
  }
/**
 * [doRegister Procesa la solicitud de alta ante 815]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function doRegister(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['error' => true, 'title' => "No se pudo procesar la activación de fibra.", 'message' => "***", 'icon' => "warning", 'code' => 'FAIL', 'success' => false];

      //el ID del inventario es opcional..
      //inv_bundle_T(n)
      //inv_bundle_M(n)
      //inv_bundle_MH(n)
      //inv_bundle_H(n)
      /////Nueva forma
      $messages = [
        'id.required' => 'El id de instalacion de fibra es requerido (IVF2000)',
        'id.regex' => 'El id no cumple el criterio de ser un valor valido (IVF2001)',
        'msisdn.required' => 'El msisdn de fibra es requerido (IVF2002)',
        'msisdn.regex' => 'El msisdn no cumple el criterio de ser un msisdn valido (IVF2003)',
        'nodo.required' => 'El nodo de conexion de fibra es requerido (IVF2004)',
        'nodo.regex' => 'El Nodo no cumple el criterio de ser un valor valido (IVF2005)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'msisdn' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'nodo' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'nodo_name' => array(
          'required',
        ),
      ];
      if (isset($request->isPort)) {
        if (filter_var($request->isPort, FILTER_VALIDATE_BOOLEAN)) {
          $messages['isPort.required'] = 'Se debe especificar si el alta sera o no con portabilidad (IVF2026)';
          $messages['port_dn.required'] = 'Se debe especificar el msisdn a someter a portabilidad (IVF2027)';
          $messages['port_dn.regex'] = 'El msisdn a portar no cumple el criterio de ser un msisdn valido (IVF2028)';
          $messages['port_nip.required'] = 'Se debe especificar el nip de la portabilidad (IVF2029)';
          $messages['port_nip.regex'] = 'El nip no cumple el criterio de ser un valor valido (IVF2030)';
          $messages['port_supplier_id.required'] = 'Se debe especificar el operador origen (IVF2031)';
          $messages['port_supplier_id.regex'] = 'El operador de origen no cumple el criterio de ser un valor valido (IVF2032)';

          $requisitos['isPort'] = array(
            'required',
          );

          $requisitos['port_dn'] = array(
            'required',
            'regex:/(^[0-9]{10}$)/u',
          );

          $requisitos['port_nip'] = array(
            'required',
            'regex:/(^[0-9]{4}$)/u',
          );

          $requisitos['port_supplier_id'] = array(
            'required',
            'regex:/(^([0-9]+)(\d+)?$)/u',
          );
        }
      }

      if (isset($request->is_bundle)) {
        //Cuando se programe  el multi poducto se debe evaluar la construccion dinamica de la validacion
        if ($request->is_bundle == 'Y') {
          $messages['inv_bundle_T1.required'] = 'Se debe especificar el DN de telefonia a dar de alta (IVF2058)';
          $messages['inv_bundle_T1.regex'] = 'El id del equipo de telefonia no cumple el criterio de ser un valor valido (IVF2059)';

          $requisitos['inv_bundle_T1'] = array(
            'required',
            'regex:/(^([0-9]+)(\d+)?$)/u',
          );
        }
      }

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['message'] = "";
        foreach ($errors->all() as $error) {
          $response['message'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //Motivado que los paquetes pueden darse de baja luego que se captura la cita se permite en estatus A e I
      $statusList = ['A', 'I'];

      if (!empty($request->id) && !empty($request->msisdn) && !empty($request->nodo)) {
        $dataIns = Installations::getInstallById($request->id);
        //Validando que la instalación pertenezca al instalador autenticado

        if (!empty($dataIns) && $dataIns->installer == session('user') &&
          $dataIns->status == 'A') {

          //Se verifica si fue pagado con tarjeta(MIT) o en efectivo
          $statusSale = 'E';
          //CASH
          $typePayment = "CONTADO";
          $infPack = null; //objeto temporal del bundle
          $infoAllBundle = null; //Informacion del bundle
          $service_recharge = null; //Servicio de recarga de la subscripcion

          if (!empty($dataIns->payment_url_subscription)) {
            $StatusPayment = self::verifyPayment($request, $dataIns);

            $response['message'] = $StatusPayment['msg'];
            $response['code'] = $StatusPayment['code'];

            if ($StatusPayment['success']) {
              //Posee enlace de pago y debe estar si o si pago para seguir
              if ($StatusPayment['code'] == 'OK_PAY') {

                $statusSale = 'A';
                //$typePayment = "TARJETA";
                $typePayment = $dataIns->type_payment;

                $response['message'] = "Hay un problema en la busqueda del servicio de recarga de la subscripcion (CF2112)";
                $response['code'] = 'EMP_SER';

                if (!empty($dataIns->bundle_id_payment)) {
                  //Log::info('Verifico fibra Bundle');
                  $infPack = Bundle::getDetailBundleAlta($dataIns->bundle_id_payment, $statusList);

                  if ($infPack['success']) {
                    $infoAllBundle = json_decode(json_encode($infPack['data']));
                    $optVerif = self::verifyServiceRecharge($request, $infoAllBundle);
                  } else {
                    $response['message'] = "Hay un problema en la busqueda del bundle de subscripcion contratado (CF2123)";
                    $response['code'] = 'EMP_BUN';
                    return response()->json($response);
                  }
                } else {
                  //Log::info('Verifico fibra normal');
                  $request->plan = $dataIns->pack_id;
                  $optVerif = self::verifyServiceRecharge($request);
                }

                if (!$optVerif['success']) {
                  $response['message'] = $optVerif['msg'];
                  $response['code'] = $optVerif['code'];
                  return response()->json($response);
                }
              } else {
                return response()->json($response);
              }
            } else {
              return response()->json($response);
            }
          }

          //Validando que el dn de fibra exista, este en el status correcto y sea del tipo asociado a la instalación
          $dnDetail = Inventory::getDataDn($request->msisdn);

          //Se verifica si el alta es de bundle se revisa el inventario
          $confBundle = null;
          $ClientBundleId = null;
          if (!empty($dataIns->bundle_id)) {
            //Es un bundle, se verifica lo que compone el bundle
            if (is_null($infoAllBundle)) {
              $confBundle = Bundle::getDetailBundleAlta($dataIns->bundle_id, $statusList);
            } else {
              //Si ya se hizo la consulta reutilizamos la data
              $confBundle = ['success' => true, 'data' => $infPack['data']];
            }

            if ($confBundle['success']) {
              $confBundle = json_decode(json_encode($confBundle['data']));

              //se verifica cada articulo que componen el bundle para saber si el instalador notifico cada DN que esta entregandole al cliente
              $tipeT = self::ChekingBundleSales($confBundle->general, $request, 'T');
              if ($tipeT['error']) {
                return response()->json($tipeT);
              }

              $tipeH = self::ChekingBundleSales($confBundle->general, $request, 'H');
              if ($tipeH['error']) {
                return response()->json($tipeH);
              }

              $tipeM = self::ChekingBundleSales($confBundle->general, $request, 'M');
              if ($tipeM['error']) {
                return response()->json($tipeM);
              }

              $tipeMH = self::ChekingBundleSales($confBundle->general, $request, 'MH');
              if ($tipeMH['error']) {
                return response()->json($tipeMH);
              }
              //Fibra no se busca en la tabla ya que siempre fibra sera el msisdn master y este se busca antes de analizar el bundle
            } else {

              $response['message'] = "El alta corresponde a un bundle pero no se encontro detalles del mismo (CF2186)";
              $response['code'] = 'ERR_DB';
              return response()->json($response);
            }
            //Registramos el cliente_blundle
            $registerBundle = ClientNetweyBundle::newBundle($dataIns->bundle_id);
            if (!$registerBundle['success']) {

              Log::error($registerBundle['msg']);
              return response()->json($registerBundle);
            } else {
              $ClientBundleId = $registerBundle['id'];
            }
          }

          if (!empty($dnDetail) &&
            $dnDetail->inv_article_id == $dataIns->inv_article_id &&
            $dnDetail->status == 'A') {
            $assig = SellerInventory::getAsignmentUser($dnDetail->id, session('user'));
            //Validando que el dn este asociado al usuario autenticado
            if (!empty($assig)) {

              //Motivado que los servicios pueden darse de baja luego que se captura la cita se permite en estatus A e I
              $service = Service::getService($dataIns->service_id, $statusList);

              $fiber_service = Service::getPKService815($dataIns->service_id, $dataIns->id_fiber_zone, $statusList);

              if (!empty($fiber_service) && !empty($service)) {

                if ($service->is_payment_forcer == 'Y') {
                  if (!empty($dataIns->payment_force_start)) {
                    $infoQr = FiberPaymentForce::getUrlQr($dataIns->clients_dni, 'START', false, $dataIns->payment_force_start);
                    if (!empty($infoQr)) {
                      if ($infoQr->status != 'A') {
                        $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $infoQr->code_url;

                        $response['message'] = "Se debe verificar el contrato haya sido aceptado: " . $urlQr;
                        $response['code'] = "FAI_FOR";
                        return response()->json($response);
                      }
                    } else {
                      $response['message'] = "La cita de instalacion es con contrato, pero no se encontro informacion de dicho contrato (CF2216)";
                      $response['code'] = "EMP_FOR";
                      return response()->json($response);
                    }
                  }
                }
                //registro la configuracion del nodo en la instalacion
                //
                $configConex = new \stdClass;
                $configConex->nodo_de_red = $request->nodo;
                $configConex->nodo_de_red_name = $request->nodo_name;
                if (empty($dataIns->unique_transaction)) {
                  $unique = uniqid('FIB-') . time();
                } else {
                  $unique = $dataIns->unique_transaction;
                }
                try {
                  $dataIns->config_conex = $configConex;
                  $dataIns->inv_detail_fiber_id = $dnDetail->id;
                  $dataIns->client_bundle_id = $ClientBundleId;
                  $dataIns->status = 'EC';
                  $dataIns->unique_transaction = $unique;
                  $processCron = new \stdClass;
                  $processCron->start = date("Y-m-d H:i:s");
                  $dataIns->dateProcess = $processCron;
                  $dataIns->save();
                  sleep(2);

                } catch (Exception $e) {
                  $txMsg = 'Error al actualizar la cita de instalacion. (CF2255) ' . (String) json_encode($e->getMessage());
                  Log::error($txMsg);
                  $response['message'] = $txMsg;
                  $response['code'] = 'ERR_DB';

                  return response()->json($response);
                }
                //Si alta es de fibra normal retornamos el OK caso contrario es un bundle se debe realizar el proceso con altan de cada servicio del bundle
                if (!empty($confBundle)) {
                  //Es un bundle, se deben dar de alta los hijos del bundle (HBB, MIFI, Telefonia y simcard)
                  //Para este proceso se llevara a cabo de forma asincrona cada 1 minuto
                  //
                  //Informamos que se puede dar de altan los hijos del bundle

                  $articlesBundle = [
                    'T' => "Telefonia",
                    'H' => "Hogar",
                    'M' => "Mifi",
                    'MH' => "Mifi Huella",
                  ];
                  $UpServiceFail = "";
                  foreach ($articlesBundle as $item => $value) {

                    $UpArticle = self::RegisterChildrenAltaBundle($item, $request, $confBundle, $unique);
                    // $UpArticle = self::ActivateService($item, $request, $confBundle, $unique);
                    //
                    if (!$UpArticle['success']) {
                      $UpServiceFail .= $UpArticle['message'] . ', ';
                      Log::error($UpArticle['message']);
                    }
                  }

                  if (!empty($UpServiceFail)) {
                    //Se debe hacer la notificacion que fibra se proceso pero no los servicios ante altan
                    $response['message'] = "El registro para procesar el alta de los elementos del combo presentaron un inconveniente. +info(" . $UpServiceFail . ") ";
                    $response['code'] = "OK+FAIL";
                    $response['icon'] = 'info';
                    return response()->json($response);
                  } else {
                    $response['error'] = false;
                    $response['title'] = "Activación de los servicios del combo estaran listos en un momento!";
                    $response['message'] = "Solicitud registrada, debes esperar un momento para activarse el servicio de fibra y los elementos que componen el combo sean activados ante Altan";
                    $response['code'] = "OK";
                    $response['icon'] = 'success';
                    return response()->json($response);
                  }
                } else {
                  //Solo fibra
                  $response['error'] = false;
                  $response['title'] = "Activación del servicio de fibra estara listo en un momento!";
                  $response['message'] = 'Debes monitorear el status de activacion del servicio de fibra';
                  $response['code'] = "OK";
                  $response['icon'] = 'success';
                  return response()->json($response);
                }
              } else {

                $response['message'] = 'No se pudo obtener el servicio de fibra que se desea dar de alta. (CF2313)';
                $response['icon'] = 'info';
              }
            } else {
              $response['message'] = 'MSIDN no válido. (CF2317)';
              $response['icon'] = 'info';
            }
          } else {
            $response['message'] = 'MSIDN no válido. (CF2321)';
            $response['icon'] = 'info';
          }
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function getMSISDNGenerate(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'code' => 'FAIL_DN', 'msg' => 'No se pudo generar el msisdn de fibra. (CF2323)'];

      $response_inventary = Inventory::getSerialArt($request->serial);

      #bandera que controla si el equipo fue cargo previamente por el instalador

      $PreLoadArt = false;
      //Falso: no se ha cargado
      //True: el instalador hizo una peticion previamente
      if (isset($request->chk_ve) && !empty($request->chk_ve)) {
        //se trata de un equipo que cargo el instalador en un momento anterior
        if ($request->chk_ve == $request->serial
          && !$response_inventary['success']) {
          //es igual el serial y ya esta registrado previamente
          $PreLoadArt = true;
        } else {
          //No permitiremos que editen luego que se genera el DN
        }
      }

      if ($response_inventary['success'] || $PreLoadArt) {
        //Obtengo un nuevo dn para fibra
        //
        $msisdn = '';
        if (!$PreLoadArt) {
          $msisdn = Inventory::getAvailableDnAutogen();
        } else {
          //El DN ya fue generado en un paso previo busco el DN
          $artPrevius = Inventory::getArticleExist($request->mac);
          if ($artPrevius['success'] && $artPrevius['code'] == 'A') {
            $msisdn = $artPrevius['infoArt']->msisdn;
          }
        }
        if (!empty($msisdn)) {
          //Registro el DN en inventario de netwey
          //
          if (!$PreLoadArt) {
            $newArticle = Inventory::create_ArtFiber($msisdn, $request);
          } else {
            $newArticle = $artPrevius;
            $newArticle['newArticle'] = $artPrevius['infoArt'];
          }

          if ($newArticle['success']) {
            //Se asigna el DN directamente al instalador sin notificacion
            //
            if (!$PreLoadArt) {
              $newAssigne = SellerInventory::newAssigneArt($newArticle['newArticle']);
            } else {
              //Ya deberia estar asignado al instalador el equipo
              //Se verifica si lo tiene asignado
              $isAssigne = SellerInventory::isAssignedDn($msisdn, session('user'));
              if (!empty($isAssigne)) {
                $newAssigne['success'] = true;
              } else {
                $newAssigne['success'] = false;
                $newAssigne['msg'] = "No esta asignado el inventario al instalador";
              }
            }
            if ($newAssigne['success']) {
              //Todo fue OK se puede dar el alta
              $response = ['success' => true, 'code' => 'OK_DN', 'msisdn' => $msisdn];
            } else {
              $response = ['success' => false, 'code' => 'ASSIGNE_DN', 'msg' => $newAssigne['msg']];
            }
          } else {
            $response = ['success' => false, 'code' => 'REGISTER_DN', 'msg' => $newArticle['msg']];
          }
        } else {
          $response = ['success' => false, 'code' => 'GENERATE_DN', 'msg' => 'No se pudo obtener un nuevo DN para fibra'];
        }
      } else {
        $response = ['success' => false, 'code' => 'SERIAL', 'msg' => $response_inventary['msg']];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [codecMACValid Analiza la cadena de mac para que tenga una correcta estructura]
 * @param  [type] $request [Envio la variable mac]
 * @return [type]          [description]
 */
  private function codecMACValid($request)
  {
    //Debo recibir: $request->mac
    $response = ['success' => false, 'msg' => 'MAC OK'];

    $messages = [
      'mac.required' => 'La MAC del producto de fibra es requerida (IVF2404)',
      'mac.regex' => 'La MAC no posee un valor valido (IVF2405)',
    ];
    $requisitos = [
      'mac' => array(
        'required',
        'regex:/^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$/',
      ),
    ];

    $validate = Validator::make($request->all(), $requisitos, $messages);
    $errors = $validate->errors();

    if ($errors->any()) {
      $response['msg'] = "";
      foreach ($errors->all() as $error) {
        $response['msg'] .= $error . ', ';
      }
      return $response;
    }
    $response['success'] = true;

    return $response;
  }

  public function chekingMac(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $macValid = self::codecMACValid($request);
      if ($macValid['success']) {
        $response_inventary = Inventory::getArticleExist($request->mac);
        return response()->json($response_inventary);
      } else {
        return response()->json($macValid);
      }
    }
    return redirect()->route('dashboard');
  }

  public function changeMac(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $macValid = self::codecMACValid($request);

      if ($macValid['success'] && !empty($request->installation_id)) {

        $ins = Installations::getComponent($request->installation_id);
        if ($ins) {
          $inv = Inventory::getConnect('W')
            ->where('msisdn', $ins->msisdn)
            ->first();
          $inv->imei = strtoupper($request->mac);
          $inv->obs = $inv->obs . " | cambio de mac: anterior -> " . $inv->imei;
          $inv->save();

          $inst = Installations::getConnect('W')
            ->where('id', $ins->id)
            ->first();
          $inst->status = 'EC';
          $inst->save();

          $response = array('success' => true, 'msg' => 'Mac actualizada con exito');
        }
      } else {
        if (empty($request->installation_id)) {
          $response = array('success' => false, 'msg' => 'Se requiere el identificado de la instalacion para continuar');
        } else {
          $response = $macValid;
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [chekingCoverageFiber Metodo que verifica si se cuenta con cobertura en la olt de la ciudad seleccionada]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function chekingCoverageFiber(Request $request, $interno = false)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      if (isset($request->city_id) && isset($request->zone_id) && isset($request->lat) && isset($request->lng)) {

        $poligono = FiberCityZone::getCoordenada($request->city_id, $request->zone_id);
        $DataPoligono = $poligono['poligono']['poligono'];
        $resSrv = FiberCityZone::servicialidadFibra($request->lat, $request->lng, $DataPoligono);

        if ($resSrv == 'VERTICE' || $resSrv == 'BORDE' || $resSrv == 'DENTRO') {
          $respuesta = array('success' => true, 'code' => $resSrv);
        } else {
          $respuesta = array('success' => false, 'msg' => 'No se cuenta con cobertura en la ubicación especificada.');
        }
      } else {
        $respuesta = array('success' => false, 'msg' => 'Faltan datos para verificar cobertura de fibra');
      }

      if (!$interno) {
        return response()->json($respuesta);
      } else {
        return $respuesta;
      }
    }
    return redirect()->route('dashboard');
  }
/**
 * [getCoordToAddress obtener coordenadas de un direccion]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getCoordFromAddress(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      return Google::getDataFromAddress($request->locality);
    }
    return redirect()->route('dashboard');
  }

/**
 * [getCalendar Calcula los dias que no puede elegir dias debido a que esta copado por agendamientos]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getCalendar(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['sucess' => false, 'msg' => "Faltan datos para procesar la solicitud"];

      if (!empty($request->fiberZone)) {
        $idZone = $request->fiberZone;
        // $city = $request->city; //Momentaneamente no es requerido

        //reviso que la zona este disponible
        $infoZone = FiberZone::getInfoZone($idZone);
        if (!empty($infoZone)) {

          //Reviso cuantos instaladores posee la zona activos
          $isInstall = User::getInstallerZone($idZone);
          $cantInstaller = count($isInstall);

          if ($cantInstaller > 0) {
            //Reviso la capacidad de instalacion de la zona
            $configuration = json_decode(json_encode($infoZone->configuration));

            if (!isset($configuration->capacity_installer)) {
              //Si no esta definido en el json por defecto es 1
              $configuration->capacity_installer = 1;
              //Capacidad por instalador por Turno
            }
            //Capacidad turno
            //$configuration->capacity_installer *= 2;
            $capacityTurno = $configuration->capacity_installer;

            //Capacidad de instalacion en la zona
            //$installZone = $cantInstaller * $configuration->capacity_installer;

            //Reviso cuantas citas de instalacion hay confirmadas en la zona
            $listPending = Installations::getInstallerPending($idZone);
            $BlockDay = [];

            $BlockDay = FiberHoliday::getDayFeriado($BlockDay, 'full');

            $BlockMediaDay = [];
            $BlockMediaDay = FiberHoliday::getDayFeriado($BlockMediaDay, 'media');

            foreach ($listPending as $agenda) {
              if (!empty($BlockMediaDay)) {
                foreach ($BlockMediaDay as $itemMedia) {
                  if (strtotime($itemMedia->fecha) == strtotime($agenda->date_instalation)) {
                    //Capacidad de medio dia
                    $installZone = $capacityTurno * $cantInstaller;
                    break;
                  } else {
                    //capacidad dia completo
                    $installZone = $capacityTurno * $cantInstaller * 2;
                  }
                }
              } else {
                //capacidad dia completo
                $installZone = $capacityTurno * $cantInstaller * 2;
              }
              if ($agenda->cant_installer >= $installZone) {
                //$fecha = explode(" ", $agenda->date_instalation);
                $festive = new \stdClass;
                /*Se debe quitar el cero a la izquierda de la fecha para que el frontend lo tome*/
                $DayBlock = strtotime($agenda->date_instalation);
                $anio = date("Y", $DayBlock);
                $mes = date("m", $DayBlock);
                $dia = date("d", $DayBlock);
                $DayBlock = intval($dia) . '-' . intval($mes) . '-' . intval($anio);

                $festive->fecha = $DayBlock;
                $festive->type = 'full';
                array_push($BlockDay, $festive);
              }
            }

            //Se toma agenda para el dia siguiente
            $starDate = date("d-m-Y", strtotime("+ 0 days", time()));

            $endDate = date("d-m-Y", strtotime("+ 8 month", time()));

            $respal = $BlockDay;
            $BlockDay = [];
            if (count($respal)) {
              foreach ($respal as $item) {
                array_push($BlockDay, $item->fecha);
              }
            }
            $respal = null;

            $html = view('fiber.Select_DateInstall', compact('BlockDay', 'starDate', 'endDate'))->render();
            $response = ['sucess' => true, 'html' => $html];

          } else {
            $response = ['sucess' => true, 'html' => '<div class="container alert alert-danger">No hay calendario disponible para mostrar en la zona, no hay personal minimo en este momento que procese la solicitud </div>'];
          }
        } else {
          $response = ['sucess' => true, 'html' => '<div class="container alert alert-danger">La zona no esta disponible para mostrar calendario</div>'];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [getClock data una fecha conocer el turno disponible si es manana o tarde]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getClock(Request $request, $interno = false)
  {
    if (($request->isMethod('post') && $request->ajax()) || $interno) {

      $response = ['sucess' => false, 'msg' => "Faltan datos para procesar la solicitud"];

      if (!empty($request->date) && !empty($request->zone_id)) {

        $dateSearch = $request->date;
        $idZone = $request->zone_id;
        // $city = $request->city; //Momentaneamente no es requerido
        //
        $infoZone = FiberZone::getInfoZone($idZone);
        if (!empty($infoZone)) {

          $fecha_actual = strtotime(date("Y-m-d"));
          $fecha_entrada = strtotime($dateSearch);

          if ($fecha_actual <= $fecha_entrada) {
            $listPendingDay = Installations::getInstallerPendingDay($idZone, $dateSearch);

            //Obtengo capacidad de instalacion de la zona
            $configuration = json_decode(json_encode($infoZone->configuration));

            if (!isset($configuration->capacity_installer)) {
              //Si no esta definido en el json por defecto es 1
              $configuration->capacity_installer = 1;
            }

            //Obtengo cuantos instaladores posee la zona activos
            $isInstall = User::getInstallerZone($idZone);
            $cantInstaller = count($isInstall);

            //Capacidad de instalacion en la zona por turno
            $installZoneTurno = $cantInstaller * $configuration->capacity_installer;

            //Lista de turnos
            $listTurno = array(
              array("hour" => "09:00 - 13:00", "habilite" => 'Y'),
              array("hour" => "14:00 - 18:00", "habilite" => 'Y'),
            );

            $BlockMediaDay = [];
            $BlockMediaDay = FiberHoliday::getDayFeriado($BlockMediaDay, 'media');

            if (!empty($BlockMediaDay)) {
              foreach ($BlockMediaDay as $itemMedia) {
                if (strtotime($itemMedia->fecha) == strtotime($request->date)) {
                  //Capacidad de la tarde no disponible por feriado
                  $listTurno[1]['habilite'] = 'N';
                  break;
                }
              }
            }
            $cantHours = count($listTurno);
            $cantHourFull = 0;
            foreach ($listTurno as &$turno) {
              foreach ($listPendingDay as $PendingDay) {
                if ($turno['hour'] == $PendingDay->schedule) {
                  if ($PendingDay->cant_installer >= $installZoneTurno) {
                    $turno['habilite'] = 'N';
                    $cantHourFull++;
                  }
                }
              }
            }
            $availableTime = true;
            if ($cantHours == $cantHourFull) {
              $availableTime = false;
            }

            if ($dateSearch == date("Y-m-d") && $availableTime) {
              //Se esta capturando la cita para el dia en curso
              $hora = intval(date("H"));
              if ($hora >= 12) {
                //Cita pedida en la tarde queda para agendar manana
                $availableTime = false;
              } else {
                //Cita pedida en la manana se puede maximo en la tarde
                $listTurno[0]['habilite'] = 'N';
              }
            }
            if (!$interno) {
              $html = view('fiber.Select_HourInstall', compact('listTurno', 'availableTime'))->render();
              $response = ['sucess' => true, 'html' => $html];
            } else {
              //Se usa para validar el dia y el turno antes de registrar en BD
              return ['listTurno' => $listTurno, 'availableTime' => $availableTime];
            }
          } else {
            $response = ['sucess' => false, 'msg' => 'La fecha a verificar debe ser a futuro, por tanto no es valida para consultar'];
          }
        } else {
          $response = ['sucess' => true, 'html' => '<div class="container alert alert-danger">La zona no esta disponible para mostrar horario</div>'];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [getTypification Se obtiene el select de typificacion]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getTypification(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->cita)) {
        $citaId = $request->cita;
        $typeBtn = $request->type;

        $dataInstall = Installations::getAddressInstalation($citaId);

        $listTypification = FiberTypification::getTypification();
        $html = view('dashboard.select_typification', compact('listTypification', 'citaId', 'typeBtn', 'dataInstall'))->render();
      } else {
        $html = "No se pudo generar el listado de tipificacion por falta de datos";
      }
      return response()->json(['success' => true, 'html' => $html]);
    }
    return redirect()->route('dashboard');
  }

  public function cancelInstalation(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->cita) && !empty($request->typification)) {
        $setmc = Installations::markCancelled($request);
        if (!$setmc['success']) {
          return response()->json(['success' => false, 'title' => 'Cita no pudo cancelarse!', 'msg' => $setmc['msg'], 'icon' => 'warning']);
        }
        $hst = History_control::insertHistory(null, $request->cita, 'SR');
        if (!$hst['success']) {
          return response()->json(['success' => false, 'title' => 'Cita no pudo cancelarse!', 'msg' => $hst['msg'], 'icon' => 'warning']);

        } else {
          return response()->json(['success' => true, 'title' => 'Cita cancelada!', 'msg' => 'La cita sera procesada por mesa de control!', 'icon' => 'success']);
        }
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [getQrForce Se carga la info de la identificacion del cliente y se crea el QR de terminos y condiciones]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getQrForce(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'Hubo un error en procesar la generación de contrato de fibra', 'icon' => 'warning', 'code' => 'FAIL'];

      $messages = [
        'dni.required' => 'El identificador del cliente es requerida (IVF2953)',
        'pack.required' => 'El identificador del paquete es requerida (IVF2954)',
        'isBundle.required' => 'El identificador del tipo de paquete es requerida (IVF2955)',
        'date_instalation.required' => 'La fecha de instalación es requerida (IVF2956)',
        'schedule.required' => 'El turno de instalación es requerida (IVF2957)',
        'identity.required' => 'El numero o codigo de identidad del cliente es requerida (IVF2958)',
        'typeIdentity.required' => 'El tipo de identificación del cliente es requerida (IVF2959)',
        'photo_identiF.required' => 'La fotografia frontal del documento de  identidad del cliente es requerida (IVF2960)',
        'photo_identiP.required' => 'La fotografia posterior del documento de  identidad del cliente es requerida (IVF2961)',
        'codePhoneContact.required' => 'La bandera del tipo de verificación del telefono del cliente es requerida (IVF2962)'];

      $requisitos = [
        'dni' => array(
          'required',
        ),
        'pack' => array(
          'required',
        ),
        'isBundle' => array(
          'required',
        ),
        'date_instalation' => array(
          'required',
        ),
        'schedule' => array(
          'required',
        ),
        'identity' => array(
          'required',
        ),
        'typeIdentity' => array(
          'required',
        ),
        'photo_identiF' => array(
          'required',
        ),
        'photo_identiP' => array(
          'required',
        ),
        'codePhoneContact' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      /* $this->validate($request, [
      'dni' => 'required',
      'pack' => 'required',
      'isBundle' => 'required',
      'date_instalation' => 'required',
      'schedule' => 'required',
      'identity' => 'required',
      'typeIdentity' => 'required',
      'photo_identiF' => 'required|image',
      'photo_identiP' => 'required|image',
      ]);*/

      $info_client = Client::getClientByDNI($request->dni);
      if (empty($info_client)) {
        //No existe el cliente
        return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'No se pudo encontrar el registro del prospecto (CF3023)', 'icon' => 'warning', 'code' => 'EMP_CLI']);
      }

      $request->pack = intval(Common::decodificarBase64($request->pack));
      $request->isBundle = Common::decodificarBase64($request->isBundle);

      if ($request->isBundle == 'Y') {
        //Buscamos el id del pack de fibra
        $confBundle = Bundle::getDetailBundleAlta($request->pack);
        if ($confBundle['success']) {
          $confBundle = json_decode(json_encode($confBundle['data']));
          if (isset($confBundle->info_F->id)) {
            $request->pack = $confBundle->info_F->id;
          } else {
            return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'Se esta tratando de generar un contrato para un pack de tipo bundle pero no se pudo obtener el id del servicio de fibra (CF3031)', 'icon' => 'warning', 'code' => 'EMP_PAK']);
          }
        } else {
          return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'Se esta tratando de generar un contrato para un pack de tipo bundle pero no se pudo obtener detalles del servicio de fibra (CF3034)', 'icon' => 'warning', 'code' => 'EMP_BUN']);
        }
      }
      $request->schedule = Common::decodificarBase64($request->schedule);

      $listPhoto = array('photo_identiF', 'photo_identiP');
      $path = 'installations/identity/';
      $urlPhotoF = '';
      $urlPhotoP = '';

      foreach ($listPhoto as $photo) {

        $photoIdent = $request->file($photo);
        $photoPath = $path . uniqid() . time() . '.' . $photoIdent->getClientOriginalExtension();

        Storage::disk('s3')->put(
          $photoPath,
          file_get_contents($photoIdent->getPathname()),
          'public'
        );

        if ($photo == "photo_identiF") {
          $urlPhotoF = (String) Storage::disk('s3')->url($photoPath);
        } elseif ($photo == "photo_identiP") {
          $urlPhotoP = (String) Storage::disk('s3')->url($photoPath);
        }
      }

      if (!empty($urlPhotoF) && !empty($urlPhotoP)) {
        //Se registran los documentos del cliente
        $creDoc = Client_document::createRegister($request->dni, $request->identity, $urlPhotoF, $urlPhotoP, $request->typeIdentity);
        if (!$creDoc['success']) {
          return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => $creDoc['msg'], 'icon' => 'warning', 'code' => 'FAIL_BD']);
        }
      } else {
        return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'Los documentos de identificacion no se pudieron procesar (CF3075)', 'icon' => 'warning', 'code' => 'EMP_PHO']);
      }

      $contract_url = FiberPaymentForce::generateContract($request);
      if (!$contract_url['success']) {
        return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => $contract_url['msg'], 'icon' => 'warning', 'code' => 'FAIL_URL']);
      } else {
        $contract_url = $contract_url['url'];
      }

      $infoQr = FiberPaymentForce::newUrlQr($request->dni, $request->pack, $request->date_instalation, $request->schedule, 'START', $contract_url);

      if (!$infoQr['success']) {
        return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => $infoQr['msg'], 'icon' => 'warning', 'code' => 'EMP_QR']);
      }

      $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $infoQr['url'];

      //Segun el tipo de verificacion que tenga el celular de contacto, o se envia el SMS o se renderiza en pantalla
      //
      if ($request->codePhoneContact == "AUTHORIZED") {

        $Qr_svg = ('' . \QrCode::format('svg')->size(310)->generate($urlQr));

        $phone_error = "";
        if (empty($info_client->phone_home)) {
          $phone_error = "El cliente no posee teléfono principal de contacto. Este dato es importante para contactar al cliente";
        }
        $dni = $request->dni;
        $name_client = $info_client->name;
        $phone_client = $info_client->phone_home;
        $type = "START";
        $tyc = $infoQr['id'];
        $msgWhatsapp = "Entendemos%20que%20puede%20haber%20ciertos%20t%C3%A9rminos%20y%20condiciones%20que%20usted%20querr%C3%A1%20revisar%20antes%20de%20proceder%20con%20este%20agendamiento%20del%20servicio%20de%20Fibra%20Netwey.%20T%C3%B3mese%20unos%20minutos%20para%20leerlas%20antes%20de%20aceptarlas%20para%20continuar%20con%20el%20proceso.";
        $msgText = "Adjunto contrato de adhesión del servicio de Fibra.";
        $htmlShare = view('fiber.share_tycf', compact('urlQr', 'phone_client', 'dni', 'name_client', 'type', 'tyc', 'msgWhatsapp', 'msgText', 'phone_error'))->render();

        return response()->json(['success' => true, 'html' => $Qr_svg, 'htmlShare' => $htmlShare, 'tyc' => $infoQr['id'], 'msg' => 'OK', 'icon' => 'success', 'code' => 'AUTHORIZED']);
      } else {
        //Envio el mensaje con el url de contrato
        //NOTA: el sms_type debe ser 'G' y el mensaje debe ir en sms_attribute y en sms(En ambos el mismo contenido)
        //
        if (empty($info_client->phone_home)) {
          return response()->json(['success' => false, 'html' => '', 'htmlShare' => '', 'msg' => 'El cliente no posee telefono de contacto registrado', 'icon' => 'warning', 'code' => 'EMP_PHO']);
        }

        //Obtengo el nombre para personalizar el mensaje
        $userText = "";
        if (!empty($info_client->name)) {
          $nombre = explode(" ", $info_client->name)[0];
          $userText = "Sr(a) " . $nombre . ", ";
        }

        try {
          $msgContract = $userText . "Su contrato de adhesion Netwey: " . $urlQr;
          Sms_notification::Send_sms(
            $info_client->phone_home,
            $info_client->phone_home,
            'G',
            '1',
            "Contrato de adhesion",
            $msgContract,
            $msgContract);
        } catch (Exception $e) {
          Log::error('No se pudo enviar el msj con el contrato de adhesión al telefono del cliente: ' . $info_client->phone_home . ' Error: ' . (String) json_encode($e->getMessage()));
        }

        $icono = Sms_notification::getIcon('SEND_SMS');
        return response()->json(['success' => true, 'html' => '', 'htmlShare' => '', 'tyc' => $infoQr['id'], 'msg' => 'Se envio un SMS con la información del contrato de adhesión al telefono: ' . $info_client->phone_home . ' del cliente el cual fue verificado, al recibir el SMS debe leer y aprobar para proseguir con el proceso de venta de fibra', 'icon' => $icono, 'code' => 'VERIFIED']);
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [sendMailQr Se envia por correo electronico el enlace de los terminos y condiciones]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function sendMailQr(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $info_client = Client::getClientByDNI($request->dni);
      $type = Common::decodificarBase64($request->type);

      $notmail = true;
      if (!empty($info_client)) {
        if (!empty($info_client->email)) {

          $infoQr = FiberPaymentForce::getUrlQr($request->dni, $type, 'C');
          if (!empty($infoQr)) {
            $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $infoQr->code_url;

            if ($type == "START") {

              $infodata = [
                'asunto' => "Contrato de adhesión para agendamiento de cita de fibra Netwey",
                'client_name' => $info_client->name,
                'urlqr' => $urlQr,
                'process' => "agendamiento de cita",
                'bodytext' => "Entendemos que puede haber ciertos términos y condiciones que usted querrá revisar antes de proceder con este agendamiento del servicio de Fibra Netwey. Tómese unos minutos para leerlas antes de aceptarlas para continuar con el proceso.",
                'nota' => "Una vez revisado y aceptado el contrato de adhesión puedes informar a tu asesor de ventas para proseguir con el proceso de agendamiento"];
            } elseif ($type == "END") {

              $infodata = [
                'asunto' => "Contrato de adhesión post-instalación de servicio de fibra Netwey",
                'client_name' => $info_client->name,
                'urlqr' => $urlQr,
                'process' => "post-instalación",
                'bodytext' => "Gracias por preferir nuestro servicio de internet de fibra, nos alegra tenerte con nosotros, ya su servicio fue instalado y nos gustaria por favor conocer que estas conforme con nuestra atención. Tómese unos minutos para visitar el enlace adjunto  para concluir el proceso de instalación.",
                'nota' => "Una vez revisado la informacion adjunta puedes informar al instalador"];
            } else {
              return response()->json(['title' => "No se pudo enviar el email", 'msg' => "El tipo de mensaje que se trata de enviar no esta configurado", 'icon' => "warning"]);
            }

            try {
              //SendMailNotifiAlert::generateMail($infodata);

              Mail::to($info_client->email)->send(new Mail_tycfQr($infodata));

              return response()->json(['title' => "Correo enviado!", 'msg' => "Por favor informar al cliente " . $info_client->name . " que revise su bandeja de correo electrónico: " . $info_client->email . ". Alli vera información de vital importancia para continuar el proceso que llevas a cabo.", 'icon' => "success"]);

            } catch (\Exception $e) {
              Log::error('No se pudo enviar el correo de contrato de adhesión del agendamiento de cita de fibra: ' . $info_client->email . ' Error: ' . (String) json_encode($e->getMessage()));
            }
          } else {
            return response()->json(['title' => "No se pudo enviar el email", 'msg' => "El cliente no posee un enlace activo por revisar", 'icon' => "warning"]);
          }
        } else {
          return response()->json(['title' => "No se pudo enviar el email", 'msg' => "El cliente no posee correo", 'icon' => "warning"]);
        }
      } else {
        return response()->json(['title' => "No se pudo enviar el email", 'msg' => "El cliente no esta registrado", 'icon' => "warning"]);
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [verifyQr Se verifica si el qr fue aceptado o no por el cliente]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function verifyQr(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo verificar el QR", 'msg' => "Faltan datos para procesar la verificacion", 'code' => 'FAIL'];

      $messages = [
        'dni.required' => 'El identificador del cliente es requerida (IVF3225)',
        'tyc.required' => 'El identificador del contrato es requerida (IVF3226)',
      ];

      $requisitos = [
        'dni' => array(
          'required',
        ),
        'tyc' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      if (!empty($request->dni) && !empty($request->tyc)) {

        $infoQr = FiberPaymentForce::getUrlQr($request->dni, false, 'A', Common::decodificarBase64($request->tyc));

        if (!empty($infoQr)) {
          return response()->json(['success' => true]);
        }
        $info_client = Client::getClientByDNI($request->dni);
        $name = '';
        if (!empty($info_client)) {
          $name = $info_client->name;
        }

        $response = ['success' => false, 'title' => "No se puede continuar", 'msg' => 'El cliente ' . $name . ' "no ha aceptado" el contrato de adhesión para el servicio de fibra Netwey. Por favor recuerdale al cliente revisarlos para poder registrar la solicitud que se lleva a cabo', 'icon' => "warning"];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [getQrForcerEnd Genera el qr final de contrato]
 * @param  [type] $id         [registro de instalacion]
 * @param  [type] $data       [la data de instalacion]
 * @param  [type] $questions  [preguntas]
 * @param  [type] $answers    [opciones de las preguntas]
 * @param  [type] $htmlBundle [description]
 * @param  [type] $tyc        [description]
 * @return [type]             [description]
 */
  private function getQrForcerEnd($id, $data)
  {
    $date = date_create($data->date_instalation);
    $date_instalation = date_format($date, "Y-m-d");

    Log::info('DATA ' . (String) json_encode($data));
    $infoQr = FiberPaymentForce::newUrlQr($data->clients_dni, $data->pack_id, $date_instalation, $data->schedule, 'END');

    if (!$infoQr['success']) {
      $Qr_svg = '<div class="container alert alert-danger">' . $infoQr['msg'] . '</div>';
      $type_content = "ERROR";

      return array('success' => false, 'Qr_svg' => $Qr_svg, 'type_content' => $type_content);
    }

    $tyc = $infoQr['id'];
    //Registramos el termino y condicion final a la instalacion
    //
    $regtyc = Installations::markAceptTyc($id, $tyc, 'END');
    if (!$regtyc['success']) {
      $Qr_svg = '<div class="container alert alert-danger">' . $regtyc['msg'] . '</div>';
      $type_content = "ERROR";

      return array('success' => false, 'Qr_svg' => $Qr_svg, 'type_content' => $type_content);
    }

    $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $infoQr['url'];
    $Qr_svg = ('' . \QrCode::format('svg')->size(310)->generate($urlQr));

    return array('success' => true, 'urlQr' => $urlQr, 'tyc' => $tyc, 'Qr_svg' => $Qr_svg);
  }

/**
 * [getShareForcerEnd Renderizado de compartir qr de pago forzado en la post-instalacion]
 * @param  [type] $data  [description]
 * @param  [type] $urlQr [description]
 * @param  [type] $tyc   [description]
 * @return [type]        [description]
 */
  private function getShareForcerEnd($data, $urlQr, $tyc)
  {
    $phone_error = "";
    if (!empty($data)) {
      $info_client = Client::getClientByDNI($data->clients_dni);
      if (empty($info_client->phone_home)) {
        $phone_error = "El cliente no posee teléfono principal de contacto. Este dato es importante para contactar al cliente";
      }
      $dni = $data->clients_dni;
      $name_client = $info_client->name;
      $phone_client = $info_client->phone_home;
      $type = "END";
      $msgWhatsapp = "Nos%20complace%20anunciar%20que%20hemos%20terminado%20de%20instalar%20su%20servicio%20de%20Fibra%20Netwey.%20%20%C2%A1Por%20favor,%20confirme%20que%20la%20instalaci%C3%B3n%20y%20las%20pruebas%20de%20velocidad%20se%20han%20hecho%20correctamente!";
      $msgText = "Adjunto contrato de adhesión del servicio de Fibra.";
      $htmlShare = view('fiber.share_tycf', compact('urlQr', 'phone_client', 'dni', 'name_client', 'type', 'tyc', 'msgWhatsapp', 'msgText', 'phone_error'))->render();

    } else {
      $htmlShare = '<div class="container alert alert-danger">No se pudo precisar información para compartir los datos de post-instalación </div>';
    }
    return array('success' => true, 'htmlShare' => $htmlShare);
  }

  private function sendSMSPostContract($dataInstall, $urlQr, $install_id)
  {
    $info_client = Client::getClientByDNI($dataInstall->clients_dni);
    if (!empty($info_client->phone_home) && !empty($info_client->verify_phone_id)) {

      $sendInstallContract = Verify_contact_client::getRegisterVerify($info_client->verify_phone_id);

      if (!empty($sendInstallContract)) {
        if ($sendInstallContract->status == "VERIFIED") {

          //Obtengo el nombre para personalizar el mensaje
          $userText = "";
          if (!empty($info_client->name)) {
            $nombre = explode(" ", $info_client->name)[0];
            $userText = "Sr(a) " . $nombre . ", ";
          }

          try {
            $msgContract = $userText . "Su servicio fibra con contrato fue instalado: " . $urlQr;
            Sms_notification::Send_sms(
              $info_client->phone_home,
              $info_client->phone_home,
              'G',
              '1',
              "Post-instalacion contrato",
              $msgContract,
              $msgContract);

            return array('success' => true, 'sendSMS' => "SEND_SMS");
          } catch (Exception $e) {
            Log::error('No se pudo enviar el msj con el recordatorio de instalacion de un servicio de fibra bajo contrato de adhesión al telefono del cliente: ' . $info_client->phone_home . ' Error: ' . (String) json_encode($e->getMessage()));
          }
        } else {
          Log::info("No se envio el mensaje de post-instalación de fibra con contrato de adhesión para la instalacion " . $install_id . " ya que el celular " . $info_client->phone_home . " del cliente no esta verificado ");
        }
      }
    } else {
      Log::info("No se envio el mensaje de post-instalación de fibra con contrato de adhesión para la instalacion " . $install_id . " debido a que el celular del cliente no esta registrado o no esta verificado");
    }
    return array('success' => false, 'sendSMS' => "NO_SEND");
  }

/**
 * [installerSurvey Resumen post-instalacion en la que se llena la encuesta y se muestra el resultado de la activacion ante altan de los productos que conformen el bundle]
 * @param  Request $request [description]
 * @param  [type]  $id      [description]
 * @return [type]           [description]
 */
  public function installerSurvey(Request $request, $id)
  {
    if ($id) {
      $data = Installations::getDateDetailByID($id);

      if (empty($data)) {
        return redirect()->route('dashboard');
      }
      $questions = FiberQuestions::getQuestionsByPlatform('END');

      $answers = FiberQuestionResult::getAnswersById($id, 'END');

      $bundle = 'N';
      $plan = null;
      $htmlBundle = "";

      //debemos saber los hijos del bundle de esta instalacion
      $childrenBundle = InstallationsBundle::getChildrenActive($data->id);
      //Log::info('data ' . (String) json_encode($childrenBundle));
      $init = true;
      $isCombo = !empty($data->bundle_id) ? true : false;
      $htmlBundle = view('fiber.resultBundle', compact('childrenBundle', 'id', 'init', 'isCombo'))->render();

      if (!empty($data->bundle_id)) {
        //Plan bundle
        $InfoBundle = Bundle::getDetailBundleAlta($data->bundle_id, ['A', 'I']);

        if ($InfoBundle['success']) {
          //Es un bundle
          $data->info_plan = json_decode(json_encode($InfoBundle['data']));
          $bundle = 'Y';
        }
      } else {
        //Es un pack individual
        $data->info_plan = Pack::getPlanDetailFiber($data->pack_id, ['A', 'I']);
      }

      $Qr_svg = '';
      $htmlShare = '';
      $type_content = "";
      $tyc = '';

      if ($data->is_payment_forcer == 'Y') {
        if (empty($data->payment_force_end)) {
          //Se genera el Qr
          //
          //Se evalua si fue ya procesado el alta para mostrar el QR
          if (!empty($data->msisdn) && ($data->status == 'P' ||
            $data->status == 'PA')) {
            /////
            $createQr = self::getQrForcerEnd($id, $data);
            if (!$createQr['success']) {
              $Qr_svg = $createQr['Qr_svg'];
              $type_content = $createQr['type_content'];

              return view('fiber.install_survey', compact('data', 'questions', 'answers', 'Qr_svg', 'htmlShare', 'htmlBundle', 'type_content', 'tyc'))->render();
            } else {
              $urlQr = $createQr['urlQr'];
              $tyc = $createQr['tyc'];
              $Qr_svg = $createQr['Qr_svg'];
            }

          } else {
            //queda con loading hasta que se tenga el Dn cargado para que genere el contrato final
            $Qr_svg = '<div class="container">
            <i class="fa fa-spin fa-spinner" style="font-size: 40px;"></i>
            <div> Se debe esperar que sea activado el servicio de fibra con contrato de adhesión para generar el QR de confirmación de instalación</div>
            </div>';
            $type_content = "LOADING";

            return view('fiber.install_survey', compact('data', 'questions', 'answers', 'Qr_svg', 'htmlShare', 'htmlBundle', 'type_content', 'tyc'))->render();
          }
        } else {
          //Se consulta el Qr
          $Qr = FiberPaymentForce::getUrlQr($data->clients_dni, 'END', false, $data->payment_force_end);

          if (!empty($Qr)) {
            $urlQr = env('SITE_WEB_NETWEY') . 'tycf/' . $Qr->code_url;
            $tyc = $data->payment_force_end;
            $Qr_svg = ('' . \QrCode::format('svg')->size(310)->generate($urlQr));
          }
        }

        $shareQrForce = self::getShareForcerEnd($data, $urlQr, $tyc);
        $htmlShare = $shareQrForce['htmlShare'];
        $sendSMSContract = self::sendSMSPostContract($data, $urlQr, $id);

        $type_content = "QR";
      } else {
        $type_content = "SIN_QR";
      }

      return view('fiber.install_survey', compact('data', 'questions', 'answers', 'Qr_svg', 'htmlShare', 'htmlBundle', 'type_content', 'tyc'))->render();
    }
    return redirect()->route('dashboard');
  }

  public function doSurvey(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['error' => true, 'message' => 'No se pudo procesar las preguntas. Faltan datos'];
      if (!empty($request->install_id)) {

        $conAn = FiberQuestionResult::insertAnswers($request->install_id, $request->all());
        if (!$conAn['success']) {
          $response = ['error' => true, 'message' => $conAn['msg']];
        } else {
          $response = ['error' => false, 'message' => 'OK'];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [setQrForce registro cual fue el termino y condicion final que se registro en la post-instalacion]
 * @param Request $request [description]
 */
  public function setQrForce(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo registrar el QR", 'msg' => "Faltan datos para procesar el registro", 'icon' => "warning"];

      if (!empty($request->tyc)) {
        $request->tyc = Common::decodificarBase64($request->tyc);
      }

      $this->validate($request, [
        'id' => 'required',
        'tyc' => 'required']);

      if (!empty($request->id) && !empty($request->tyc)) {
        $regtyc = Installations::markAceptTyc($request->id, $request->tyc, 'END');

        if (!$regtyc['success']) {
          $response = ['success' => false, 'msg' => $regtyc['msg']];
        } else {
          $response = ['success' => true, 'msg' => 'OK'];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }
  private function createSharePayment($idInstall, $urlQr)
  {
    sleep(1);
    $infoCita = Installations::getDateDetailByID($idInstall);
    if (!empty($infoCita)) {
      if (!empty($infoCita->payment_url_subscription)) {
        $dataClient = Client::getClientByDNI($infoCita->clients_dni);

        if (!empty($dataClient)) {
          if (empty($dataClient->phone_home)) {
            $phone_error = "El cliente no posee teléfono principal de contacto. Este dato es importante para contactar al cliente";
            $phone_client = "";
          } else {
            $phone_error = "";
            $phone_client = $dataClient->phone_home;
          }
          $dni = $dataClient->dni;
          $name_client = $dataClient->name;
          $msgWhatsapp = "Nos%20complace%20saber%20que%20formaras%20parte%20de%20nosotros%20y%20que%20seleccionaste%20un%20servicio%20de%20pago%20recurrente.";
          $msgText = "Adjunto enlace de pago de servicio.";
          $idInstall = Common::codificarBase64($infoCita->id);
          $html = view('fiber.share_payment', compact('urlQr', 'phone_client', 'idInstall', 'name_client', 'msgWhatsapp', 'msgText', 'phone_error'))->render();
          $response = ['success' => true, 'html' => $html];
        } else {
          $response = ['success' => false, 'title' => "No se pudo crear los enlaces de compartir QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del cliente", 'icon' => "warning"];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo crear los enlaces de compartir QR de pago", 'msg' => "No se encuentra la URL de pago en el sistema (CF3181)", 'icon' => "warning"];
      }
    } else {
      $response = ['success' => false, 'title' => "No se pudo crear los enlaces de compartir QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda de la cita", 'icon' => "warning"];
    }
    return $response;
  }

/**
 * [verifyServiceRecharge Se evalua que se tenga los paquetes y servicio y el correspondiente plan de recarga configurado los de tipo de subscripcion]
 * @param  [type]  $request    [description]
 * @param  boolean $dataBundle [Si el campo trae informacion es xq verificaremos un bundle]
 * @return [type]              [description]
 */
  private function verifyServiceRecharge($request, $dataBundle = false, $statusList = ['A', 'I'])
  {
    //$objB = new \stdClass;
    $packpricesID = null;
    $serviceID = null;
    $mount_payment = 0;
    $title_payment = "";

    if ($dataBundle) {
      //Log::info('Revision del un plan de fibra con bundle y con susbcripcion ');
      //Log::info('DATABUNDLE ' . (String) json_encode($dataBundle));

      $infoBundle = Bundle::getComponentBundle($dataBundle->general->id, ['A', 'I']);
      if (!empty($infoBundle)) {
        if (!empty($infoBundle->recharge_susbcription)) {
          $packpricesID = $dataBundle->general->id;
          $service_recharge = $infoBundle->recharge_susbcription;
        } else {
          return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio de recarga para el bundle: " . $dataBundle->general->id . " (CF3213)", 'icon' => "warning", 'code' => "EMP_SRP"];
        }
      } else {
        return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del bundle: " . $dataBundle->general->id . " (CF3216)", 'icon' => "warning", 'code' => "EMP_SRB"];
      }
      $serviceID = $dataBundle->general->id;
      $mount_payment = $dataBundle->general->total_payment;
      $title_payment = $dataBundle->general->title;
      $is_bundle = "Y";
///////////////////////////////////////
      /*
      $productList = [
      'F' => "Fibra",
      'T' => "Telefonia",
      'M' => "Mifi",
      'MH' => "Mifi Huella",
      'H' => "Hogar"];

      foreach ($productList as $key => $value) {
      $containt = "containt_" . $key;
      $infoArt = "info_" . $key;
      if ($dataBundle->general->$containt == 'Y' &&
      !empty($dataBundle->$infoArt)) {
      $serviceByPack = PackPrices::getPackPriceByPackId($dataBundle->$infoArt->id, $statusList);

      if (!empty($serviceByPack)) {
      //Se verifica si el servicio es de pago recurrente o no
      $infoService = Service::getService($serviceByPack->service_id, $statusList);
      if (empty($infoService)) {
      return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio combo de " . $value . " (CF3228)", 'icon' => "warning", 'code' => "EMP_SER"];
      }
      $is_bundle = $infoService->is_bundle;
      //Evaluamos que sea de alta con pago recurrente y obtenemos el servicio con que queda recargando
      if ($infoService->for_subscription == 'Y' &&
      $infoService->type == 'A' &&
      !empty($infoService->service_recharge)) {

      $service_recharge = $infoService->service_recharge;
      } else {
      return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se pudo obtener el servicio de recarga del producto de " . $value . " que se dara de alta para el combo '" . $infoService->title . "', posiblemente aun no se ha configurado (CF3238)", 'icon' => "warning", 'code' => "FAI_CNF"];
      }
      $packprices = PackPrices::getServiceByPack($dataBundle->$infoArt->id, $serviceByPack->service_id, $statusList);

      if (empty($packprices)) {
      return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del precio pagado del paquete de " . $value . " del combo (CF3243)", 'icon' => "warning", 'code' => "EMP_PAK"];
      }
      } else {
      return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio de " . $value . " del combo (CF3246)", 'icon' => "warning", 'code' => "EMP_SRP"];
      }
      }
      }
       */
///////////////////////////////
    } else {
      //Log::info('Revision del un plan de fibra sin bundle y con susbcripcion ');
      $serviceByPack = PackPrices::getPackPriceByPackId($request->plan, $statusList);

      if (!empty($serviceByPack)) {
        //Se verifica si el servicio es de pago recurrente o no
        $serviceID = $serviceByPack->service_id;
        $infoService = Service::getService($serviceByPack->service_id, $statusList);

        if (empty($infoService)) {
          return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio de fibra (CF3260)", 'icon' => "warning", 'code' => "EMP_SER"];
        }
        //Deberia ser N
        $is_bundle = $infoService->is_bundle;
        //Evaluamos que sea de alta con pago recurrente y obtenemos el servicio con que queda recargando
        if ($infoService->for_subscription == 'Y' &&
          $infoService->type == 'A' &&
          !empty($infoService->service_recharge)) {

          $service_recharge = $infoService->service_recharge;
          $title_payment = $infoService->title;
        } else {
          return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se pudo obtener el servicio de recarga del producto de fibra que se dara de alta '" . $infoService->title . "', posiblemente aun no se ha configurado (CF3272)", 'icon' => "warning", 'code' => "FAI_CNF"];
        }
        $packprices = PackPrices::getServiceByPack($request->plan, $serviceByPack->service_id, $statusList);

        if (!empty($packprices)) {
          $packpricesID = $packprices->id;
          $mount_payment = $packprices->total_price;

        } else {
          return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del precio pagado del producto de fibra (CF3281)", 'icon' => "warning", 'code' => "EMP_PAK"];
        }
      } else {
        return ['success' => false, 'title' => "No se pudo procesar la solicitud", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio de fibra (CF3284)", 'icon' => "warning", 'code' => "EMP_SRP"];
      }
    }
    return ['success' => true, 'title' => "Paquete y servicio OK", 'msg' => "OK", 'icon' => "success", 'code' => "OK", 'packpricesID' => $packpricesID, 'serviceID' => $serviceID, 'mount_payment' => $mount_payment, 'title_payment' => $title_payment, 'is_bundle' => $is_bundle];
  }
/**
 * [newUrlPayment Se realiza la peticion de una nueva url de pago]
 * @param  [type] $infoCita [description]
 * @return [type]           [description]
 */
  private function newUrlPayment($request, $infoCita, $statusPayment, $email_client, $statusList = ['A', 'I'])
  {
    //El plan que se cobrara es de alta, se debe consultar el plan asociado a la alta para que se suscriba el plan de recarga

    $service_recharge = '';

    if (isset($request->infoAllBundle) && !empty($request->infoAllBundle)) {
      //Log::info('Se generar un QR de pago para Bundle CF3335');
      $optVerif = self::verifyServiceRecharge($request, $request->infoAllBundle);
    } else {
      //Log::info('Se generar un QR de pago SIN bundle CF3338');
      $optVerif = self::verifyServiceRecharge($request);
    }

    if (!$optVerif['success']) {
      return $optVerif;
    }
    $is_bundle = $optVerif['is_bundle'];
    $packpricesID = $optVerif['packpricesID'];
    $serviceID = $optVerif['serviceID'];
    $mount_payment = $optVerif['mount_payment'];
    $title_payment = $optVerif['title_payment'];

    if (empty($infoCita->payment_url_subscription)) {
      $unique = uniqid('FIB-') . time();
    } else {
      $unique = $infoCita->unique_transaction;
    }

    $DataSend = [
      'reference' => $unique,
      'subscription' => 'Y',
      'email' => $email_client,
      'services' => [
        [
          'description' => $title_payment,
          'amount' => $mount_payment,
          'type' => 'A', //Alta
          'bundle' => $is_bundle,
          'installation' => $infoCita->id,
          'client_dni' => $infoCita->clients_dni,
          'subscription' => 'Y']]];

    if ($is_bundle == 'Y') {
      $DataSend['services'][0]['bundle_id'] = $serviceID;
    } else {
      $DataSend['services'][0]['service'] = $serviceID;
      $DataSend['services'][0]['pack_price'] = $packpricesID;
    }
    //Log::info('DATA SEND GENERATE QR ' . (String) json_encode($DataSend));

    $requestNewUrl = ApiMIT::sendRequest("generate", $DataSend);

    //start de TESTING sin API
    //
    /* $requestNewUrl = [
    'success' => true,
    'data' => json_decode(json_encode([
    'cd_response' => 'success',
    'nb_url' => "https://u.mitec.com.mx/p/i/JIJVSPO7",
    ]))];*/
    //
    //End de TESTING sin API

    if ($requestNewUrl['success'] && $requestNewUrl['data']->cd_response == "success") {

      if (isset($requestNewUrl['data']->nb_url) && !empty($requestNewUrl['data']->nb_url)) {

        $QrPayment = ('' . \QrCode::format('svg')->size(310)->generate($requestNewUrl['data']->nb_url));

        $uniq = Installations::asigneSubscriptionInfo($infoCita->id,
          $unique,
          $requestNewUrl['data']->nb_url,
          ($is_bundle == 'N') ? $packpricesID : null,
          $email_client,
          ($is_bundle == 'Y') ? $serviceID : null);

        if (!$uniq['success']) {
          $response = ['success' => false, 'msg' => $uniq['msg'], 'code' => "ERR_DB"];
        } else {
          //Creo enlace para compartir enlace
          $sharePayment = self::createSharePayment($infoCita->id, $requestNewUrl['data']->nb_url);

          $is_pending = true;
          if ($is_bundle == 'N') {
            $packpricesORbundleID = Common::codificarBase64($packpricesID);
          } else {
            $packpricesORbundleID = Common::codificarBase64($serviceID);
          }
          $is_bundle = Common::codificarBase64($is_bundle); //Y o N en base64
          $html = view('fiber.payment_subscribe', compact('QrPayment', 'statusPayment', 'email_client', 'is_pending', 'sharePayment', 'packpricesORbundleID', 'is_bundle'))->render();

          $response = ['success' => true, 'html' => $html];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => "No se pudo conocer la url de pago (CF3390)", 'icon' => "warning", 'code' => "FAI_URL"];
      }
    } else {
      $msg_mit = $requestNewUrl['msg-MIT'];
      if (isset($infomit->nb_response) && !empty($infomit->nb_response)) {
        $msg_mit = $infomit->nb_response;
      }

      $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => $requestNewUrl['msg'] . " " . $msg_mit . " (CF3398)", 'icon' => "warning", 'code' => "ERR_MIT"];
    }
    return $response;
  }
/**
 * [getPaymentSubscrip El instalador ofrece un plan con pago recurrente en el momento que esta instalando cuando desde la cita le ofrecieron un plan sin pago recurrente]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getPaymentSubscrip(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => "Faltan datos para procesar la peticion", 'icon' => "warning"];

      $this->validate($request, [
        'id' => 'required|integer',
        'plan' => 'required',
        'isBundle' => 'required']);

      if (!empty($request->plan)) {
        $request->plan = Common::decodificarBase64($request->plan);
      }
      if (!empty($request->isBundle)) {
        $request->isBundle = Common::decodificarBase64($request->isBundle);
      }

      if ($request->isBundle == 'N') {
        $request->bundle_id = null;
      } else {
        //$infoinstall = Installations::getAddressInstalation($request->id);

        $request->bundle_id = $request->plan;
      }
      $response = self::processUrlPayment($request);
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [processUrlPayment Analiza al momento de cargar si debe solicitar un enlace de pago o solo mostrar el que esta en espera de pago]
 * @param  [type] $request [description]
 * @return [type]          [description]
 */
  private function processUrlPayment($request, $statusList = ['A'])
  {
    //$request = new \stdClass;
    //$request->id = 1; //id de la cita
    //$request->plan = 1; //id del pack
    //$request->bundle_id = id:null; //Si es un bundle trae el id de lo contrario null
    $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda de la cita", 'icon' => "warning", 'code' => 'EMP_INS'];
    //Consulto la instalacion

    $infoCita = Installations::getDateDetailByID($request->id);

    if (!empty($infoCita)) {
      $dataClient = Client::getClientByDNI($infoCita->clients_dni);

      if (!empty($dataClient)) {
        if (!empty($dataClient->email)) {
          //Revisamos el tipo de plan.
          if (!empty($request->bundle_id)) {
            //Log::info('Procesado de QR de pago para paquete bundle C3462');

            $infPack = Bundle::getDetailBundleAlta($request->bundle_id, $statusList);
            $infoAllBundle = json_decode(json_encode($infPack['data']));
            if ($infPack['success'] && $infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {
              $request->plan = $infoAllBundle->info_F->id;
              $request->infoAllBundle = $infoAllBundle;
            } else {
              return ['success' => false, 'title' => "No se pudo procesar el QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo (CF3503)", 'icon' => "warning", 'code' => 'EMP_PAK'];
            }
          }

          $serviceByPack = PackPrices::getPackPriceByPackId($request->plan, $statusList);

          if (empty($serviceByPack)) {
            return ['success' => false, 'title' => "No se pudo procesar el QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra", 'icon' => "warning", 'code' => 'EMP_PAK'];
          }

          $infoService = Service::getService($serviceByPack->service_id, $statusList);
          if (empty($infoService)) {
            return ['success' => false, 'title' => "No se pudo procesar el QR de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio de fibra", 'icon' => "warning", 'code' => 'EMP_SER'];
          }

          //NOTA: Cuando se habilite la opcion de pagar una subscripcion con bundle se habilita la posibilidad de ofrecer un cambio de plan o de crear un qr de pago
          ///
          ///
          if ($infoService->for_subscription == 'Y' /*&& empty($request->bundle_id)*/) {
            $is_pending = true;
            if (empty($infoCita->payment_url_subscription)) {
              $statusPayment = "Enlace nuevo para pagar";
              $response = self::newUrlPayment($request, $infoCita, $statusPayment, $dataClient->email);
            } else {
              //Ya tiene registro de URL, miramos el status del pago
              $infoPayment = self::ChekingPaymentMIT($infoCita->unique_transaction);

              $QrPayment = '';

              if ($infoPayment['success']) {
                if ($infoPayment['status_pay'] == 'APPROVED') {
                  $statusPayment = $infoPayment['status_descrip'];
                  $is_pending = false;
                  $QrPayment = self::getIcon('like');
                  $email_client = '';

                  //Aca se podria actualizar los datos del plan que tiene configurado si desde la cita el plan no era de susbcripcion
                  //Para ello se debe verificar que tipo de servicio esta en la cita, si es un servicio sin subscripcion se envia true caso contrario y desde la cita es subcrito no se debe cambiar nada y se envia false

                  $serviceFirst = Service::getService($infoCita->service_id, $statusList);
                  if (!empty($serviceFirst)) {
                    if ($serviceFirst->for_subscription == 'N') {
                      $changerPlan = true;
                    } else {
                      $changerPlan = false;
                    }
                    $UpdateCita = Installations::setMethodPayment($request->id, 'CARD', $changerPlan);

                    if (!$UpdateCita['success']) {
                      $response = ['success' => false, 'title' => "La cita esta pagada, pero se presento un problema al actualizar los datos del plan y su forma de pago", 'msg' => $UpdateCita['msg'], 'icon' => "warning", 'code' => $UpdateCita['code']];
                    } else {
                      $packpricesORbundleID = '';
                      $is_bundle = '';

                      if (!empty($infoCita->pack_price_id)) {
                        //id de cita de plan de subscripcion para fibra sola
                        $is_bundle = 'N';
                        $packpricesORbundleID = Common::codificarBase64($infoCita->pack_price_id);
                      } elseif (!empty($infoCita->bundle_id_payment)) {
                        //id de cita de plan de subscripcion para fibra con bundle
                        $is_bundle = 'Y';
                        $packpricesORbundleID = Common::codificarBase64($infoCita->bundle_id_payment);
                      }
                      $is_bundle = Common::codificarBase64($is_bundle); //Y o N en base64

                      $html = view('fiber.payment_subscribe', compact('QrPayment', 'statusPayment', 'email_client', 'is_pending', 'packpricesORbundleID', 'is_bundle'))->render();

                      $response = ['success' => true, 'html' => $html];
                    }
                  } else {
                    $response = ['success' => false, 'title' => "Se presento un problema en la busqueda del servicio", 'msg' => "El servicio configurado inicialmente en la cita no se encuentra en el sistema (CF3542)", 'icon' => "warning", 'code' => $UpdateCita['code']];
                  }
                } else {
                  if ($infoPayment['status_pay'] == 'REJECTED') {
                    $statusPayment = "El pago esta fue rechazado por la pasarela de pago.";
                  } else {
                    $statusPayment = $infoPayment['status_descrip'];
                  }

                  $QrPayment = ('' . \QrCode::format('svg')->size(310)->generate($infoCita->payment_url_subscription));
                  $sharePayment = self::createSharePayment($infoCita->id, $infoCita->payment_url_subscription);

                  $email_client = (!empty($infoCita->payer_email)) ? $infoCita->payer_email : $dataClient->email;

                  $packpricesORbundleID = "";
                  $is_bundle = '';
                  if (!empty($infoCita->pack_price_id)) {
                    //id de cita de plan de subscripcion para fibra sola
                    $is_bundle = 'N';
                    $packpricesORbundleID = Common::codificarBase64($infoCita->pack_price_id);
                  } elseif (!empty($infoCita->bundle_id_payment)) {
                    //id de cita de plan de subscripcion para fibra con bundle
                    $is_bundle = 'Y';
                    $packpricesORbundleID = Common::codificarBase64($infoCita->bundle_id_payment);
                  }

                  $is_pending = true;
                  $is_bundle = Common::codificarBase64($is_bundle); //Y o N en base64

                  $html = view('fiber.payment_subscribe', compact('QrPayment', 'statusPayment', 'email_client', 'is_pending', 'sharePayment', 'packpricesORbundleID', 'is_bundle'))->render();

                  $response = ['success' => true, 'html' => $html];
                }
              } else {
                $response = ['success' => false, 'title' => $infoPayment['title'], 'msg' => $infoPayment['status_descrip'], 'icon' => $infoPayment['icon'], 'code' => $infoPayment['code']];
              }
            }
          } else {
            //No se necesita generar QR
            $response = ['success' => true, 'html' => ''];
          }
        } else {

          $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => "El cliente no posee correo registrado en netwey, es el momento de registrar uno", 'icon' => "warning", 'code' => 'EMP_MAI'];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo generar el QR de pago", 'msg' => "La informacion del cliente no pudo se encontrada en el sistema", 'icon' => "warning", 'code' => 'EMP_CLI'];
      }
    }
    return $response;
  }

  private function getIcon($icon)
  {
    //opciones:
    //like
    switch ($icon) {
      case 'like':
        $ico = '<svg version="1.1" viewBox="0 0 480 480" id="svg24" sodipodi:docname="tarjetaok" inkscape:export-filename="tajetaok.png" inkscape:export-xdpi="96" inkscape:export-ydpi="96" inkscape:version="1.2.2 (732a01da63, 2022-12-09, custom)" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" width="480" height="480"><defs id="defs28"/><sodipodi:namedview id="namedview26" pagecolor="#ffffff" bordercolor="#000000" borderopacity="0.25" inkscape:showpageshadow="2" inkscape:pageopacity="0.0" inkscape:pagecheckerboard="0" inkscape:deskcolor="#d1d1d1" showgrid="false" inkscape:zoom="0.67" inkscape:cx="640.29851" inkscape:cy="480.59701" inkscape:window-width="1366" inkscape:window-height="685" inkscape:window-x="0" inkscape:window-y="0" inkscape:window-maximized="1" inkscape:current-layer="svg24"/><path fill="#4299e6" d="M399.752 194.4H103.78a0.18 0.18 0 0 1 -0.18 -0.18q0.004 -32.296 -0.004 -64.624 0 -5.564 0.456 -7.972c1.664 -8.76 8.492 -15.864 17.272 -17.864q2.448 -0.556 7.248 -0.556 134.64 -0.012 269.28 0 4.752 0 7.192 0.556c10.616 2.42 17.712 11.72 17.72 22.64q0.036 90.9 0.016 181.8c-0.004 10.856 -7.332 20.12 -17.952 22.44q-2.56 0.56 -7.952 0.56H240.6a0.204 0.2 90 0 1 -0.2 -0.204V308.6a0.2 0.2 0 0 1 0.2 -0.2h159.2a0.2 0.2 0 0 0 0.2 -0.2V194.648a0.248 0.248 0 0 0 -0.248 -0.248Zm0.248 -68.272a0.128 0.128 0 0 0 -0.128 -0.128H126.528a0.128 0.128 0 0 0 -0.128 0.128v22.544a0.128 0.128 0 0 0 0.128 0.128h273.344a0.128 0.128 0 0 0 0.128 -0.128v-22.544Z" id="path18" style="fill:#28a745;fill-opacity:1"/><path fill="#4299e6" d="M217.592 296.992a79.796 79.796 0 0 1 -79.796 79.796A79.796 79.796 0 0 1 58 296.992a79.796 79.796 0 0 1 79.796 -79.796 79.796 79.796 0 0 1 79.796 79.796Zm-25.796 -26.564q-6.712 -6.888 -13.4 -13.452a0.444 0.444 0 0 0 -0.624 0L121.876 312.876a0.32 0.316 44.7 0 1 -0.452 0l-23.744 -23.752a0.248 0.248 0 0 0 -0.352 0l-15.78 15.772a0.216 0.216 0 0 0 0 0.304l40.076 40.076a0.108 0.108 0 0 0 0.152 0l72.204 -72.208a0.26 0.26 0 0 0 0.036 -0.324c-0.612 -0.952 -1.664 -1.744 -2.22 -2.316Z" id="path20" style="fill:#28a745;fill-opacity:1"/><path fill="#4299e6" d="M320.392 225.94c22.736 -20.244 57.812 -2.9 56.732 26.784 -0.616 16.944 -13.836 31.104 -30.776 32.652q-14.576 1.336 -25.896 -8.472a0.376 0.376 0 0 0 -0.496 0c-20.864 17.964 -52.764 6.232 -56.472 -21.204 -2.596 -19.204 12.032 -37.136 31.484 -38.36q14.092 -0.888 25.06 8.6a0.276 0.272 -44.6 0 0 0.364 0Z" id="path22" style="fill:#28a745;fill-opacity:1"/></svg>';
        break;
      default:
        $ico = "Icono no definido";
        break;
    }
    return $ico;
  }

/**
 * [changerInCash El instalador decidio cambiar el pago a efectivo, se pierde la opcion de la pago recurrente]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function changerInCash(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo cambiar la forma de pago a efectivo", 'msg' => "Faltan datos para verificar la cita", 'icon' => "warning", 'code' => 'ERR_DAT'];

      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF3581)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF3582)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      $dataCite = Installations::getInstallToEdit($request->id);
      if (!empty($dataCite)) {
        //Se cancela ante MIT
        $canceler = self::cancelQRPayment($request, true);

        if ($canceler['success']) {

          //Si se desea pasar a efectivo y se acredito el pago no tiene sentido cambiar la forma de pago
          $dataCite = Installations::getInstallToEdit($request->id);
          if (!empty($dataCite)) {

            //Se revisa que tipo de plan es el que tenia planificado en la cita
            //Se verifica si el servicio es de pago recurrente o no
            $infoService = Service::getService($dataCite->service_id, ['A', 'I']);

            $htmlPlanes = "";
            if (!empty($infoService) && $infoService->for_subscription == 'Y') {
              //Significa que se debe cambiar a un plan activo tradicional
              //Se debe verificar segun si el plan es forzado o normal para ofrecer un plan sin pago recurrente(recarga manual) con el mismo modo de contrato

              $isMig = false;
              if (hasPermit('MIG-FIB')) {
                $isMig = ClientNetwey::isClientByTypes($request->dni, ['H', 'M', 'MH']) > 0 ? true : false;
              }
              $packs = Pack::getFiberPacks($dataCite->id_fiber_zone, $isMig, $infoService->is_payment_forcer, 'N', $infoService->is_bundle);
              //Si es migración pero no se consigue ningun pack para migración se buscan los packs normales
              if ($isMig && $packs->count() == 0) {
                if ($init) {
                  $packs = Pack::getFiberPacks($request->oltid, false);
                } else {
                  $packs = Pack::getFiberPacks($request->oltid, false, $infoService->is_payment_forcer, 'N', $infoService->is_bundle);
                }
              }
              $packs = $packs->filter(function ($val, $key) {
                if (!empty($val->date_ini) && !empty($val->date_end)) {
                  if (strtotime($val->date_ini) <= time() && strtotime($val->date_end) >= time()) {
                    return true;
                  }
                } elseif (!empty($val->date_ini)) {
                  if (strtotime($val->date_ini) <= time()) {
                    return true;
                  }
                } elseif (!empty($val->date_end)) {
                  if (strtotime($val->date_end) >= time()) {
                    return true;
                  }
                } elseif (empty($val->date_ini) && empty($val->date_end)) {
                  return true;
                }
              });
              $view = 'init';
              $htmlPlanes = view('fiber.Select_plan',
                compact('view', 'packs'))->render();
            }
            $response = ['success' => true, 'title' => "Proceso de pago recurrente ha sido cancelado", 'msg' => "Si cambia de parecer el cliente y desea un plan con pago recurrente desde el primer día aun esta a tiempo de volver a intentarlo antes de proceder con el alta", 'icon' => "success", 'htmlPlanes' => $htmlPlanes];
          }
        } else {
          $response = ['success' => false, 'title' => $canceler['title'], 'msg' => $canceler['msg'], 'icon' => $canceler['icon']];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => "No se encontro detalles de la cita de instalación (CF3704)", 'icon' => "warning"];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [cancelQRPayment Cancela ante MP un enlace de pago]
 * @param  Request $request [se envia el id de la cita]
 * @param  boolean $interno [indica si se llama dentro del mismo controlador el metodo o no]
 * @return [type]           [description]
 */
  public function cancelQRPayment(Request $request, $interno = false, $infoPayment = false)
  {
    if (($request->isMethod('post') && $request->ajax()) || $interno) {

      $validate = $this->validate($request, [
        'id' => 'required|integer']);

      $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => "Faltan datos para cancelar el registro", 'icon' => "warning", 'code' => 'ERR_DAT'];

      if ($validate) {

        $dataCite = Installations::getInstallToEdit($request->id);

        if (!empty($dataCite)) {

          if (!empty($dataCite->payment_url_subscription)) {

            if (!$infoPayment) {
              $infoPayment = self::ChekingPaymentMIT($dataCite->unique_transaction);
            } else {
              $infoPayment = $infoPayment;
            }
            if ($infoPayment['success']) {
              if ($infoPayment['status_pay'] != 'APPROVED' /*&&
            $infoPayment['status_pay'] != 'REJECTED'*/) {
                //No se cancela si se pago y tampoco las que las pasarela de pago cancelo por temas de estadistica de netwey

                $DataSend = [
                  'reference' => $dataCite->unique_transaction];

                $requestCancelPay = ApiMIT::sendRequest('subscriptions/delete', $DataSend, 'DELETE');

                $next = false;
                if (!$interno) {
                  $next = true;
                }

                if (($requestCancelPay['success'] && !empty($requestCancelPay['data'][0])) || $next) {

                  $declarer = false;
                  $is_cancel = false;

                  if (!$next) {
                    if (isset($requestCancelPay['data'][0]->status)) {
                      $declarer = true;
                      if (strtoupper($requestCancelPay['data'][0]->status) == 'T') {
                        $is_cancel = true;
                      }
                    }
                  }

                  if ($declarer || $next) {
                    if ($is_cancel || $next) {

                      $ind = Installations::asigneSubscriptionInfo($request->id,
                        null,
                        null,
                        null,
                        null,
                        null);
                      if (!$ind['success']) {
                        $response = ['success' => false, 'title' => "Enlace de pago no cancelado", 'msg' => $ind['msg'], 'icon' => "warning", 'code' => 'ERR_DB'];
                      } else {
                        $UpdatePayment = Installations::setMethodPayment($request->id, 'CASH', false);

                        if (!$UpdatePayment['success']) {
                          $response = ['success' => false, 'title' => "Los datos de la subscripción se setearon correctamente, pero se presento un problema al actualizar la forma de pago", 'msg' => $UpdatePayment['msg'], 'icon' => "warning", 'code' => $UpdatePayment['code']];
                        } else {
                          $response = ['success' => true, 'title' => "Enlace de pago cancelado", 'msg' => "El enlace de pago se descarto de forma exitosa", 'icon' => "success", 'code' => 'OK_CANCEL'];
                        }
                      }
                    } else {
                      $response = ['success' => false, 'title' => "Enlace de pago no cancelado", 'msg' => "No se obtuvo confirmación de cancelación ante la pasarela de pago, intenta nuevamente", 'icon' => "warning", 'code' => 'ERR_MIT'];
                    }
                  } else {
                    $response = ['success' => false, 'title' => "Enlace de pago no cancelado", 'msg' => "Se obtuvo una respuesta no esperada en la cancelación de la pasarela de pago, intenta nuevamente", 'icon' => "warning", 'code' => 'ERR_MIT'];
                  }
                } else {
                  $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => $requestCancelPay['msg'] . " " . $requestCancelPay['msg-MIT'] . " (CF3795)", 'icon' => "warning", 'code' => 'ERR_MIT'];
                }
              } else {
                //if ($infoPayment['status_pay'] == 'APPROVED') {
                $response = ['success' => false, 'title' => "No se pudo cancelar el enlace de pago", 'msg' => "El pago de la cita de instalación figura como acreditado ante la pasarela de pago. Puedes continuar con el proceso de alta", 'icon' => "warning", 'code' => 'OK_PAY'];
                /* } else {
              //if($infoPayment['status_pay'] == 'REJECTED')
              //Aca solo renuevo el enlace pero no lo elimino ante api intermedia
              $response = ['success' => false, 'title' => "No se pudo cancelar el enlace de pago", 'msg' => "La pago de la cita de instalación figura como cancelado por la pasarela de pago. ", 'icon' => "warning", 'code' => 'CANCEL_PAY'];
              }*/
              }
            } else {
              $response = ['success' => false, 'title' => $infoPayment['title'], 'msg' => $infoPayment['status_descrip'], 'icon' => $infoPayment['icon'], 'code' => $infoPayment['code']];
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => 'No hay registros de enlace de pago que se puedan cancelar', 'icon' => "warning", 'code' => 'EMP_QR'];
          }
        } else {
          $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => "No se encontro detalles de la cita", 'icon' => "warning", 'code' => 'EMP_INS'];
        }
      }
      if (!$interno) {
        return response()->json($response);
      } else {
        return $response;
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [reloadQrPayment regeneracion de enlace de pago]
 * @param  Request $request [description]
 * @param  boolean $interno [description]
 * @return [type]           [description]
 */
  public function reloadQrPayment(Request $request, $interno = false, $statusList = ['A', 'I'])
  {
    if (($request->isMethod('post') && $request->ajax()) || $interno) {
      $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Faltan datos para renovar el QR de pago", 'icon' => "warning", 'code' => 'ERR_DAT'];

      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF3788)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF3789)',
        'packprices.required' => 'El detalles del paquete de subscripcion es requerida (IVF3790)',
        'isBundle.required' => 'Se debe indicar si el paquete que se informo corresponde para bundle o no (IVF3791)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
        'packprices' => array(
          'required'),
        'isBundle' => array(
          'required'),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }
      //Capturamos el plan actual del QR
      $infoCita = Installations::getInstallToEdit($request->id);
      if (!empty($infoCita)) {

        $dataClient = Client::getClientByDNI($infoCita->clients_dni);
        if (!empty($dataClient)) {

          if (!empty($dataClient->email) || !empty($request->newmail)) {

            if (!empty($request->newmail)) {
              //existe link pero cambio correo
              $correoMP = $request->newmail;
            } else {
              $correoMP = $dataClient->email;
            }

            $request->packprices = Common::decodificarBase64($request->packprices);
            $is_bundle = Common::decodificarBase64($request->isBundle);

            if ($is_bundle == 'Y') {
              //Log::info('Fibra Bundle');
              $infPack = Bundle::getDetailBundleAlta($request->packprices, $statusList);

              if ($infPack['success']) {
                $infoAllBundle = json_decode(json_encode($infPack['data']));
                if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {
                  $request->plan = $infoAllBundle->info_F->id;
                  $request->infoAllBundle = $infoAllBundle;
                } else {
                  $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo (CF3909)", 'icon' => "warning", 'code' => 'EMP_PAK'];
                }
              } else {
                $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del bundle de susbcripcion ofrecido. Info: " . $infPack['data'], 'icon' => "warning", 'code' => 'EMP_BUN'];
              }
            }

            if (!empty($infoCita->payment_url_subscription)) {
              //Cancelamos el actual Qr si aun no se ha pagado
              $canceler = self::cancelQRPayment($request, true);
              //Se cancela de forma exitosa o ya el id estaba cancelado
              if ($canceler['success']) {
                //obtenemos el pack que tenia el qr
                // Log::info('CANCELACION EXITOSA');

                if ($is_bundle == 'N') {
                  //Log::info('Fibra Individual');
                  $packprice = PackPrices::getPackPriceDetail($request->packprices, $statusList);
                  if (!empty($packprice)) {
                    $request->plan = $packprice->pack_id;
                  } else {
                    $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del plan de susbcripcion ofrecido (CF3943)", 'icon' => "warning", 'code' => 'EMP_PAK'];
                  }
                }

                if (isset($request->plan)) {
                  $statusPayment = "*";
                  //Se vuelve a buscar el registro ya que como se cancelo algunos campos cambiaron
                  $infoCita = Installations::getInstallToEdit($request->id);
                  if (!empty($infoCita)) {
                    // Log::info('SE ANDA PIDIENDO UNO NUEVO POR RENOVACION');
                    $response = self::newUrlPayment($request, $infoCita, $statusPayment, $correoMP);
                  } else {
                    $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se encuentra la cita actualizada en el sistema (CF3923)", 'icon' => "warning", 'code' => 'EMP_INS'];
                  }
                } else {
                  //No es necesario establer el else ya que $response tiene la info xq no hay valor en $request->plan
                }
              } else {
                $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => $canceler['msg'], 'icon' => "warning", 'code' => 'ERR_MP'];
              }
            } else {
              //si llego es xq es el plan original y no se generado aun url de pago y posible se deba a un cambio de correo
              $statusPayment = "*";

              $NEXT = true;
              if ($is_bundle == 'N') {
                $packprice = PackPrices::getPackPriceDetail($request->packprices, $statusList);
                if (!empty($packprice)) {
                  $request->plan = $packprice->pack_id;
                } else {
                  $NEXT = false;
                  $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se pudo obtener el pack de pago recurrente (CF3974)", 'icon' => "warning", 'code' => 'EMP_PAK'];
                }
              }
              if ($NEXT) {
                $response = self::newUrlPayment($request, $infoCita, $statusPayment, $correoMP);
              }
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "El cliente en este momento no posee correo electronico registrado en netwey, recomendamos registrar uno para continuar (CF3982)", 'icon' => "warning", 'code' => 'EMP_MAI'];
          }
        } else {
          $response = ['success' => false, 'title' => "No se pudo cancelar la forma de pago", 'msg' => "No se obtuvo detalle del cliente (CF3947)", 'icon' => "warning", 'code' => 'EMP_CLI'];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se encuentra la cita en el sistema (CF3950)", 'icon' => "warning", 'code' => 'EMP_INS'];
      }

      if (!$interno) {
        return response()->json($response);
      } else {
        return $response;
      }
    }
    return redirect()->route('dashboard');
  }

  private function getInfoMailQr($pack_id, $service_id, $dataCita, $price = false, $statusList = ['A'], $isBundle = 'N')
  {
    $dataClient = Client::getClientByDNI($dataCita->clients_dni);
    if (!empty($dataClient)) {

      if ($isBundle == 'N') {
        if (!$price) {
          $packprice = PackPrices::getServiceByPack($pack_id, $service_id, $statusList);
          if (empty($packprice)) {
            return ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo de la pasarela de pago", 'msg' => "No se pudo obtener el detalle del paquete y el servicio de pago recurrente (CF4130)", 'icon' => "warning", 'code' => 'EMP_PAK'];
          }
        } else {
          $packprice = new \stdClass;
          $packprice->id = $price->id;
          $packprice->total_price = $price->total_price;
        }
        $plan = Pack::getActivePackById($pack_id, $statusList);
        $servicio = Service::getService($service_id, $statusList);

        if (!empty($plan) && !empty($servicio)) {
          $response = ['success' => true, 'client' => $dataClient->name . ' ' . $dataClient->last_name, 'plan' => $plan->title, 'service' => $servicio->title, 'packprice' => Common::codificarBase64($packprice->id), 'mount' => $packprice->total_price, 'email' => $dataClient->email, 'code' => 'OK'];
        } else {
          $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo para la pasarela de pago", 'msg' => "No se pudo obtener el pack o el servicio de pago recurrente (IVF3937)", 'icon' => "warning", 'code' => 'SER_PAK'];
        }
      } else {
        //info base del bundle que se esta renovando correo de pago
        //Aca los parametros se juegan a conveniencia para evitar reprocesado
        $response = ['success' => true, 'client' => $dataClient->name . ' ' . $dataClient->last_name, 'plan' => $pack_id, 'service' => $service_id, 'packprice' => Common::codificarBase64($price->id), 'mount' => $price->total_price, 'email' => $dataClient->email, 'code' => 'OK'];
      }
    } else {
      $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo para la pasarela de pago", 'msg' => "No se obtuvo detalle del cliente (CF3992)", 'icon' => "warning", 'code' => 'EMP_CLI'];
    }

    return $response;
  }

  public function getMailPayment(Request $request, $statusList = ['A', 'I'])
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF3954)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF3955)',
        'packNew.required' => 'El paquete de subscripcion es requerida (IVF3956)',
        'isBundle.required' => 'Se debe indicar si el paquete que se informo corresponde para bundle o no (IVF3957)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
        'packNew' => array(
          'required'),
        'isBundle' => array(
          'required'),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo", 'msg' => "Faltan datos para cambiar el correo para la pasarela de pago (CF4181)", 'icon' => "warning", 'code' => 'ERR_DAT'];

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      $dataCita = Installations::getInstallToEdit($request->id);

      if (!empty($dataCita)) {

        if (!empty($dataCita->pack_price_id)) {
          //Log::info('Procesado para email Fibra Individual');

          $packprice = PackPrices::getPackPriceDetail($dataCita->pack_price_id, $statusList);

          if (!empty($packprice)) {
            $response = self::getInfoMailQr($packprice->pack_id, $packprice->service_id, $dataCita, $packprice, $statusList);
          } else {
            $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo", 'msg' => "No se pudo obtener el detalle del paquete y el servicio de pago recurrente (CF4203)", 'icon' => "warning", 'code' => 'EMP_PAK'];
          }
        } elseif (!empty($dataCita->bundle_id_payment)) {
          //Log::info('Procesado para email Fibra Bundle');
          $infPack = Bundle::getDetailBundleAlta($dataCita->bundle_id_payment, $statusList);

          if ($infPack['success']) {
            $infoAllBundle = json_decode(json_encode($infPack['data']));
            if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {

              $packprice = new \stdClass;
              $packprice->id = $infoAllBundle->general->id;
              $packprice->total_price = $infoAllBundle->general->total_payment;

              $response = self::getInfoMailQr($infoAllBundle->general->title, $infoAllBundle->general->description, $dataCita, $packprice, $statusList, 'Y');

            } else {
              $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo (CF4220)", 'icon' => "warning", 'code' => 'EMP_PAK'];
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del bundle de susbcripcion ofrecido. (CF4223) Info: " . $infPack['data'], 'icon' => "warning", 'code' => 'EMP_BUN'];
          }

        } else {
          //Log::info('Procesado para email por no tener correo');
          //Si llego aca es posible por error del correo, y debo crear la url de pago del servicio que tengo actualmente, se verifica si se trata de un servicio de pago recurrente
          //
          $servicio = Service::getService($dataCita->service_id, $statusList);
          if ($servicio->for_subscription == 'Y') {
            //Significa que la cita posee un plan de subscripcion configurado y se generara por primera vez el enlace y tenia problemas en el correo
            //
            // Log::info('ES una susbcripcion desde la cita 4234');
            if ($request->isBundle == 'N') {
              // Log::info('NO es bundle 4236');

              $response = self::getInfoMailQr($dataCita->pack_id, $dataCita->service_id, $dataCita, false, $statusList, $request->isBundle);

            } else {
              // Log::info('ES bundle 4241');
              $infPack = Bundle::getDetailBundleAlta($dataCita->bundle_id, $statusList);
              if ($infPack['success']) {
                $infoAllBundle = json_decode(json_encode($infPack['data']));
                if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {

                  $packprice = new \stdClass;
                  $packprice->id = $infoAllBundle->general->id;
                  $packprice->total_price = $infoAllBundle->general->total_payment;

                  $response = self::getInfoMailQr($infoAllBundle->general->title, $infoAllBundle->general->description, $dataCita, $packprice, $statusList, 'Y');
                  //Log::info('response 4242 ' . (String) json_encode($response));
                } else {
                  $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo (CF4254)", 'icon' => "warning", 'code' => 'EMP_PAK'];
                }
              } else {
                $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del bundle de susbcripcion configurado. (CF4257) Info: " . $infPack['data'], 'icon' => "warning", 'code' => 'EMP_BUN'];
              }
            }
          } else {
            //El instalador le esta cambiando el plan al cliente a uno de subscripcion
            //Log::info('ES una susbcripcion que hace el instalador 4263');

            if ($request->packNew) {
              //Significa que se esta cambiando el instalador el plan a uno con pago recurrente pero fallo el correo de netwey
              $request->packNew = Common::decodificarBase64($request->packNew);
              //Debo buscar el servicio del nuevo plan
              //Log::info('packNew ' . (String) $request->packNew);

              if ($request->isBundle == 'N') {
                //Log::info('NO bundle 4272');
                $serviceByPack = PackPrices::getPackPriceByPackId($request->packNew);
                if (!empty($serviceByPack)) {
                  $response = self::getInfoMailQr($request->packNew, $serviceByPack->service_id, $dataCita, false, $statusList);
                } else {
                  $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo", 'msg' => "No se encuentra el servicio asociado al nuevo pack en el sistema (CF4276)", 'icon' => "warning", 'code' => 'EMP_SER'];
                }
              } else {
                //Log::info('ES bundle 4281');
                //Paquete bundle con susbcripcion que el instalador le esta ofreciendo como plan nuevo
                $infPack = Bundle::getDetailBundleAlta($request->packNew, $statusList);
                if ($infPack['success']) {
                  $infoAllBundle = json_decode(json_encode($infPack['data']));
                  if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {

                    $packprice = new \stdClass;
                    $packprice->id = $infoAllBundle->general->id;
                    $packprice->total_price = $infoAllBundle->general->total_payment;
                    $response = self::getInfoMailQr($infoAllBundle->general->title, $infoAllBundle->general->description, $dataCita, $packprice, $statusList, 'Y');

                  } else {
                    $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo de susbcripcion que se esta ofreciendo (CF4292)", 'icon' => "warning", 'code' => 'EMP_PAK'];
                  }
                } else {
                  $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del bundle de susbcripcion que ofrece el instalador. (CF4295 Info: " . $infPack['data'], 'icon' => "warning", 'code' => 'EMP_BUN'];
                }
              }
            } else {
              $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo de la pasarela de pago", 'msg' => "No se encuentra el detalle de pago en el sistema (CF4299)", 'icon' => "warning", 'code' => 'EMP_PAY'];
            }
          }
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo cargar información asociada al cliente para la edición del correo de la pasarela de pago", 'msg' => "No se encuentra la cita en el sistema (CF4304)", 'icon' => "warning", 'code' => 'EMP_INS'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [setMailPayment Se informa el nuevo correo con el que se desea pagar el alta de fibra]
 * @param Request $request [description]
 */
  public function setMailPayment(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      //cita
      //Si se guarda en netwey o no
      //correo electronico
      //pack a subscribir O titulo del bundle
      //servicio a subscribir O descripcion del bundle
      //isbundle indicaria el servicio o paquete a renovar correo pertence a un bundle

      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF4072)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF4073)',
        'use_netwey.required' => 'El paquete de subscripcion es requerida (IVF4074)',
        'newmail.required' => 'El nuevo correo a usar en el proceso de pago es requerido (IVF4075)',
        'packprices.required' => 'El identificador del paquete que sera pagado es necesario (IVF4076)',
        'isBundle.required' => 'Se debe indicar si el paquete que se informo corresponde para bundle o no (IVF4077)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
        'use_netwey' => array(
          'required'),
        'newmail' => array(
          'required'),
        'packprices' => array(
          'required'),
        'isBundle' => array(
          'required'),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      $response = ['success' => false, 'title' => "No se pudo actualizar información asociada al correo del proceso de pago del cliente", 'msg' => "Faltan datos para cambiar el correo (CF4148)", 'icon' => "warning", 'code' => 'ERR_DAT'];

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      $dataCita = Installations::getInstallToEdit($request->id);

      if (!empty($dataCita)) {
        $reload = self::reloadQrPayment($request, true);
        //Si no se pago no tiene sentido cambiar el correo
        if ($reload['success']) {
          $response = ['success' => true, 'title' => "Correo actualizado", 'msg' => "Ya que el correo se actualizo se genero un nuevo enlace de pago para procesar nuevamente", 'icon' => "success", 'code' => "OK"];

          $infoCliente = Client::getClientByDNI($dataCita->clients_dni);
          if (!empty($infoCliente)) {
            if (filter_var($request->use_netwey, FILTER_VALIDATE_BOOLEAN) ||
              empty($infoCliente->email)) {
              //Si deseo guardar el email
              $insert = Client::updateInfoContact($dataCita->clients_dni, false, false, $request->newmail);
              if (!$insert['success']) {
                $response = ['success' => false, 'title' => "No se pudo actualizar información asociada al correo del cliente (CF4173)", 'msg' => $insert['msg'], 'icon' => "warning", 'code' => 'FAI_EMA'];
              }
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo actualizar información asociada al correo del cliente", 'msg' => "El cliente no se encuentra en el sistema (CF4177)", 'icon' => "warning", 'code' => 'EMP_INS'];
          }
        } else {
          $response = ['success' => $reload['success'], 'title' => $reload['title'], 'msg' => $reload['msg'], 'icon' => $reload['icon']];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo actualizar información asociada al correo del cliente", 'msg' => "No se encuentra la cita en el sistema", 'icon' => "warning", 'code' => 'EMP_INS'];
      }

      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function setChangerPack(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo actualizar el paquete para la instalación", 'msg' => "Faltan datos para cambiar el paquete", 'icon' => "warning", 'code' => 'ERR_DAT'];

      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF4373)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF4374)',
        'newpack.required' => 'El paquete es requerido (IVF4375)',
        'is_bundle.required' => 'El identificador de bundle del paquete es requerida (IVF4376)',
        'is_bundle.regex' => 'El identificador de bundle del paquete no posee un valor valido (IVF4377)'];

      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
        'newpack' => array(
          'required',
        ),
        'is_bundle' => array(
          'required',
          'regex:/(^([N]|[Y])$)/u',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      $dataCita = Installations::getInstallToEdit($request->id);
      if (!empty($dataCita)) {
        //buscamos el servicio
        $request->newpack = Common::decodificarBase64($request->newpack);
        if ($request->is_bundle == 'Y') {

          $infPack = Bundle::getDetailBundleAlta($request->newpack, ['A']);
          if ($infPack['success']) {
            $infoAllBundle = json_decode(json_encode($infPack['data']));
            if ($infoAllBundle->general->containt_F == 'Y' && isset($infoAllBundle->info_F->id)) {
              $serviceByPack = PackPrices::getPackPriceByPackId($infoAllBundle->info_F->id);
            } else {
              $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del pack de fibra dentro del combo (CF4416)", 'icon' => "warning", 'code' => 'EMP_PAK'];
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Se presento un problema en obtener el detalle del bundle de susbcripcion configurado. (CF4419) Info: " . $infPack['data'], 'icon' => "warning", 'code' => 'EMP_BUN'];
          }
        } else {
          $serviceByPack = PackPrices::getPackPriceByPackId($request->newpack);
        }

        if (!empty($serviceByPack)) {
          $infoService = Service::getService($serviceByPack->service_id);

          $delPayment = ($infoService->for_subscription == 'N') ? true : false;

          if ($request->is_bundle == 'Y') {
            $newpack = $infoAllBundle->info_F->id;
            $newservice = $infoAllBundle->info_F->service_id;
            $totalprice = $infoAllBundle->general->total_payment;
            //Actualizo al hijo del bundle
            //
            if ($infoAllBundle->general->containt_T == 'Y' && isset($infoAllBundle->info_T)) {
              $updateChildren = InstallationsBundle::updateChildrenChangerBundle($request->id,
                'T',
                $infoAllBundle->info_T->id,
                $infoAllBundle->info_T->service_id
              );
              if (!$updateChildren['success']) {
                $response = ['success' => false, 'title' => "No se pudo actualizar el paquete de Telefonia del bundle para la instalación (CF4443)", 'msg' => $objUpd['msg'], 'icon' => "warning"];
                return $response;
              }
            }
          } else {
            $newpack = $request->newpack;
            $newservice = $serviceByPack->service_id;
            $totalprice = $serviceByPack->total_price;
          }

          $objUpd = Installations::setChangerPackService($request->id, $newpack, $newservice, $totalprice, $delPayment);

          if ($objUpd['success']) {
            $response = ['success' => true, 'title' => "El paquete de la instalación fue cambiado exitosamente", 'msg' => "Puedes proceder el alta del servicio con un nuevo paquete ", 'icon' => "success"];
          } else {
            $response = ['success' => false, 'title' => "No se pudo actualizar el paquete para la instalación", 'msg' => $objUpd['msg'], 'icon' => "warning"];
          }
        } else {
          $response = ['success' => false, 'title' => "No se pudo actualizar el paquete para la instalación", 'msg' => "No se obtuvo una respuesta satisfactoria en la busqueda del servicio asociado al paquete (CF4461)", 'icon' => "warning"];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo actualizar el paquete para la instalación", 'msg' => "No se encuentra la cita en el sistema", 'icon' => "warning", 'code' => 'EMP_INS'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function sendMailQrPayment(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $idInstall = Common::decodificarBase64($request->id);

      $notmail = true;
      $infoCita = Installations::getDateDetailByID($idInstall);
      if (!empty($infoCita)) {
        $infoPayment = self::ChekingPaymentMIT($infoCita->unique_transaction);

        if ($infoPayment['success']) {
          if (!empty($infoCita->payment_url_subscription)) {
            $dataClient = Client::getClientByDNI($infoCita->clients_dni);

            if (!empty($dataClient)) {
              if (!empty($dataClient->email) || !empty($infoCita->payer_email)) {

                $urlQr = $infoCita->payment_url_subscription;
                $infodata = [
                  'asunto' => "Enlace de pago para pago recurrente",
                  'client_name' => $dataClient->name,
                  'urlqr' => $urlQr,
                  'bodytext' => "Por favor ingresa al link sumunistrado en la brevedad posible para el pago de servicios de fibra de netwey, una vez el pago sea confirmado podras comenzar a disfrutar los servicios que ofrece Netwey para ti",
                  'nota' => ""];

                try {
                  //SendMailNotifiAlert::generateMail($infodata);

                  if (!empty($infoCita->payer_email)) {
                    $emailUrl = $infoCita->payer_email;
                  } else {
                    $emailUrl = $dataClient->email;
                  }

                  Mail::to($emailUrl)->send(new Mail_paymentQr($infodata));

                  $response = ['success' => true, 'title' => "Correo enviado!", 'msg' => "Por favor informar al cliente '" . $dataClient->name . "' que revise la bandeja de correo electrónico: '" . $emailUrl . "'. Alli vera información de interes sobre el proceso de pago del servicio de pago recurrente.", 'icon' => "success"];

                } catch (\Exception $e) {
                  Log::error('No se pudo enviar el correo del enlace para el pago recurrente: ' . $emailUrl . ' Error: ' . (String) json_encode($e->getMessage()));
                }
              } else {
                $response = ['success' => false, 'title' => "No se pudo enviar el email con el enlace para el pago recurrente", 'msg' => "El cliente no cuenta con correo electronico registrado", 'icon' => "warning"];
              }
            } else {
              $response = ['success' => false, 'title' => "No se pudo enviar el email con el enlace de pago para el pago recurrente", 'msg' => "No se obtuvo detalle del cliente", 'icon' => "warning"];
            }
          } else {
            $response = ['success' => false, 'title' => "No se pudo enviar el email con el enlace de pago para el pago recurrente", 'msg' => "No se obtuvo la URL de pago de la pasarela pago", 'icon' => "warning"];
          }
        } else {
          $response = ['success' => false, 'title' => $infoPayment['title'], 'msg' => $infoPayment['status_descrip'], 'icon' => $infoPayment['icon'], 'code' => $infoPayment['code']];
        }
      } else {
        $response = ['success' => false, 'title' => "No se pudo enviar el email con el enlace de pago para el pago recurrente", 'msg' => "No se encuentra la cita en el sistema", 'icon' => "warning"];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  private function ChekingPaymentMIT($subscription_id = false)
  {
    $response = ['success' => false, 'title' => "No se pudo conocer el status del pago", 'status_descrip' => "Hubo un problema para consultar el status de pago, faltan datos", 'icon' => "warning", 'code' => 'ERR_MIT', 'status_pay' => "FAIL"];

    if ($subscription_id) {
      $DataSend = [
        'reference' => $subscription_id];
    } else {
      return $response;
    }

    $consultationPay = ApiMIT::sendRequest('subscriptions/get', $DataSend, 'GET');
    if ($consultationPay['success']) {
      $response['success'] = true;
      $response['title'] = "Status del pago";
      $response['icon'] = "success";
      $response['code'] = "OK";
      $response['data'] = $consultationPay['data'][0];

      switch (strtoupper($response['data']->status)) {
        case 'I':
          $response['status_pay'] = "PENDING";
          break;
        case 'A':
          $response['status_pay'] = "APPROVED";
          break;
        case 'C': //Rechazada por MIT
          $response['status_pay'] = "REJECTED";
          break;
        case 'P':
          $response['status_pay'] = "IN_PROCESS";
          break;
        case 'T':
          $response['status_pay'] = "DELETED";
          break;
        default:
          $response['status_pay'] = "Estatus de la operacion de pago no conocido";
          break;
      }
      //$response['data']->code_response == '00'
      //Validaciones para asegurar integridad de la respuesta de MIT
      if (isset($response['data']->subscripted[0]->status) &&
        isset($response['status_pay'])) {

        if (strtoupper($response['data']->subscripted[0]->status) != 'A' &&
          $response['status_pay'] == "APPROVED") {

          $response['status_pay'] == "WARNING_APPROVED1";

        } elseif ($response['status_pay'] == "APPROVED" &&
          isset($response['data']->code_response)) {

          if (!empty($response['data']->code_response) && $response['data']->code_response != '00') {
            $response['status_pay'] == "WARNING_APPROVED2";
          }
        }
      } else {
        $response['status_pay'] == "WARNING";
      }

      if (isset($response['data']->code_response) && !empty($response['data']->code_response)) {

        $CodeMIT = DB::table('islim_mit_codes')
          ->select('description')
          ->where('code', $response['data']->code_response)
          ->first();
        if (!empty($CodeMIT)) {
          $response['status_descrip'] = $CodeMIT->description;
        } else {
          $response['status_descrip'] = "Code (" . $response['data']->code_response . ") sin significado conocido";
        }
      } else {
        $response['status_descrip'] = "El enlace no ha sido usado por el cliente";
      }
    } else {
      $response = ['success' => false, 'title' => "No se pudo conocer el status del pago", 'status_descrip' => $consultationPay['msg'] . " " . $consultationPay['msg-MIT'] . " (CF4347)", 'icon' => "warning", 'code' => 'ERR_MIT', 'status_pay' => "FAIL"];
    }
    return $response;
  }

/**
 * [verifyPaymentInstall Verifica si la cita de fibra es con pago con tarjeta e indica si fue pagada o no, en caso de ser cita con pago en efectivo indicar que se debe cobrar en efectivo]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function verifyPayment(Request $request, $datInterno = false, $statusList = ['A', 'I'])
  {
    if (($request->isMethod('post') && $request->ajax())) {

      if (!$datInterno) {
        $response = ['success' => false, 'title' => "No se pudo verificar el enlace de pago", 'msg' => "Faltan datos para verificar el pago", 'icon' => "warning", 'code' => 'ERR_DAT'];

        $messages = [
          'id.required' => 'La cita de fibra es requerida (IVF4316)',
          'id.regex' => 'La cita de fibra no posee un valor valido (IVF4317)',
        ];
        $requisitos = [
          'id' => array(
            'required',
            'regex:/^([0-9])*$/',
          ),
        ];

        $validate = Validator::make($request->all(), $requisitos, $messages);
        $errors = $validate->errors();

        if ($errors->any()) {
          $response['msg'] = "";
          foreach ($errors->all() as $error) {
            $response['msg'] .= $error . ', ';
          }
          return $response;
        }
        $infoCita = Installations::getDateDetailByID($request->id);
      } else {
        $infoCita = $datInterno;
      }

      if (!empty($infoCita)) {
        $infoPack = Pack::getPlanDetailFiber($infoCita->pack_id, $statusList);
        $infoService = Service::getService($infoCita->service_id, $statusList);

        if (empty($infoPack) || empty($infoService)) {
          return ['success' => false, 'title' => "No se puede proseguir aun con el alta", 'msg' => "No se pudo obtener datos del paquete o del servicio configurado en la cita", 'icon' => "warning", 'code' => 'EMP_SRP'];
        }

        $isBundle = empty($infoCita->bundle_id) ? false : true;

        if (empty($infoCita->payment_url_subscription) &&
          $infoService->for_subscription == 'N') {
          //&#128176;
          $response = ['success' => true, 'title' => "Pago en efectivo! 💰", 'msg' => "El servicio es sin pago recurrente (recargas manuales).

          Debes cobrar en efectivo.", 'icon' => "success", 'code' => 'SIN_SUBS', 'service' => $infoService->title, 'pack' => $infoPack->title, 'price' => $infoPack->total_price, 'infoBundle' => $isBundle, 'pay_susbcription' => false];

        } elseif (empty($infoCita->payment_url_subscription) &&
          $infoService->for_subscription == 'Y') {
          //Se deberia hacer una verificacion si es motivado a que no se genero enlace de pago por falta de plan de recarga o si realmente es xq desea pagar en efectivo el cliente
          //
          $response = ['success' => false, 'title' => "Pago en efectivo! 💰", 'msg' => "Estas realizando un alta con pago en efectivo. Recuerda seleccionar el plan de recarga manual que se adapte a tus necesidades y confirma 'Aceptar cambio de plan' antes de continuar", 'icon' => "info", 'code' => 'CHN_SER', 'service' => $infoService->title, 'pack' => $infoPack->title, 'price' => $infoPack->total_price, 'infoBundle' => $isBundle, 'pay_susbcription' => true];
        } else {
          //Si cuenta con url es xq esta tratando de pagar con tarjeta
          $bandChanger = false;
          if ($infoCita->type_payment != 'CARD') {
            //Validacion para evitar que envie la peticion cuando ya se tiene registrado el pago como tarjeta no pierda tiempo volviendo a verificar
            $infoPayment = self::ChekingPaymentMIT($infoCita->unique_transaction);
            $bandChanger = true;
          } else {
            //inicializo los campos para no volver a pedir verificacion del pago
            $infoPayment = [
              'success' => true,
              'status_pay' => "APPROVED",
              'data' => json_decode(json_encode(['code_response' => '00']))];
          }

          if ($infoPayment['success']) {
            if ($infoPayment['status_pay'] == 'APPROVED') {

              if (!empty($infoService)) {
                $textPayment = '';
                if ($bandChanger) {

                  if ($infoService->for_subscription == 'N') {
                    //Significa que originalmente tenia un plan normal y el instalador le cambio su oferta
                    //Aca se le debe cambiar la oferta a la cita
                    $UpdateCita = Installations::setMethodPayment($request->id, 'CARD', true);
                    //Los datos de la cita cambiaron y se debe consultar
                    $infoCita = Installations::getDateDetailByID($request->id);
                    if (!empty($infoCita)) {
                      //En caso de que el paquete o servicio se desactive luego que se agendo pueda continuar
                      $infoPack = Pack::getPlanDetailFiber($infoCita->pack_id, ['A', 'I']);
                      $infoService = Service::getService($infoCita->service_id, ['A', 'I']);
                    }
                  } else {
                    //Actualizacion de pago a tarjeta de un Bundle
                    //Se envio en falso xq no tiene sentido que sea configurado un plan de subscripcion y se page otro plan de subscripcion
                    $UpdateCita = Installations::setMethodPayment($request->id, 'CARD', false);
                  }
                } else {
                  $UpdateCita = ['success' => true];
                  $textPayment = "...";
                }

                if ($UpdateCita['success']) {
                  //&#128179;
                  $response = ['success' => true, 'title' => "El pago fue acreditado! 💳", 'msg' => "El pago de subscripción de la cita de fibra ha sido acreditado y se registro de forma correcta" . $textPayment, 'icon' => "success", 'code' => 'OK_PAY', 'service' => $infoService->title, 'pack' => $infoPack->title, 'price' => $infoPack->total_price, 'infoBundle' => $isBundle, 'pay_susbcription' => true];
                } else {
                  $response = ['success' => false, 'title' => "El pago fue acreditado, pero la actualización de datos no se pudo realizar", 'msg' => $UpdateCita['msg'], 'icon' => "warning", 'code' => $UpdateCita['code']];
                }
              } else {
                $response = ['success' => false, 'title' => "Se presento un problema para continuar el alta", 'msg' => "El pago fue acreditado pero los detalle del servicio que posee la cita no fue encontrado", 'icon' => "warning", 'code' => 'EMP_SER'];
              }
            } else {
              $response = ['success' => false, 'title' => "No se puede proseguir aun con el alta", 'msg' => "El pago aun no ha sido aprobado, verifica con el cliente que halla realizado el pago y vuelve a intentar dar el alta en unos minutos.", 'icon' => "warning", 'code' => 'NOT_PAY'];
            }
          } else {
            $response = ['success' => false, 'title' => $infoPayment['title'], 'msg' => $infoPayment['status_descrip'], 'icon' => $infoPayment['icon'], 'code' => $infoPayment['code']];
          }
        }
      } else {
        $response = ['success' => false, 'title' => "No se puede proseguir aun con el alta", 'msg' => "El detalle de la cita de instalación no fue encontrada", 'icon' => "warning", 'code' => 'EMP_INS'];
      }

      if (!$datInterno) {
        return response()->json($response);
      } else {
        return $response;
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [getInstallerCharges Verifica si fue pagado luego de mostrar el resumen de movimiento de inventario al momento del alta]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getInstallerCharges(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo renovar el enlace de pago", 'msg' => "Faltan datos para verificar el pago", 'icon' => "warning", 'code' => 'ERR_DAT'];

      $messages = [
        'id.required' => 'La cita de fibra es requerida (IVF4446)',
        'id.regex' => 'La cita de fibra no posee un valor valido (IVF4447)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/^([0-9])*$/',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return $response;
      }

      $infoCita = Installations::getDateDetailByID($request->id);
      if (!empty($infoCita)) {
        $dat_Bundle = null;
        if (!empty($infoCita->bundle_id)) {
          //Es una cita con bundle
          $infoAllBundle = Bundle::getDetailBundleAlta($infoCita->bundle_id, ['A', 'I']);
          if ($infoAllBundle['success']) {
            $InfoBundle = json_decode(json_encode($infoAllBundle['data']));

            $dat_Bundle = new \stdClass;
            //Log::info((String) json_encode($InfoBundle));
            $dat_Bundle->pack_title = $InfoBundle->general->title;

            $priceBundle = Bundle::getPriceBundleByObj($InfoBundle);
            if ($priceBundle['success']) {
              $dat_Bundle->price = $priceBundle['price'];

              if ($InfoBundle->general->containt_H == 'Y' && isset($InfoBundle->info_H->id)) {
                $dat_Bundle->product_H = $InfoBundle->info_H->product_title;
                $dat_Bundle->pack_H = $InfoBundle->info_H->title;
              } else {
                $dat_Bundle->product_H = null;
                $dat_Bundle->pack_H = null;
              }

              if ($InfoBundle->general->containt_M == 'Y' && isset($InfoBundle->info_M->id)) {
                $dat_Bundle->product_M = $InfoBundle->info_M->product_title;
                $dat_Bundle->pack_M = $InfoBundle->info_M->title;
              } else {
                $dat_Bundle->product_M = null;
                $dat_Bundle->pack_M = null;
              }

              if ($InfoBundle->general->containt_MH == 'Y' && isset($InfoBundle->info_MH->id)) {
                $dat_Bundle->product_MH = $InfoBundle->info_MH->product_title;
                $dat_Bundle->pack_MH = $InfoBundle->info_MH->title;
              } else {
                $dat_Bundle->product_MH = null;
                $dat_Bundle->pack_MH = null;
              }

              if ($InfoBundle->general->containt_T == 'Y' && isset($InfoBundle->info_T->id)) {
                $dat_Bundle->product_T = $InfoBundle->info_T->product_title;
                $dat_Bundle->pack_T = $InfoBundle->info_T->title;
              } else {
                $dat_Bundle->product_T = null;
                $dat_Bundle->pack_T = null;
              }

              if ($InfoBundle->general->containt_F == 'Y' && isset($InfoBundle->info_F->id)) {
                $dat_Bundle->product_F = $InfoBundle->info_F->product_title;
                $dat_Bundle->pack_F = $InfoBundle->info_F->title;
              } else {
                $dat_Bundle->product_F = null;
                $dat_Bundle->pack_F = null;
              }

              $response = ['success' => true, 'title' => "Debes cobrar la instalación", 'msg' => "El plan es un plan en combo y debes cobrarle al cliente", 'icon' => "success", 'code' => 'EMP_PAY', 'infoBundle' => $dat_Bundle];
            } else {
              $response = ['success' => false, 'title' => "Problema en calculo de precios del combo", 'msg' => $priceBundle['msg'], 'icon' => 'warning', 'code' => $priceBundle['code']];
            }
          } else {
            $response = ['success' => false, 'title' => "Problema con datos del combo", 'msg' => "Se presento un problema en obtener los detalles del combo", 'icon' => 'warning', 'code' => 'EMP_BUN'];
          }
        }
        if (!is_null($dat_Bundle)) {
          if ($response['success']) {
            $response = self::verifyPayment($request, $infoCita);

            if ($response['success']) {
              $response['infoBundle'] = $dat_Bundle;
            }
          }
        } else {
          $response = self::verifyPayment($request, $infoCita);

          if ($response['success']) {
            $response['infoBundle'] = $dat_Bundle;
          }
        }
      } else {
        $response = ['success' => false, 'title' => "No se puede proseguir aun con el alta", 'msg' => "El detalle de la cita de instalación no fue encontrada", 'icon' => "warning", 'code' => 'EMP_INS'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [verifyInfoPort Se verifica si el DN a portar se puede usar para el alta del bundle de telefonia]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function verifyInfoPort(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $validate = $this->validate($request, [
        'msisdn' => 'required|integer']);

      $response = ['success' => false, 'title' => "No se puede proseguir aun con el alta", 'msg' => "Faltan datos para verificar los datos de la portacion", 'icon' => "alert-danger", 'code' => 'ERR_DAT'];
      if ($validate && strlen(trim($request->msisdn)) == 10) {
        $isClient = ClientNetwey::isClient(trim($request->msisdn));
        if ($isClient) {
          $response = ['success' => true, 'title' => "Se puede proseguir pero hay una observacion", 'msg' => "El msisdn " . $request->msisdn . " es un cliente registrado en netwey, pero se procesara un reciclaje de dicho numero", 'icon' => "alert-danger", 'code' => 'ERR_DAT'];
        } else {
          $response = ['success' => true, 'title' => "Se puede proseguir con el alta", 'msg' => "El msisdn " . $request->msisdn . " es valido para la portacion", 'icon' => "alert-success", 'code' => 'OK'];
        }
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function findInventoryAsigned(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      return response()->json(Inventory::getArticleAssigne($request->search, $request->type, 20));
    }
    return redirect()->route('dashboard');
  }

/**
 * [viewProcessFail muestra la causa del error y si cuenta el inventario para realizar el cambio]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function viewProcessFail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo obtener el motivo de la falla de activacion", 'msg' => "Faltan datos", 'icon' => "alert-danger", 'code' => 'ERR_DAT', 'obs' => '', 'inventory' => ''];

      ///New validate
      $messages = [
        'id.required' => 'El id de instalacion de fibra es requerido (IVF4602)',
        'id.regex' => 'El id no cumple el criterio de ser un valor valido (IVF4603)',
        'dn_type.required' => 'El tipo de equipo es requerido (IVF4604)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'dn_type' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      ///New validate

      /*$validate = $this->validate($request, [
      'id' => 'required|integer',
      'dn_type' => 'required']);*/

      if ($request->dn_type != 'F') {
        $infoBun = InstallationsBundle::getChildren($request->id);

        if (!empty($infoBun)) {
          //1- Busco el motivo de la falla
          //2- Busco si tiene dns disponibles del tipo de producto que desea cambiar exectuando el equipo actual con falla, para esto cuando se registro en el bundle para procesar se coloco en status E el producto en inventario por tanto solo se veran aca si estan en status A
          $response['success'] = true;
          $response['obs'] = $infoBun->obs;
          $response['icon'] = 'alert-success';
          $response['code'] = 'OK';

          //verifico el inventario del instalador para realizar el cambio
          $email = [session("user")];
          $disposeInv = User::FilterInstallerInventary($email, $infoBun->pack_id);
          if (count($disposeInv) > 0) {
            $dn_type = $infoBun->dn_type;
            $response['inventory'] = $html = view('fiber.Select_childrenBundle', compact('dn_type'))->render();
            $response['title'] = "Se puede proceder con el cambio de producto que fallo";
            $response['msg'] = "OK";
          } else {
            $response['inventory'] = '<div class="alert alert-danger text-dark"> No se cuenta con inventario para realizar el reemplazo.</div>
              <div class="alert alert-dismissible text-dark"><strong>Nota:</strong> Puedes solicitar inventario, aceptarlo y volver ingresando a: <u><i> ' . $request->root() . '/fiber/installerSurvey/' . $infoBun->installations_id . '</i></u> para terminar el proceso de activacion</div>';
          }
        } else {
          $response['msg'] = "El registro del bundle de tipo " . $request->dn_type . " no se encuentra";
        }
      } else {
        $response['msg'] = "El msisdn de fibra no se puede cambiar";
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [processFail realiza el cambio de producto cuando fallo una activacion del bundle]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function processFail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo procesar el producto que presento fallo de activacion", 'msg' => "Faltan datos", 'icon' => "alert-danger", 'code' => 'ERR_DAT'];

      ///New validate
      $messages = [
        'id.required' => 'El id de instalacion de fibra es requerido (IVF4679)',
        'id.regex' => 'El id no cumple el criterio de ser un valor valido (IVF4680)',
        'dn_type.required' => 'El tipo de equipo es requerido (IVF4681)',
        'inv_detail_id_new.required' => 'El identificador del equipo(msisdn) es requerido (IVF4682)',
        'inv_detail_id_new.regex' => 'El identificador del equipo(msisdn) no cumple el criterio de ser un valor valido (IVF4683)',
      ];
      $requisitos = [
        'id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
        'dn_type' => array(
          'required',
        ),
        'inv_detail_id_new' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      ///New validate
      /*$validate = $this->validate($request, [
      'id' => 'required|integer',
      'dn_type' => 'required',
      'inv_detail_id_new' => 'required']);*/

      //1- se debe verificar que el equipo lo tenga asignado
      //2- Se debe verificar que este disponible y corresponda con el paquete del producto que fallo
      //3- se actualiza el nuevo id de inventario y el status
      //4- se debe actualizar el equipo anterior para que este disponible
      $infoBun = InstallationsBundle::getChildren($request->id);

      if (!empty($infoBun)) {

        $asigne = SellerInventory::getAsignmentUser($request->inv_detail_id_new, session('user'));
        if (!empty($asigne)) {
          $inventoryDetail = Inventory::getDnsById($request->inv_detail_id_new);
          if (!empty($inventoryDetail)) {
            $inventory = Inventory::getArticByIdAndDN($infoBun->pack_id, $inventoryDetail->msisdn);
            if (!empty($inventory)) {
              Inventory::markArticleSale($request->inv_detail_id_new, 'E', 'Reservado para activacion en instalacion bundle');
              Inventory::markArticleSale($infoBun->inv_detail_id, 'A', false);
              $updateBun = InstallationsBundle::updateChildrenForFail($request->id, $request->inv_detail_id_new);
              if ($updateBun['success']) {
                $response['success'] = true;
                $response['title'] = 'Actualizacion de producto';
                $response['msg'] = 'Producto actualizado correctamente';
                $response['icon'] = 'alert-success';
                $response['code'] = 'OK';
              } else {
                $response['msg'] = $updateBun['msg'];
              }
            } else {
              $response['msg'] = "El articulo que se esta tratando de usar para reemplazar no corresponde con el paquete de alta";
            }
          } else {
            $response['msg'] = "El articulo no esta registrado en inventario";
          }
        } else {
          $response['msg'] = "El articulo con que se trata de reemplazar no lo tienes asignado";
        }
      } else {
        $response['msg'] = "El registro del bundle de tipo " . $request->dn_type . " no se encuentra";
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [refresFail Actualiza la lista de productos que conforma el bundle]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function refresFail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo actualizar el status de la activacion de los productos bundle", 'msg' => "Faltan datos", 'icon' => "alert-danger", 'code' => 'ERR_DAT'];

      $validate = $this->validate($request, [
        'id' => 'required|integer']);

      if ($validate) {
        $childrenBundle = InstallationsBundle::getChildrenActive($request->id);
        //Log::info('data ' . (String) json_encode($childrenBundle));
        if (count($childrenBundle)) {
          $id = $request->id;
          $init = false;
          $isCombo = (count($childrenBundle) > 1) ? true : false;
          $htmlBundle = view('fiber.resultBundle', compact('childrenBundle', 'id', 'init', 'isCombo'))->render();
        } else {
          $htmlBundle = '<div class="alert alert-danger text-dark">No se pudo cargar los status de los productos del combo.</div>';
        }
        $response = ['success' => true, 'title' => "Lista de status de productos bundle actualizados", 'msg' => $htmlBundle, 'icon' => "alert-success", 'code' => 'OK'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  /**
   * [refresComponent consulta estatus de un producto en el proceso de instalacion]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function refresComponent(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $response = ['success' => false, 'title' => "No se pudo consultar el status de la activacion", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];

      $validate = $this->validate(
        $request,
        [
          'id' => 'required|integer',
          'config' => 'required|in:master,children',
        ]
      );
      if ($validate) {

        $showMsgProvisioning = 'N';
        if ($request->config == 'master') {
          $component = Installations::getComponent($request->id);
          if (!empty($component->dataProvisioned)) {
            if (!empty($component->dataProvisioned['success'])) {
              if ($component->dataProvisioned['success'] == 'N') {
                $showMsgProvisioning = 'Y';
              }
            }
          }
        }
        if ($request->config == 'children') {
          $component = InstallationsBundle::getComponent($request->id);
        }

        $totalProcess = 'N';
        if ($component) {
          $totalProcess = Installations::totalProcess($component->installations_id);

          $html = view('fiber.resultBundleComponent', compact('component'))->render();
          $statusRet = $component->status;
        } else {
          $html = '<div class="alert alert-danger text-dark">No se pudo cargar estatus de este producto.</div>';
          $statusRet = '';
        }

        $response = ['success' => true, 'title' => "Estatus del productos consultado", 'msg' => $html, 'status' => $statusRet, 'totalProcess' => $totalProcess, 'showMsgProvisioning' => $showMsgProvisioning, 'icon' => "alert-success", 'code' => 'OK'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [cantToken Evalua cuantos intentos de verificacion posee el telefono con el cliente en el dia]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function cantToken(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo revisar los registros de verificacion para el celular", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];
      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono a verificar es requerido (IVF5200)',
        'phoneVal.regex' => 'El telefono a verificar no cumple el criterio de ser un valor valido (IVF5201)',
        'clientId.required' => 'El ID del cliente es requerido (IVF5202)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'clientId' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }

      //Revisamos si tiene registro de verificacion
      $datClient = Client::getClientByDNI($request->clientId);
      $isVerifyPhone = "";
      if (!empty($datClient)) {
        if (!empty($datClient->verify_phone_id)) {
          $codePhone = Verify_contact_client::getRegisterVerify($datClient->verify_phone_id);
          if (!empty($codePhone)) {
            $isVerifyPhone = $codePhone->status;
          }
        }
      }

      $verifycant = Verify_contact_client::getRegisterVerifyInDay($request->phoneVal, $request->clientId);
      $response = ['success' => true, 'title' => "Cantidad de verificacion del telefono con el mismo cliente", 'msg' => $verifycant, 'icon' => "success", 'code' => $isVerifyPhone];
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [newToken Nuevo token de verificacion de telefono, si existen verificaciones previas y no se usaron se eliminan]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function newToken(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo registrar el token de verificacion para el celular", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];
      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono a verificar es requerido (IVF5257)',
        'phoneVal.regex' => 'El telefono a verificar no cumple el criterio de ser un valor valido (IVF5258)',
        'clientId.required' => 'El ID del cliente es requerido (IVF5259)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'clientId' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //verificamos el dni del cliente y el telefono a verificar antes de crear el token
      $vericatePhone = Verify_contact_client::getCodeVerifyByPhone($request->phoneVal, $request->clientId);
      if ($vericatePhone['code'] != 'REJECTED' &&
        $vericatePhone['code'] != 'IS_PHONE_CLIENT') {
        $obs = Verify_contact_client::getMsgVerify($vericatePhone['code']);

        //Marco en T las verificacion previas
        Verify_contact_client::deleteOldVerified($request->phoneVal, $request->clientId);
        $newToken = Common::getTokenVerify();
        $response = Verify_contact_client::registerNewToken($request->phoneVal, $request->clientId, $newToken, $obs);

        if ($response['success']) {
          //Reviso cuantas veces esta la combinacion dni y telefono en el dia para controlar los intentos de verificacion
          $response['intento'] = Verify_contact_client::getRegisterVerifyInDay($request->phoneVal, $request->clientId);

          //Obtengo el nombre para personalizar el mensaje
          $datClient = Client::getClientByDNI($request->clientId);
          $userText = "";
          if (!empty($datClient)) {
            if (!empty($datClient->name)) {
              $nombre = explode(" ", $datClient->name)[0];
              $userText = "Sr(a) " . $nombre . ", ";
            }
          }

          //Envio el mensaje con el codigo de token
          //NOTA: el sms_type debe ser 'G' y el mensaje debe ir en sms_attribute y en sms(En ambos el mismo contenido)
          try {
            $msgNewToken = $userText . "Su token de verificacion celular es: " . $newToken;
            Sms_notification::Send_sms(
              $request->phoneVal,
              $request->phoneVal,
              'G',
              '1',
              "Tokenizado de verificacion",
              $msgNewToken,
              $msgNewToken);
          } catch (Exception $e) {
            Log::error('No se pudo enviar el msj de tokenizado de telefono del cliente: ' . $request->phoneVal . ' Error: ' . (String) json_encode($e->getMessage()));
          }
        }
      } else {
        $response['msg'] = "EL telefono esta registrado en otro cliente o esta en lista negra, por favor intente con otro numero de telefono";
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

  public function verifyPhone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo verificar el numero celular", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];
      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono a verificar es requerido (IVF5400)',
        'phoneVal.regex' => 'El telefono a verificar no cumple el criterio de ser un valor valido (IVF5401)',
        'clientId.required' => 'El ID del cliente es requerido (IVF5402)',
        'tokenPhone.required' => 'El token a verificar es requerido (IVF5403)',
        'tokenPhone.regex' => 'El token a verificar no cumple el criterio de ser un valor valido (IVF5404)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'clientId' => array(
          'required',
        ),
        'tokenPhone' => array(
          'required',
          'regex:/(^[a-zA-Z]{3}[0-9]{3}$)/u',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }

      //cadena valida ABC123
      $request->tokenPhone = strtoupper($request->tokenPhone);
      $resulVery = Verify_contact_client::verifyToken($request->phoneVal, $request->clientId, $request->tokenPhone);
      $response = $resulVery;

      if ($resulVery['success']) {
        //Se verifico el cel
        $response['icon'] = Verify_contact_client::getIconVerified('VERIFIED') . $response['msg'];
      } else {
        //No se ha logrado verificar
        $response['icon'] = Verify_contact_client::getIconVerified('NOT_VERIFIED') . $response['msg'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [requestAutorized Solicitud de autorizacion por mesa de control para seguir sin verificar]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function requestAutorized(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo registrar la solicitud de autorización a mesa de control", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];
      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono a verificar es requerido (IVF5459)',
        'phoneVal.regex' => 'El telefono a verificar no cumple el criterio de ser un valor valido (IVF5460)',
        'clientId.required' => 'El ID del cliente es requerido (IVF5461)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'clientId' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //Se elimina los registro previos por verificacion
      Verify_contact_client::deleteOldVerified($request->phoneVal, $request->clientId, ['VERIFIED', 'AUTHORIZED']);

      //Reviso el ultimo registro de autorizacion o de rechazo donde salga el telefono sin importar el cliente.
      $Previus = Verify_contact_client::getLastRegisterVerify($request->phoneVal, $request->clientId);

      //Guardamos la solicitud para que mesa de control la procese
      $response = Verify_contact_client::registerNewAutorized($request->phoneVal, $request->clientId, $Previus['msg']);

      if ($response['success']) {
        //Se verifico el cel
        $response['icon'] = "<div class='text-center' style='font-size: 40px;'><i class='fa fa-spin fa-spinner'></i></div><br/> Se debe esperar revisión por parte de mesa de control. Solicitud: #" . $response['msg'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [checkingAutorized Proceso que es llamado varias veces cuando el registro de autorizacion aun no se ha sido aun aprobado]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function checkingAutorized(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo verificar si la solicitud de autorización fue revisada por mesa de control", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];
      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono a verificar es requerido (IVF5512)',
        'phoneVal.regex' => 'El telefono a verificar no cumple el criterio de ser un valor valido (IVF5513)',
        'clientId.required' => 'El ID del cliente es requerido (IVF5514)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
        'clientId' => array(
          'required',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //Evaluamos si fue aprobado o no la solicitud
      $statusVerified = Verify_contact_client::verifiedAutorized($request->phoneVal, $request->clientId);
      return response()->json($statusVerified);
    }
    return redirect()->route('dashboard');
  }

/**
 * [reSendForceURL El vendedor solicito reenvio de mensaje de url de contrato]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function reSendForceURL(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo reenviar el contrato de adhesional al telefono verificado del cliente", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];

      ///New validate
      $messages = [
        'phoneVal.required' => 'El telefono de envio de contrato de adhesión es requerido (IVF5555)',
        'phoneVal.regex' => 'El telefono de envio de contrato adhesión no cumple el criterio de ser un valor valido (IVF5556)',
      ];
      $requisitos = [
        'phoneVal' => array(
          'required',
          'regex:/(^[0-9]{10}$)/u',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //Generamos un nuevo registro para enviar el SMS con el contrato
      $newsms = Sms_notification::resend_sms($request->phoneVal, "Contrato de adhesion");
      if (!empty($newsms)) {
        $response = ['success' => true, 'title' => "Reenviado SMS de contrato de adhesión", 'msg' => $newsms, 'icon' => "success", 'code' => 'OK'];
      } else {
        $response['msg'] = "No se pudo regenerar el SMS con la url de contrato";
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }

/**
 * [checkingActiveFiber Verifica si la instalacion esta realizada o no para que se genere el Qr de post instalacion de un alta con contrato de adhesion]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function checkingActiveFiber(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $response = ['success' => false, 'title' => "No se pudo verificar si la instalacion ya se proceso", 'msg' => "Faltan datos", 'icon' => "error", 'code' => 'ERR_DAT'];

      ///New validate
      $messages = [
        'install_id.required' => 'El identificador de cita de instalacion es requerido (IVF5670)',
        'install_id.regex' => 'El identificador de cita de instalacion no cumple el criterio de ser un valor valido (IVF5671)',
      ];
      $requisitos = [
        'install_id' => array(
          'required',
          'regex:/(^([0-9]+)(\d+)?$)/u',
        ),
      ];

      $validate = Validator::make($request->all(), $requisitos, $messages);
      $errors = $validate->errors();

      if ($errors->any()) {
        $response['msg'] = "";
        foreach ($errors->all() as $error) {
          $response['msg'] .= $error . ', ';
        }
        return response()->json($response);
      }
      //Consultamos la cita

      $dataInstall = Installations::getInstallById($request->install_id);
      if (!empty($dataInstall)) {
        if (!empty($dataInstall->msisdn) && ($dataInstall->status == 'P' || $dataInstall->status == 'PA')) {
          //Si tiene dn es xq se activa la instalacion
          $response = ['success' => true, 'title' => "Instalación procesada", 'msg' => "OK", 'icon' => "success", 'code' => 'ACTIVE'];
          //Ya que esta activo se debe mostrar el Qr final
          //
          $createQr = self::getQrForcerEnd($request->install_id, $dataInstall);
          if (!$createQr['success']) {
            $Qr_svg = $createQr['Qr_svg'];
            //$type_content = $createQr['type_content'];
            $htmlShare = "";
            $response = ['success' => false, 'title' => "Fallo generacion de QR", 'msg' => "El qr de contrato de adhesión de post-instalación no se pudo generar", 'icon' => "warning", 'code' => 'ACTIVE', 'Qr_svg' => $Qr_svg, 'htmlShare' => $htmlShare];

            return response()->json($response);
          } else {
            $urlQr = $createQr['urlQr'];
            $tyc = $createQr['tyc'];
            $Qr_svg = $createQr['Qr_svg'];
          }
          //Se genero QR se debe mostrar el area de compartir del QR
          //
          $shareQrForce = self::getShareForcerEnd($dataInstall, $urlQr, $tyc);
          $htmlShare = $shareQrForce['htmlShare'];
          $response['msg'] = "Se genero el Qr y el area de compartir en post-instalación";
          $response['Qr_svg'] = $Qr_svg;
          $response['htmlShare'] = $htmlShare;

          //Si tiene el telefono verificado se le envia el mensaje con el contrato final
          //
          $sendSMSContract = self::sendSMSPostContract($dataInstall, $urlQr, $request->install_id);

        } else {
          $response = ['success' => false, 'title' => "Instalación aun en espera", 'msg' => "La instalación aun no tiene un msisdn de fibra establecido", 'icon' => "warning", 'code' => 'NO_ACTIVE'];
        }
      } else {
        $response = ['success' => false, 'title' => "Instalación no se encuentra en sistema", 'msg' => "El id suministrado " . $response->install_id . " no se encuentra en sistema", 'icon' => "warning", 'code' => 'EMP_INS'];
      }
      return response()->json($response);
    }
    return redirect()->route('dashboard');
  }
}
