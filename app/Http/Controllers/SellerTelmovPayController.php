<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\Inventory;
use App\Models\PackPrices;
use App\Models\Product;
use App\Models\SellerInventory;
use App\Models\TelmovPay;
use App\Utilities\ApiTelmovPay;
use App\Utilities\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;

class SellerTelmovPayController extends Controller
{
  /**
   * [chekingIdentiTelmov Verifica que al momento de ingresar a venta+activacion se realice financiamiento de telmovPay cuando se halla realizado autenticado y configurado el celular]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function chekingIdentiTelmov(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->ine)) {
        //Reviso que el vendedor no tenga un proceso de verificacion de identidad abierto
        $res = TelmovPay::inProcess(session('user'), ['CF']);

        $verifyIdenty = true;

        if (empty($res)) {
          //El vendedor NO configuro el equipo con el app telmovPay.
          return response()->json(['error' => false, 'success' => false, 'msg' => 'No existe registros de emparejamiento de celular con TelmovPay', 'verifyIdenty' => false]);
        }

        $texto = 'OK';
        $htmlEquip = '';
        $viewPort = false;

        if ($res['dni'] != $request->ine) {

          //Debe terminar el proceso para iniciar con otro cliente
          $texto = 'Tiene un proceso activo de telmovPay distinto al que haz ingresado. Usuario pendiente: ';

          $cliente = Client::getClientByDNI($res['dni']);
          if (!empty($cliente)) {
            $texto .= $cliente['name'] . ' ' . $cliente['last_name'];
          }
          $verifyIdenty = false;

        } else {
          //Obtenemos la marca y el modelo que se va dar de alta basado en el equipo que fue emparejado
          //
          if ($res['isPort'] === 'Y') {
            $viewPort = true;
          }
          $art_det = Inventory::getArticsByDns([$res['msisdn']], session('user'));
          // Log::info('art_det');
          //  Log::info((String) json_encode($art_det));
          if (count($art_det) == 1) {
            $equip = new \stdClass;
            $equip->msisdn = $res['msisdn'];
            $equip->imei = $art_det[0]['imei'];
            $equip->serial = $art_det[0]['serial'];
            $equip->iccid = $art_det[0]['iccid'];

            $art = Inventory::getDnsById($art_det[0]['id']);
            $equip->brand = $art['brand'];
            $equip->model = $art['model'];
            $htmlEquip = view('seller.InfoEquip', compact('equip'))->render();
          }

          if (empty($htmlEquip)) {
            $texto = "El vendedor no cuenta con el equipo " . $res['msisdn'] . " que configuro con telmovPay";
          }
        }

        return response()->json(['error' => false, 'success' => $verifyIdenty, 'msg' => $texto, 'verifyIdenty' => $verifyIdenty, 'html' => $htmlEquip, 'viewPort' => $viewPort]);

      }
      return response()->json(['error' => true]);
    }
    return redirect()->route('dashboard');
  }

  /**
   * [listModelSmartPhone Basado en una marca seleccionada listo los modelos de equipos que tiene asignado el vendedor que esta realizando una venta]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function listModelSmartPhone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->brand)) {
        $equip = new \stdClass;
        $modelosSmartPhone = [];
        $brand = $request->brand;

        $invAssig = SellerInventory::getArticsAssign(session('user'), 'T');
        if (count($invAssig) > 0 || session('org_type') == 'R') {
          if (session('org_type') == 'R') {
            $articlesAssig = Inventory::getArticsByWh(session('wh'), 'T');

            $idInvAssig = $articlesAssig->pluck('id')
              ->toArray();
          } else {
            $idInvAssig = $invAssig->pluck('inv_arti_details_id')
              ->toArray();

            $articlesAssig = Inventory::getArticsByIds($idInvAssig, 'T');
          }

          if (count($articlesAssig) > 0) {
            $idDetailArti = $articlesAssig->pluck('inv_article_id')
              ->toArray();

            $articles = Product::getProductsById($idDetailArti, 'T', env('SMARTCATID'), $brand);

            if (count($articles) > 0) {
              //Posee celulares de la marca asignados
              foreach ($articles as $item) {
                $modelo = (!empty($item->model)) ? $item->model : $item->title;
                array_push($modelosSmartPhone, array(
                  'id' => $item->id,
                  'model' => $modelo,
                ));
              }
              $equip->status = true;
              $equip->message = "Con articulos disponibles.";
            } else {
              $equip->status = false;
              $equip->message = "Sin articulos en inventario.";
            }
          } else {
            $equip->status = false;
            $equip->message = "No se consiguiron los articulos del inventario.";
          }
        } else {
          $equip->status = false;
          $equip->message = "Vendedor sin inventario.";
        }

        $brand = ($brand == 'Samsung') ? "la marca " . $brand : "otras marcas";

        $htmlModel = view('telmovPay.select_model', compact('modelosSmartPhone', 'brand'))->render();

        return response()
          ->json(['error' => false, 'success' => $equip->status, 'msg' => $equip->message, 'htmlModel' => $htmlModel]);
      }
      return response()->json(['error' => true]);
    }
    return redirect()->route('dashboard');
  }

/**
 * [asociateFinanceTelmov Vista que renderiza el input para asociar la financiacion de telmovPay]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  /* public function asociateFinanceTelmov(Request $request)
  {
  if (hasPermit('SEL-TLP')) {
  return view('telmovPay.associate');
  }
  return redirect()->route('dashboard');
  }*/

  /**
   * [verifyInitTelmov Verifico si el msisdn se puede Asociar a una financiacion de telmovPay]
   * @param  Request $request [recibo el MSISDN que se compro con opcion de financiamiento de telmovpay]
   * @return [type]           [description]
   */
  /*public function verifyInitTelmov(Request $request)
  {
  if ($request->isMethod('post') && $request->ajax()) {
  $ChekingAsociate = new \stdClass;
  $ChekingAsociate->success = false;
  $ChekingAsociate->typeAlert = 'alert-danger';
  $ChekingAsociate->msg = "No se puede continuar, faltan datos.";
  $htmlInit = '';
  $ChekingAsociate->error = true;

  if (!empty($request->msisdn)) {
  $ChekingAsociate->error = false;

  $PreReg = TelmovPay::getInfoTelmov($request->msisdn, ['A']);
  if (!empty($PreReg)) {

  $sale = Sale::getSaleByDn($request->msisdn);
  //valido que el vendedor que realizo el alta es el mismo que esta haciendo la asociacion
  if (!empty($sale) && $sale->users_email == session('user')) {
  //valido que solo se asocie DN que se registraron como Alta en telmovpay
  $equip = new \stdClass;
  $saleTelmov = new \stdClass;

  $client = ClientNetwey::getClientByDN($request->msisdn);
  //Se crea una variable nueva para el dni ya que la vista recibe es dni
  $client->dni = $client->clients_dni;

  $equip->msisdn = $request->msisdn;

  $infoPhone = Inventory::existDN($request->msisdn);
  if (!empty($infoPhone)) {
  $equip->imei = $infoPhone->imei;
  $equip->serial = $infoPhone->serial;
  $equip->iccid = $infoPhone->iccid;

  $infoArt = Product::getProductById($infoPhone->inv_article_id);
  if (!empty($infoArt)) {
  $equip->brand = $infoArt->brand;
  $equip->model = $infoArt->model;
  }
  }

  $equip->price = $PreReg->total_amount;
  $equip->dateSale = $PreReg->date_reg;

  $saleTelmov->initial_amount = $PreReg->initial_amount;
  $saleTelmov->cant_cuotes = $PreReg->cant_cuotes;

  $htmlInit = view('telmovPay.infoFinanceTelmov', compact('client', 'equip', 'saleTelmov'))->render();

  $ChekingAsociate->msg = "La asociacion de financiamiento puede continuar.";
  $ChekingAsociate->success = true;
  $ChekingAsociate->typeAlert = 'alert-success';

  } else {
  if (empty($sale)) {
  $ChekingAsociate->msg = "No se tiene registro del alta para el msisdn " . $request->msisdn;

  } elseif ($sale->users_email != session('user')) {
  $ChekingAsociate->msg = "La asociación de financiamiento la debe realizar quien realizo la venta";
  }
  }
  } else {
  $ChekingAsociate->msg = "No hay registros de financiacion para el msisdn " . $request->msisdn;
  }
  }
  return response()
  ->json(['error' => $ChekingAsociate->error, 'success' => $ChekingAsociate->success, 'message' => $ChekingAsociate->msg, 'html' => $htmlInit]);
  }
  return redirect()->route('dashboard');
  }
   */
  /**
   * Inicia el proceso de verificacion de telmov (identidad y contrato)
   */
  public function initTelmov(Request $request)
  {
    if (hasPermit('SEL-TLP')) {
      return view('telmovPay.initTelmovPay');
    }
    return redirect()->route('dashboard');
  }

  /**
   * [updateConctactClient Si el cliente prefiere guardar un nuevo dato de contacto como correo o telefono este se guarda junto con el dato del curp]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function updateConctactClient(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      if (!empty($request->curp) && !empty($request->email) && !empty($request->phone)) {

        $resInsert = array(
          'success' => true,
          'detail_error' => []
        );

        $curp = preg_match("/^[a-zA-Z]{4}[0-9]{2}[0-1][0-9][0-3][0-9][a-zA-Z]{6}[a-zA-Z0-9]{2}$/", $request->curp);
        if (!$curp) {
          $resInsert['success'] = false;
          array_push($resInsert['detail_error'], 'Curp no valido');
        }

        $email = preg_match("/^[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}$/", $request->email);

        if (!$email) {
          $resInsert['success'] = false;
          array_push($resInsert['detail_error'], 'Email no valido');
        }

        if (strlen($request->phone) != 10) {
          $resInsert['success'] = false;
          array_push($resInsert['detail_error'], 'Telefono no valido');
        }

        if ($resInsert['success']) {

          //Obtengo los datos antes del cambio
          //
          $DataOldClient = Client::getClientByDNI($request->ine);

          if (!empty($DataOldClient)) {
            if ($DataOldClient->email != $request->email ||
              $DataOldClient->phone_home != $request->phone ||
              $DataOldClient->code_curp != $request->curp) {
              //  Log::info('SERA ACTUALIZADO EL CLIENTE');
              $updateInfo = Client::updateInfoContact($request->ine, $request->curp, $request->phone, $request->email);

              if ($updateInfo['success']) {

                //Como edite deberia eliminar el preregistro y crear uno nuevo solo si se cambio algun dato
                //

                TelmovPay::updateStatus('T', $request->ine, true);

                TelmovPay::firstInsertTelmov(['seller_mail' => session('user'), 'dni' => $request->ine, 'date_reg' => date('Y-m-d H:i:s')]);

                return response()->json(['success' => true, 'icon' => 'alert-success', 'error' => $updateInfo['error'], 'message' => $updateInfo['msg']]);
              }
            } else {
              return response()->json(['success' => true, 'icon' => 'alert-success', 'error' => false, 'message' => 'No fue necesario actualizar datos']);
            }
          } else {
            return response()->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => 'Verifique los datos suministrado y vuelve a intentar']);
          }
        } else {
          $text = '';
          foreach ($resInsert['detail_error'] as $value) {
            $text .= $value . '. ';
          }
          return response()->json(['error' => false, 'icon' => 'alert-danger', 'success' => false, 'message' => $text]);
        }
      }
      return response()->json(['error' => true, 'icon' => 'alert-danger', 'success' => false, 'message' => "Faltan datos para procesar la solicitud"]);
    }
    return redirect()->route('dashboard');
  }

  /**
   * [showClientNetwey Retorna la informacion de un cliente dado un dni]
   * @param  Request $request [recibo el dni]
   * @return [type]           [description]
   */
  public function step1InitFinance(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dni)) {
        $client = Client::getClientByDNI($request->dni);
        $isActivo = TelmovPay::inProcess(session('user'));
        $isActivoProcess = false;

        if (!empty($isActivo)) {
          if ($isActivo->dni == $request->dni) {
            $isActivoProcess = true;
          }
        }
        $html = view('telmovPay.step1InitFinace', compact('client', 'isActivoProcess'))->render();
        return response()
          ->json(['error' => false, 'html' => $html, 'dni' => $client->dni]);
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [cancelTelmov Opcion de cancelacion de telmovpay]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function cancelTelmov(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->ine)) {
        TelmovPay::updateStatus('T', base64_decode($request->ine), true);
        return response()
          ->json(['success' => true, 'icon' => 'alert-success', 'error' => false, 'message' => 'Eliminado exitosamente']);
      }
      return response()
        ->json(['error' => true, 'icon' => 'alert-danger', 'success' => false, 'message' => "Faltan datos para procesar la solicitud"]);
    }
    return redirect()->route('dashboard');
  }

/**
 * [chekingMail Verificacion del correo ante telmovpay]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function chekingMail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->email)) {

        $Verificado = false;
        if (!empty($request->ine)) {
          $Dni = $request->ine;

          //Reviso si la verificacion de identidad es PASSED si no pido una nueva verificacion de correo
          //
          // $isprocessPrevio = TelmovPay::inProcess(session('user'), ['V'], $Dni);
          $isprocessPrevio = TelmovPay::inProcess(session('user'), ['V', 'P'], $Dni);
          //Log::info('isprocessPrevio');
          //  Log::info($isprocessPrevio);

          if (!empty($isprocessPrevio)) {
            if ($isprocessPrevio->dni == $Dni && $isprocessPrevio->status_verify === "PASSED") {
              $Verificado = true;
            }
          }
        }

        if ($Verificado) {
          //Si se verifico la identidad y es passed no es necesario volver a verificar email ya que aun no ha terminado el proceso de compra
          return response()->json(['success' => true, 'icon' => 'alert-success', 'error' => false, 'message' => 'No es necesario volver a verificar email']);
        }

        $data = array(
          'client_email' => $request->email,
        );

        $Infomail = ApiTelmovPay::sendRequest('api/validate-email', $data);
        //Log::info('Infomail');
        // Log::info((String) json_encode($Infomail));

        $msgTelmov = '';
        if (!$Infomail['success']) {
          $msgTelmov = (!empty($Infomail['msg-telmov'])) ? $Infomail['msg-telmov'] : $Infomail['msg'];
        } else {
          $Infomail = json_decode(json_encode($Infomail['data']));
          if ($Infomail->success && !is_null($Infomail->data) && $Infomail
            ->data
            ->available) {

            //Reviso si ya existe registro previo
            //
            $isprocess = TelmovPay::inProcess(session('user'), ['P', 'V'], $request->ine);
            if (empty($isprocess)) {
              //Se realiza pre-registro
              //
              TelmovPay::updateStatus('T', $request->ine, true);

              $up = TelmovPay::firstInsertTelmov(['seller_mail' => session('user'), 'dni' => $request->ine, 'date_reg' => date('Y-m-d H:i:s')]);
            }
            return response()
              ->json(['success' => true, 'icon' => 'alert-success', 'error' => false, 'message' => $Infomail->message]);
          } else {
            if (!$Infomail->success) {
              $msgTelmov = isset($Infomail
                  ->data
                  ->error) ? $Infomail
                  ->data->error : 'No se obtuvo respuesta satisfactoria del Api intermedia';
              } elseif (!$Infomail
                ->data
                ->available) {
                $msgTelmov = "Correo no disponible ante TelmovPay";
              } elseif (is_null($Infomail->data)) {
              $msgTelmov = "Problemas para verificar correo";
            }
          }
        }

        return response()->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => $msgTelmov]);
      }
      return response()->json(['error' => true, 'icon' => 'alert-danger', 'success' => false, 'message' => "Faltan datos para procesar la solicitud"]);
    }
    return redirect()->route('dashboard');
  }

/**
 * [credit_retrieve Revisa el nivel crediticio de un cliente]
 * @param  Request $request         [description]
 * @param  boolean $isprocessPrevio [description]
 * @param  boolean $device_id       [description]
 * @param  boolean $payment_total   [description]
 * @param  boolean $brand           [description]
 * @return [type]                   [description]
 */
  public function credit_retrieve(Request $request, $isprocessPrevio = false, $device_id = false, $payment_total = false, $brand = false)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = array(
        'seller_email' => session('user'),
        'id_device' => $device_id,
        'brand' => strtoupper($brand),
        'loan_amount' => $payment_total,
      );
      $infoCredit = ApiTelmovPay::sendRequest('api/credit-retrieve', $data);

      // Log::info('infoCredit');
      // Log::info((String) json_encode($infoCredit));

      $InfoCreditClient = array();
      $InfoCreditClient['success'] = false;
      $InfoCreditClient['dataFinance'] = null;
      $InfoCreditClient['msg'] = null;
      $InfoCreditClient['typeAlert'] = 'alert-danger';
      if ($infoCredit['success']) {
        if ($infoCredit['data']->success && !is_null($infoCredit['data']->data)) {
          $clientCreditTelmov = json_decode(json_encode($infoCredit['data']->data));

          if (isset($clientCreditTelmov->rating) && !empty($clientCreditTelmov->rating)) {

            $InfoCreditClient['success'] = true;
            $InfoCreditClient['typeAlert'] = 'alert-success';
            $InfoCreditClient['msg'] = "Credito del ultimo cliente verificado";
            $InfoCreditClient['dataFinance'] = $clientCreditTelmov;
          } else {
            $InfoCreditClient['msg'] = "Hay un problema, no se conoce el nivel crediticio del usuario";
          }
        } else {
          if (isset($infoCredit['data']->message) && !empty($infoCredit['data']->message)) {
            $InfoCreditClient['msg'] = $infoCredit['data']->message;
          } else {
            $InfoCreditClient['msg'] = "No se puedo obtener informacion de credito para el ultimo cliente verificado";
          }
        }
      } else {
        $msgTelmov = (!empty($InfoRClient['msg-telmov'])) ? $InfoRClient['msg-telmov'] : $InfoRClient['msg'];
        $InfoCreditClient['msg'] = $msgTelmov;
      }

      if ($isprocessPrevio) {
        return $InfoCreditClient;
      }
      return response()->json($InfoCreditClient);
    }
    return redirect()->route('dashboard');
  }

/**
 * [client_retrieve Retorna la informacion del cliente que se verifico ante telmovpay]
 * @param  Request $request         [description]
 * @param  boolean $isprocessPrevio [description]
 * @param  boolean $Dni             [description]
 * @return [type]                   [description]
 */
  public function client_retrieve(Request $request, $isprocessPrevio = false, $Dni = false)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $idTelmov = '';
      if ($isprocessPrevio) {
        $idTelmov = $isprocessPrevio['id'];
      } else {
        $isprocessPrevio = TelmovPay::inProcess(session('user'), ['V'], $Dni);
        if (!empty($isprocessPrevio)) {
          $idTelmov = $isprocessPrevio['id'];
        }
      }
      $InfoClient = array();
      $InfoClient['success'] = false;
      $InfoClient['dataClient'] = null;
      $InfoClient['msg'] = '';
      $InfoClient['typeAlert'] = 'alert-danger';

      if (!empty($idTelmov)) {

        $data = array(
          'transaction_id' => $idTelmov,
        );
        $InfoRClient = ApiTelmovPay::sendRequest('api/client-retrieve', $data);

        //Log::info('InfoRClient');
        // Log::info((String) json_encode($InfoRClient));

        if ($InfoRClient['success']) {
          // Log::info('InfoRClient sucess');
          // Log::info($InfoRClient['data']->success);

          if ($InfoRClient['data']->success) {
            $clientTelmov = json_decode(json_encode($InfoRClient['data']->data));

            $InfoClient['success'] = true;
            $InfoClient['dataClient'] = $clientTelmov;
            $InfoClient['typeAlert'] = 'alert-success';
            $InfoClient['msg'] = "Ultimo cliente verificado";

          } else {
            $msg = (isset($InfoRClient['data']->message) && !empty($InfoRClient['data']->message)) ? '. ' . $InfoRClient['data']->message : '';

            $InfoClient['msg'] = "No se obtuvo información del cliente en telmovPay " . $msg;
          }
        } else {
          $msgTelmov = (!empty($InfoRClient['msg-telmov'])) ? $InfoRClient['msg-telmov'] : $InfoRClient['msg'];
          $InfoClient['msg'] = $msgTelmov;
        }
      } else {
        $InfoClient['msg'] = 'No se pudo obtener el ID de registro de telmovPay';
      }

      if ($isprocessPrevio) {
        // Log::info('Sin Json');
        return $InfoClient;
      } else {
        // Log::info('Con Json');
        return response()->json($InfoClient);
      }
    }
    return redirect()->route('dashboard');
  }

/**
 * [info_client_credit Retorna la informacion crediticia y los datos personales del cliente]
 * @param  Request $request         [description]
 * @param  boolean $isprocessPrevio [description]
 * @param  boolean $Dni             [description]
 * @return [type]                   [description]
 */
  public function info_client_credit(Request $request, $isprocessPrevio = false, $Dni = false)
  {

    //Si ya el cliente se verifico no necesito volver hacerlo, obtengo la data del cliente que se verifico
    $clientRetrieve = self::client_retrieve($request, $isprocessPrevio, $Dni);

    $htmlClient = '';
    if ($clientRetrieve['success']) {

      $clientTelmov = $clientRetrieve['dataClient'];
      $htmlClient = view('telmovPay.infoClientVerify', compact('clientTelmov'))->render();

    } else {
      $htmlClient = "<label class='col-md-12'>" . $clientRetrieve['msg'] . "</label>";
    }

    return response()->json(['success' => $clientRetrieve['success'], 'icon' => $clientRetrieve['typeAlert'], 'error' => false, 'html' => $htmlClient, 'infoClient' => true, 'message' => $clientRetrieve['msg']]);
  }

/**
 * [requestQr Solicitud de un Qr de verificacion de identidad]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function requestQr(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $Dni = '';
      $Verificado = false;
      $isprocessPrevio = null;

      if (!empty($request->ine)) {
        $Dni = $request->ine;

        //Reviso el proceso con telmov que posee el vendedor
        // $isprocessPrevio = TelmovPay::inProcess(session('user'), ['V'], $Dni);

        $isprocessPrevio = TelmovPay::inProcess(session('user'));
        // Log::info('P1');
        if (!empty($isprocessPrevio)) {
          //  Log::info('P2');

          if ($isprocessPrevio->dni == $Dni && in_array($isprocessPrevio->status, ['P', 'V'])) {
            //Reviso si la verificacion de identidad es PASSED si no pido una nueva verificacion de identidad
            // Log::info('P3');

            if ($isprocessPrevio->status_verify === 'PASSED') {
              $Verificado = true;
              //  Log::info('P4');
            }
          } else {
            // Log::info('P5');
            if ($isprocessPrevio->dni != $Dni) {
              // Log::info('P6');
              //Elimino el proceso previo de otro cliente que no se completo
              //
              TelmovPay::updateStatus('T', $isprocessPrevio->dni, true);

              $client = Client::getClientByDNI($isprocessPrevio->dni);
              $text = "Se cancelo el proceso previo que tenias con el cliente: " . $client->name . "  " . $client->last_name . " ya que no concluyo. Vuelve a intentar para iniciar uno nuevo";
              return response()
                ->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => $text, 'resetView' => false]);
            } else {
              //Log::info('P7');
              //Elimino los procesos previos del usuario actual y que no completo el flujo
              TelmovPay::updateStatus('T', $Dni, true);
              return response()
                ->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => 'Debes iniciar un nuevo proceso, no completaste el proceso previo', 'resetView' => true]);
            }
          }
        } else {
          return response()
            ->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => "Debes iniciar un nuevo proceso desde la busqueda del prospecto", 'resetView' => true]);
        }
      }

      if ($Verificado) {
        return self::info_client_credit($request, $isprocessPrevio, $Dni);
      } else {
        /**
         * Reviso cuantas verificaciones de identidad ha realizado
         *
         */
        $cant = 0;
        if (session('countTelmov') == null) {
          $intentos = DB::table('islim_configs')->select('param')
            ->where([['attrib', 'count_inDay_telmovpay'], ['status', 'A']])
            ->first();

          $cant = !empty($intentos) ? intval($intentos->param) : 5;

          session(['cantIntentos' => $cant, 'countTelmov' => $cant, 'ineTelmov' => $request->ine, 'dateTelmov' => date('Y-m-d')]);
        } else {

          if (intval(session('countTelmov')) <= 0 && session('ineTelmov') == $request->ine) {

            $texto = "Agoto los " . session('cantIntentos') . " intentos de verificacion en el dia para el ine " . session('ineTelmov');
            return response()->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => $texto, 'resetView' => true]);

          } elseif (intval(session('countTelmov')) > 0 && session('ineTelmov') == $request->ine && session('dateTelmov') == date('Y-m-d')) {

            $intentos = intval(session('countTelmov'));
            session(['countTelmov' => $intentos--]);
          }
        }
      }

      $StartVerify = new \stdClass;
      $StartVerify->success = false;
      $StartVerify->error = false;
      $StartVerify->typeAlert = 'alert-danger';
      $StartVerify->msg = "No se puede continuar, Hubo un problema de ejecucion.";
      $StartVerify->urlQR = null;
      $StartVerify->resetView = true;

      $data = array('seller_email' => session('user'));
      $InfoInitQR = ApiTelmovPay::sendRequest('api/identity-validations-start', $data);

      // Log::info('InfoInitQR');
      //  Log::info((String) json_encode($InfoInitQR));

      if ($InfoInitQR['success']) {
        $InfoInitQR = json_decode(json_encode($InfoInitQR['data']));
        if ($InfoInitQR->success && !is_null($InfoInitQR->data)) {
          //Reviso si ya existe registro previo
          if (!empty($Dni)) {
            $isprocess = TelmovPay::inProcess(session('user'), false, $Dni);
            // Log::info('isprocess');
            // Log::info((String) json_encode($isprocess));

            if (!empty($isprocess)) {

              $StartVerify->urlQR = $InfoInitQR
                ->data->agreementSigningUrl;

              $Regtelmovpay = TelmovPay::getConnect('W')->where([['seller_mail', session('user')], ['dni', $Dni]])->whereIn('status', ['P', 'V'])
                ->update([
                  'verification_id' => $InfoInitQR->data->_id,
                  'agreement_id' => $InfoInitQR->data->agreementId,
                  'salesclerk_id' => $InfoInitQR->data->salesclerkId,
                  'url_verify' => $StartVerify->urlQR,
                  'status' => 'V',
                  'status_verify' => $InfoInitQR->data->status]);

              $StartVerify->success = true;
              $StartVerify->resetView = false;
              $StartVerify->typeAlert = 'alert-success';
              $StartVerify->msg = 'Inicio de verificacion OK';

              // $html = view('telmovPay.viewQr', compact('$StartVerify->urlQR', 'Dni'))->render();
              //Muestro el QR

            } else {
              $StartVerify->msg = "No hay registro que cumplan los requisitos para iniciar verificacion";
            }
          } else {
            $StartVerify->msg = "No se pudo generar el QR de verificacion de identidad, faltan datos intente nuevamente";
          }
        } else {
          $StartVerify->msg = !empty($InfoInitQR->message) ? $InfoInitQR->message : 'No se obtuvo respuesta satisfactoria para iniciar un proceso de verificacion de identidad';
        }
      } else {
        $StartVerify->msg = (!empty($InfoInitQR['msg-telmov'])) ? $InfoInitQR['msg-telmov'] : $InfoInitQR['msg'];
      }
      return response()->json(['success' => $StartVerify->success, 'icon' => $StartVerify->typeAlert, 'error' => false, 'message' => $StartVerify->msg, 'resetView' => $StartVerify->resetView, 'urlQR' => $StartVerify->urlQR, 'infoClient' => false]);
    }
    return redirect()->route('dashboard');
  }

  /**
   * [requestQrVerifyLast Luego de iniciada la verificacion de identidad consulto si la validacion de identidad se realizo]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function requestQrVerifyLast(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $EndLastVerify = new \stdClass;
      $EndLastVerify->success = false;
      $EndLastVerify->error = false;
      $EndLastVerify->typeAlert = 'alert-danger';
      $EndLastVerify->msg = "No se puede continuar, Faltan datos.";

      if (!empty($request->ine)) {
        $Dni = $request->ine;
        $isprocess = TelmovPay::inProcess(session('user'), ['V'], $Dni);

        if (!empty($isprocess)) {
          $client = Client::getClientByDNI($Dni);

          if (!empty($client)) {

            $data2 = array(
              'seller_email' => session('user'),
              'dni' => $client->code_curp,
              'client_email' => $client->email,
              'client_phone' => $client->phone_home,
            );

            //  Log::info((String) json_encode($data2));
            $EndVerifyQR = ApiTelmovPay::sendRequest('api/identity-validations-end', $data2);
            //Indico que se completo la verificacion de identidad
            //  Log::info('EndVerifyQR');
            //  Log::info((String) json_encode($EndVerifyQR));

            if ($EndVerifyQR['success']) {
              $EndVerifyQR = json_decode(json_encode($EndVerifyQR['data']));

              if ($EndVerifyQR->success) {
                $data = array('seller_email' => session('user'));

                $InfoLastQR = ApiTelmovPay::sendRequest('api/identity-validations-get-last', $data);
                //  Log::info('InfoLastQR');
                //  Log::info((String) json_encode($InfoLastQR));

                if ($InfoLastQR['success']) {
                  $InfoLastQR = json_decode(json_encode($InfoLastQR['data']));

                  if ($InfoLastQR->success && !is_null($InfoLastQR->data)) {

                    $Regtelmovpay = TelmovPay::getConnect('W')->where([['seller_mail', session('user')], ['dni', $Dni]])->whereIn('status', ['V'])
                      ->update(['status_verify' => $InfoLastQR
                          ->data->status, 'customer_id' => $InfoLastQR
                          ->data->customerId, 'store_id' => $InfoLastQR
                          ->data->storeId]);

                      //MIRO SI PASOO O NO
                      if ($InfoLastQR->data->status == 'PASSED') {

                      //Muestro informacion capturada de la verificacion de identidad
                      //
                      return self::info_client_credit($request, false, $Dni);
                      $EndLastVerify->msg = "Verificacion exitosa";
                    } else {
                      return response()->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => "Verificacion de identidad no fue exitosa"]);
                    }
                  } else {
                    $EndLastVerify->msg = !empty($InfoLastQR->message) ? $InfoLastQR->message : 'No se obtuvo respuesta satisfactoria al verificar ultima la verificacion de identidad';
                  }
                } else {
                  $msgTelmov = (!empty($InfoLastQR['msg-telmov'])) ? $InfoLastQR['msg-telmov'] : $InfoLastQR['msg'];
                  $EndLastVerify->msg = "Hay un problema en conocer el status de la ultima verificacion. " . $msgTelmov;
                }
              } else {
                $EndLastVerify->msg = !empty($EndVerifyQR->message) ? $EndVerifyQR->message : 'No se obtuvo respuesta satisfactoria al finalizar el proceso de verificacion de identidad';
              }
            } else {
              $msgTelmov = (!empty($EndVerifyQR['msg-telmov'])) ? $EndVerifyQR['msg-telmov'] : $EndVerifyQR['msg'];
              $EndLastVerify->msg = "Hay un problema al finalizar verificacion. " . $msgTelmov;
            }
          } else {
            $EndLastVerify->msg = "No se puede continuar, no hay registros del cliente";
          }
        } else {
          $EndLastVerify->msg = "No se puede continuar, no hay registros que cumplan el criterio de verificacion";
        }
      }

      return response()
        ->json(['success' => $EndLastVerify->success, 'icon' => $EndLastVerify->success, 'error' => false, 'message' => $EndLastVerify->msg]);

    }
    return redirect()->route('dashboard');
  }

  /**
   * [associateCashTelmov Realiza el proceso de asociacion de financiacion a la venta realizada con la opcion de telmovPay]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  /*
  public function associateCashTelmov(Request $request)
  {
  if ($request->isMethod('post') && $request->ajax()) {
  if (!empty($request->msisdn) && !empty($request->money)) {
  if ($request->money == 'Y') {

  $sale = Sale::getSaleByDn($request->msisdn);
  if (!empty($sale) && $sale->users_email == session('user')) {
  //Reviso que el msisdn se dio de alta
  $RegTelmov = TelmovPay::getInfoTelmov($request->msisdn, ['A']);
  if (!empty($RegTelmov)) {
  if ($RegTelmov->total_amount == $sale->amount) {
  //Asignando financiamiento al cliente
  ClientNetwey::getConnect('W')
  ->where('msisdn', $request->msisdn)
  ->update(['telmovpay_id' => $RegTelmov->id]);

  //Actualizando monto que recibio el vendedor en efectivo (el enganche)
  //
  $enganche = $RegTelmov->initial_amount;
  Sale::getConnect('W')
  ->where('id', $sale->id)
  ->update(['amount' => $enganche, 'amount_net' => ($enganche / env('TAX'))]);
  //Actualizo el monto recibido en el detalle de la venta
  //
  $detailA = AssignedSalesDetail::getLastDetail($sale->unique_transaction);
  if (!empty($detailA)) {
  AssignedSalesDetail::getConnect('W')->where('id', $detailA->id)
  ->update(['amount' => $enganche, 'amount_text' => $enganche]);

  $saleA = AssignedSales::getSale($detailA->asigned_sale_id);
  if (!empty($saleA)) {
  $newAmount = ($saleA->amount - $enganche);
  AssignedSales::getConnect('W')->where('id', $saleA->id)
  ->update(['amount' => $newAmount, 'amount_text' => $newAmount]);
  }
  }

  $Notify = new \stdClass;
  $Notify->success = false;
  $Notify->error = false;
  $Notify->typeAlert = 'alert-danger';
  $Notify->msg = "Financiación del msisdn " . $request->msisdn . " realizada con exito en: Netwey ";

  //Notifico a telmovPay que el vendedor tiene el dinero

  $data = array(
  'seller_email' => session('user'));
  $NotifyPayment = ApiTelmovPay::sendRequest('api/payment', $data);
  Log::info('NotifyPayment');
  Log::info($NotifyPayment);

  if ($NotifyPayment['success']) {
  $NotifyPayment = json_decode(json_encode($NotifyPayment['data']));

  if ($NotifyPayment->success) {

  //Marcando financiamiento como procesado
  $starContract = TelmovPay::getConnect('W')
  ->where([
  ['seller_mail', session('user')],
  ['dni', $RegTelmov->dni],
  ['msisdn', $request->msisdn]])
  ->whereIn('status', ['A'])
  ->update([
  'status' => 'AF',
  ]);
  $Notify->msg .= "y TelmovPay!";
  $Notify->typeAlert = 'alert-success';

  } else {
  $Notify->error = true;
  $Notify->msg .= !empty($NotifyPayment->message) ? $NotifyPayment->message : ' pero no se obtuvo respuesta satisfactoria para notificar el pago ante TelmovPay';
  }
  } else {
  $Notify->error = true;
  $Notify->msg .= (!empty($NotifyPayment['msg-telmov'])) ? $NotifyPayment['msg-telmov'] : $NotifyPayment['msg'];
  }

  //Fin de la notificacion de recepcion de dinero en telmovPay

  return response()->json(['success' => true, 'icon' => $Notify->typeAlert, 'error' => $Notify->error, 'message' => $Notify->msg]);
  }
  }
  return response()
  ->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => "No hay registros en telmovPay de Alta del msisdn " . $request->msisdn . " comprado con financiacion"]);
  }
  return response()
  ->json(['success' => false, 'icon' => 'alert-danger', 'error' => false, 'message' => "La asociacion de financiamiento la debe realizar quien realizo la venta."]);
  }
  //Marcando financiamiento como procesado
  TelmovPay::updateStatus('AF', false, false, false, $request->msisdn);
  return response()
  ->json(['success' => true, 'icon' => 'alert-success', 'error' => false, 'message' => "Financiacion del msisdn " . $request->msisdn . " cerrada! Recuerda que debes cancelar la deuda total del equipo"]);
  }
  return response()
  ->json(['success' => false, 'icon' => 'alert-danger', 'error' => true, 'message' => "No se puede continuar, faltan datos. "]);
  }
  return redirect()
  ->route('dashboard');
  }
   */
  public function buildPlan(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->brand) && !empty($request->model) && !empty($request->port)) {

        $typeSell = 'T';
        $typeArtic = 'mov-ph';
        $isBandTE = false;
        $typePayment = 'telmovpay';
        $brand = $request->brand;
        $isport = ($request->port === 'CP') ? true : false;

        $packs = Seller::SearchPack($typeSell, $typeArtic, $isBandTE, $typePayment, $brand, $isport);

        $html = view('telmovPay.select_plan', compact('packs'))->render();

        return response()
          ->json(['success' => true, 'html' => $html]);
      }
      return response()->json(['success' => false, 'icon' => 'alert-danger', 'error' => true, 'message' => "No se puede continuar, faltan datos. "]);
    }
    return redirect()
      ->route('dashboard');
  }

  public function getModels(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      //  Log::info('input');
      //  Log::info($input);

      $Cheking = new \stdClass;
      $Cheking->success = false;
      $Cheking->typeAlert = 'alert-danger';
      $htmlFinance = "";
      $Cheking->msg = "No se puede continuar, faltan datos.";

      if (!empty($input['msisdn']) && !empty($input['pack']) && !empty($input['brand']) && !empty($input['model']) && !empty($input['port']) && !empty($input['ine'])) {

        $art_inv_id = base64_decode($input['model']);
        //  Log::info('model_id_art');
        //  Log::info($art_inv_id);
        $infoArt = Product::getProductById($art_inv_id);

        if (!empty($infoArt)) {
          if (!empty($infoArt->brand) && !empty($infoArt->model)) {
            $data = array(
              'brand' => strtoupper($infoArt->brand),
              'model' => strtoupper($infoArt->model),
            );
            $InfoModels = ApiTelmovPay::sendRequest('api/models', $data);
            //  Log::info('InfoModels');
            //  Log::info($InfoModels);
            if ($InfoModels['success']) {
              $InfoModels = json_decode(json_encode($InfoModels['data']));
              if ($InfoModels->success && !is_null($InfoModels->data)) {
                //Debo solo tener un solo registro
                if (count($InfoModels->data) == 1) {

                  //Con el tipo telefonia y pack saco el precio
                  //
                  $costo = PackPrices::getServiceByPackAndType(base64_decode($input['pack']), 'T');
                  if (!empty($costo)) {
                    $payment = doubleval($costo->price_pack) + doubleval($costo->price_serv);
                    $art_telmov_id = $InfoModels->data[0]->_id;

                    //Consulto el equipo si me brindan financiacion
                    $creditRetrieve = self::credit_retrieve($request, true, $art_telmov_id, $payment, $infoArt->brand);

                    //Consulta de si permite el cliente recibir financiamiento
                    $InfoFinance = array();
                    $InfoFinance['success'] = false;

                    if ($creditRetrieve['success']) {

                      //Verifico que la calificacion corresponde a la verificacion de identidad registrada
                      //
                      $RegistroVerify = TelmovPay::getConnect('W')
                        ->where([
                          ['seller_mail', session('user')],
                          ['dni', $input['ine']]])
                        ->whereIn('status', ['V', 'S'])
                        ->first();

                      if (!empty($RegistroVerify)) {
                        if ($RegistroVerify->verification_id == $creditRetrieve['dataFinance']->identityValidationId || true) {
                          if (strtoupper($creditRetrieve['dataFinance']->rating) != 'E') {

                            $Cheking->success = true;
                            $Cheking->typeAlert = 'alert-success';
                            $Cheking->rating = $creditRetrieve['dataFinance']->rating;
                            $Cheking->minimumPayment = doubleval($creditRetrieve['dataFinance']->loanMinimumDownPaymentAmount) + 1;
                            $Cheking->payment = $payment;
                            $Cheking->WeekAmounts = json_encode($creditRetrieve['dataFinance']->loanInstallmentAmounts);

                            $Regtelmovpay = TelmovPay::getConnect('W')
                              ->where([
                                ['seller_mail', session('user')],
                                ['dni', $input['ine']]])
                              ->whereIn('status', ['V'])
                              ->update([
                                'rating' => $Cheking->rating,
                                'msisdn' => $input['msisdn'],
                                'pack_id' => base64_decode($input['pack']),
                                'isPort' => ($input['port'] === 'CP') ? 'Y' : 'N',
                                'initial_amount' => $Cheking->minimumPayment,
                                'minimum_amount' => doubleval($Cheking->minimumPayment) - 1,
                                'weekly' => $Cheking->WeekAmounts,
                                'total_amount' => $payment,
                                'smartPhone_id' => $art_telmov_id,
                                'status' => 'S',
                              ]);

                            $Cheking->msg = "Felicidades, puedes disfrutar de un financiamiento de TelmovPay! Cat (" . $Cheking->rating . ") con enganche minimo de $ " . $Cheking->minimumPayment;

                            $infoCredit = $Cheking;

                            $htmlFinance = view('telmovPay.configFinanceClient', compact('infoCredit'))->render();

                          } else {
                            $Cheking->msg = "El usuario no tiene permitido recibir credito de TelmovPay";
                          }
                        } else {
                          $Cheking->msg = "El codigo de verificación de identidad del cliente no coincide con el codigo aprobado del nivel crediticio &#128542;. Recuerda que solo puedes tener un proceso de creación de contrato de telmovPay a la vez";
                        }
                      } else {
                        $Cheking->msg = "No hay resultados de solicitudes de financiación en proceso aun por verificar. Es probable que debas iniciar un nuevo proceso";
                      }
                    } else {
                      $Cheking->msg = $creditRetrieve['msg'];
                    }
                  } else {
                    $Cheking->msg = "No se pudo obtener el costo del equipo solicitado";
                  }
                } else {
                  $Cheking->msg = "Los resultados de modelos de equipos en telmovPay no son consistentes";
                }
              } else {
                $Cheking->msg = "No hay resultados del modelo seleccionado para ser vendido por TelmovPay";
              }
            } else {
              $Cheking->msg = (!empty($EndVerifyQR['msg-telmov'])) ? $EndVerifyQR['msg-telmov'] : $EndVerifyQR['msg'];
            }
          } else {
            $Cheking->msg = "En BD el equipo: " . $infoArt->title . " falta por completar la marca o el modelo";
          }
        } else {
          $Cheking->msg = "No se encuentran registros del equipo en el sistema";
        }
      }
      return response()
        ->json(['success' => $Cheking->success, 'icon' => $Cheking->typeAlert, 'error' => false, 'message' => $Cheking->msg, 'html' => $htmlFinance]);
    }
    return redirect()->route('dashboard');
  }

  public function initContract(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      $Contract = new \stdClass;
      $Contract->success = false;
      $Contract->typeAlert = 'alert-danger';
      $QrContract = "";
      $Contract->msg = "No se puede continuar, faltan datos.";

      if (!empty($input['dni']) && !empty($input['enganche']) && !empty($input['periodicity'])) {
        //  Log::info('input');
        //  Log::info((String) json_encode($input));

        $isprocess = TelmovPay::inProcess(session('user'), ['S'], $input['dni']);
        if (!empty($isprocess)) {
          //Busco la marca para que el api verifique el monto minimo si es permitido

          if ($isprocess->msisdn) {
            $DataDn = Inventory::getDataDn($isprocess->msisdn);
            if (!empty($DataDn)) {
              $idInv = $DataDn->inv_article_id;
              $infoArt = Product::getProductById($idInv);
              if (!empty($infoArt)) {

                $data = array(
                  'seller_email' => session('user'),
                  'amount' => $isprocess->total_amount,
                  'brand' => strtoupper($infoArt->brand),
                  'deviceModelId' => $isprocess->smartPhone_id,
                  //'downPaymentAmount' => doubleval($input['enganche']),
                  'downPaymentAmount' => $isprocess->initial_amount,
                  'installmentCount' => intval(base64_decode($input['periodicity'])),
                );
                $IntContract = ApiTelmovPay::sendRequest('api/loan-start', $data);
                //  Log::info('data IntContract');
                //  Log::info($data);

                //  Log::info('IntContract');
                //  Log::info($IntContract);
                if ($IntContract['success']) {
                  $IntContract = json_decode(json_encode($IntContract['data']));
                  if ($IntContract->success) {

                    $starContract = TelmovPay::getConnect('W')
                      ->where([
                        ['seller_mail', session('user')],
                        ['dni', $input['dni']]])
                      ->whereIn('status', ['S'])
                      ->update([
                        'cant_cuotes' => base64_decode($input['periodicity']),
                        //'initial_amount' => $input['enganche'],
                        'status' => 'F',
                      ]);
                    sleep(2);

                    $data = array('seller_email' => session('user'));

                    $StatusContract = ApiTelmovPay::sendRequest('api/loan-status', $data);

                    //  Log::info('StatusContract');
                    //  Log::info($StatusContract);
                    if ($StatusContract['success']) {
                      $StatusContract = json_decode(json_encode($StatusContract['data']));
                      // Log::info((String) json_encode($StatusContract));

                      if ($StatusContract->success) {
                        $dataContract = json_decode(json_encode($StatusContract->data));
                        // Log::info((String) json_encode($dataContract));

                        if ($dataContract->identityValidationId == $isprocess->verification_id) {

                          $QrContract = $dataContract->svg;

                          $Q = array("\n", "\\", "<?xml", 'version="1.0"', 'encoding="UTF-8"?>');
                          $QrContract = str_replace($Q, "", $QrContract);

                          $starContract = TelmovPay::getConnect('W')
                            ->where([
                              ['seller_mail', session('user')],
                              ['dni', $input['dni']]])
                            ->whereIn('status', ['F'])
                            ->update([
                              'url_contract' => $dataContract->agreementSigningUrl,
                            ]);

                          // Log::info('SVG');
                          //  Log::info((String) json_encode($QrContract));
                          $Contract->success = true;
                          $Contract->msg = "Procede a firmar el contrato de telmovPay";
                        } else {
                          $Contract->msg = "El id de verificacion o el id del convenio no corresponde con el del generación de contrato";
                        }
                      } else {
                        $Contract->msg = !empty($StatusContract->message) ? $StatusContract->message : 'No se obtuvo respuesta satisfactoria para ver el status del contrato';
                      }
                    } else {
                      $Contract->msg = (!empty($StatusContract['msg-telmov'])) ? $StatusContract['msg-telmov'] : $StatusContract['msg'];
                    }
                  } else {
                    $Contract->msg = !empty($IntContract->message) ? $IntContract->message : 'No se obtuvo respuesta satisfactoria para crear el contrato';
                  }
                } else {
                  $Contract->msg = (!empty($IntContract['msg-telmov'])) ? $IntContract['msg-telmov'] : $IntContract['msg'];
                }
              }
            } else {
              $Contract->msg = "No existe el Dn " . $isprocess->msisdn . " en inventario netwey";
            }
          } else {
            $Contract->msg = "No se ha registrado el Dn " . $isprocess->msisdn . " en proceso de telmovPay";
          }

        } else {
          $Contract->msg = "No se puede continuar, no se encontraron datos de seleccion de equipo para crear el contrato";
        }
      }
      return response()
        ->json(['success' => $Contract->success, 'icon' => $Contract->typeAlert, 'error' => false, 'message' => $Contract->msg, 'QrContract' => $QrContract]);

    }
    return redirect()->route('dashboard');
  }

  public function endContract(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      $EndContract = new \stdClass;
      $EndContract->success = false;
      $EndContract->error = false;
      $EndContract->typeAlert = 'alert-danger';
      $EndContract->msg = "No se puede continuar, faltan datos.";
      $QrInitEnrole = '';

      if (!empty($input['dni'])) {

        $isprocess = TelmovPay::inProcess(session('user'), ['F', 'PE'], $input['dni']);
        if (!empty($isprocess)) {

          $data = array('seller_email' => session('user'));

          $FinContract = ApiTelmovPay::sendRequest('api/loan-end', $data);

          //  Log::info('data FinContract');
          //  Log::info((String) json_encode($data));
          //  Log::info('FinContract');
          //  Log::info($FinContract);

          if ($FinContract['success']) {
            $FinContract = json_decode(json_encode($FinContract['data']));
            if ($FinContract->success) {
              //Se realizo la firma del contrato, notifico que recibi el dinero
              //
              $NotifyPayment = ApiTelmovPay::sendRequest('api/payment', $data);
              //  Log::info('data NotifyPayment');
              //  Log::info((String) json_encode($data));
              //  Log::info('NotifyPayment');
              //  Log::info($NotifyPayment);

              if ($NotifyPayment['success'] && $isprocess->status == 'F') {
                //Si dado caso da un error luego que se pago pueda volver a intentar iniciar el enrolamiento
                $NotifyPayment = json_decode(json_encode($NotifyPayment['data']));

                if ($NotifyPayment->success) {
                  //Marcando que fue recibido el pago
                  $Pagado = TelmovPay::getConnect('W')
                    ->where([
                      ['seller_mail', session('user')],
                      ['dni', $input['dni']]])
                    ->whereIn('status', ['F'])
                    ->update([
                      'status' => 'PE',
                    ]);
                  sleep(2);
                } else {
                  $EndContract->error = true;
                  $EndContract->msg = 'No se obtuvo respuesta satisfactoria al notificar el pago ante TelmovPay' . empty($NotifyPayment->message) ? ". " . $NotifyPayment->message : "";
                }
              } else {
                if ($isprocess->status == 'F') {
                  $EndContract->error = true;
                  $EndContract->msg = (!empty($NotifyPayment['msg-telmov'])) ? $NotifyPayment['msg-telmov'] : $NotifyPayment['msg'];
                }
              }

              //Fin de la notificacion de recepcion de dinero en telmovPay
              //
              if (!$EndContract->error) {
                //Si no hubo problema en notificar el dinero se continua
                //Inicio la enrolacion del equipo
                //
                $DataDn = Inventory::getDataDn($isprocess->msisdn);
                if (!empty($DataDn)) {
                  $idInv = $DataDn->inv_article_id;
                  $infoArt = Product::getProductById($idInv);
                  if (!empty($infoArt)) {

                    $data['brand'] = strtoupper($infoArt->brand);
                    //if ($data['brand'] == 'SAMSUNG') {
                    $data['imei'] = $DataDn->imei;
                    //}
                    $InitEnrole = ApiTelmovPay::sendRequest('api/enrollment-start', $data);
                    //  Log::info('data InitEnrole');
                    //  Log::info((String) json_encode($data));
                    //  Log::info('InitEnrole');
                    //  Log::info($InitEnrole);
                    if ($InitEnrole['success']) {
                      $EnroleStart = json_decode(json_encode($InitEnrole['data']));

                      if ($EnroleStart->success) {
                        $dataIntEnrole = json_decode(json_encode($EnroleStart->data));

                        //Se valida por si acaso viene algo del cuerpo en null cuando es samsung o otra marca
                        $status = isset($dataIntEnrole->status) ? $dataIntEnrole->status : null;
                        $lockProvider = isset($dataIntEnrole->lockProvider) ? $dataIntEnrole->lockProvider : null;
                        $lockReference = isset($dataIntEnrole->lockReference) ? $dataIntEnrole->lockReference : null;
                        $loanId = isset($dataIntEnrole->loanId) ? $dataIntEnrole->loanId : null;

                        $initEnrole = true;

                        if (strtoupper($input['brand']) == 'SAMSUNG') {
                          //Si es samsung no es necesario el Qr de inicio de enrolamiento
                          $enrollmentData = null;
                        } else {
                          //cualquier otra marca necesita ver el Qr de inicio de enrolamiento
                          $enrollmentData = (isset($dataIntEnrole->enrollmentData) && !empty($dataIntEnrole->enrollmentData)) ? $dataIntEnrole->enrollmentData : null;

                          if (!is_null($enrollmentData)) {

                            $QrEnrole = $dataIntEnrole->svg;
                            $Q = array("\n", "\\", "<?xml", 'version="1.0"', 'encoding="UTF-8"?>');
                            $QrInitEnrole = str_replace($Q, "", $QrEnrole);
                          } else {
                            $initEnrole = false;
                          }
                        }

                        if ($initEnrole) {
                          $starContract = TelmovPay::getConnect('W')
                            ->where([
                              ['seller_mail', session('user')],
                              ['dni', $input['dni']]])
                            ->whereIn('status', ['PE'])
                            ->update([
                              'status' => 'C',
                              'status_enrole' => $status,
                              'lockProvider' => $lockProvider,
                              'lockReference' => $lockReference,
                              'loan_id' => $loanId,
                              'enrollment_data' => $enrollmentData,
                            ]);

                          $EndContract->success = true;
                          $EndContract->msg = "Iniciar el emparejamiento del celular";
                        } else {
                          $EndContract->msg = "La data del inicio de enrolamiento llego invalido desde el proveedor del servicio";
                        }
                      } else {
                        $EndContract->msg = !empty($EnroleStart->message) ? $EnroleStart->message : 'No se obtuvo respuesta satisfactoria para ver el status del contrato';
                      }
                    } else {
                      $EndContract->msg = (!empty($InitEnrole['msg-telmov'])) ? $InitEnrole['msg-telmov'] : $InitEnrole['msg'];
                    }
                  } else {
                    $EndContract->msg = "No se puede continuar, no se encontro el articulo en inventario";
                  }
                } else {
                  $EndContract->msg = "No se puede continuar, no se encontro el msisdn en inventario";
                }
              }
            } else {
              $EndContract->error = true;
              $EndContract->msg = 'No se obtuvo respuesta satisfactoria al finalizar el contrato ante TelmovPay' . empty($FinContract->message) ? ". " . $FinContract->message : "";
            }
          } else {
            $EndContract->error = true;
            $EndContract->msg = (!empty($FinContract['msg-telmov'])) ? $FinContract['msg-telmov'] : $FinContract['msg'];
          }
        } else {
          $EndContract->msg = "No se puede continuar, no se encontraron datos de inicio de contrato";
        }
      }
      return response()
        ->json(['success' => $EndContract->success, 'icon' => $EndContract->typeAlert, 'error' => $EndContract->error, 'message' => $EndContract->msg, 'QrInitEnrole' => $QrInitEnrole]);
    }
    return redirect()->route('dashboard');
  }

  public function endEnrole(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      $EndEnrole = new \stdClass;
      $EndEnrole->success = false;
      $EndEnrole->typeAlert = 'alert-danger';
      $EndEnrole->msg = "No se puede continuar, faltan datos.";
      $QrSincronice = "";

      if (!empty($input['dni'])) {
        $isprocess = TelmovPay::inProcess(session('user'), ['C'], $input['dni']);
        if (!empty($isprocess)) {

          $DataDn = Inventory::getDataDn($isprocess->msisdn);
          if (!empty($DataDn)) {
            $idInv = $DataDn->inv_article_id;
            $infoArt = Product::getProductById($idInv);
            if (!empty($infoArt)) {

              $data = array(
                'seller_email' => session('user'),
                'brand' => strtoupper($infoArt->brand));

              $FinEnrole = ApiTelmovPay::sendRequest('api/enrollment-end', $data);
              // Log::info('FinEnrole');
              //  Log::info($FinEnrole);
              if ($FinEnrole['success']) {
                //Fin de enrolamiento
                $EnroleEnd = json_decode(json_encode($FinEnrole['data']));
                if ($EnroleEnd->success) {
                  //Cargo el QR de emparejamiento de financiamiento
                  //
                  $data = array(
                    'seller_email' => session('user'),
                    'loan_id' => $isprocess->loan_id);

                  $SincronApp = ApiTelmovPay::sendRequest('api/enrollment-telmovpay', $data);

                  //  Log::info('SincronApp');
                  //  Log::info($SincronApp);

                  if ($SincronApp['success']) {
                    $AppSincro = json_decode(json_encode($SincronApp['data']));
                    if ($AppSincro->success) {
                      $QrSincronice = $AppSincro->data;

                      $Q = array("\n", "\\", "<?xml", 'version="1.0"', 'encoding="UTF-8"?>');
                      $QrSincronice = str_replace($Q, "", $QrSincronice);

                      $saveEmpareje = TelmovPay::getConnect('W')
                        ->where([
                          ['seller_mail', session('user')],
                          ['dni', $input['dni']]])
                        ->whereIn('status', ['C'])
                        ->update([
                          'sincrone_data' => $QrSincronice,
                        ]);

                      $EndEnrole->success = true;
                      $EndEnrole->typeAlert = 'alert-success';
                      $EndEnrole->msg = "Puedes emparejar la APP de telmovPay con la financiacion realizada.";

                    } else {
                      $EndEnrole->msg = !empty($AppSincro->message) ? $AppSincro->message : 'No se obtuvo respuesta satisfactoria para generar el QR de emparejamiento del APP';
                    }
                  } else {
                    $EndEnrole->msg = (!empty($SincronApp['msg-telmov'])) ? $SincronApp['msg-telmov'] : $SincronApp['msg'];
                  }
                } else {
                  $EndEnrole->msg = !empty($EnroleEnd->message) ? $EnroleEnd->message : 'No se obtuvo respuesta satisfactoria para finalizar el emparejamiento.';
                }
              } else {
                $EndEnrole->msg = (!empty($FinEnrole['msg-telmov'])) ? $FinEnrole['msg-telmov'] : $FinEnrole['msg'];
              }
            } else {
              $EndEnrole->msg = "No se puede continuar, no se encuentra informacion del equipo";
            }
          } else {
            $EndEnrole->msg = "No se puede continuar, no se encuentra el msisdn en inventario netwey";
          }
        } else {
          $EndEnrole->msg = "No se puede continuar, no hay registros de procesos de inicio de enrolamiento de equipo.";
        }
      }
      return response()
        ->json(['success' => $EndEnrole->success, 'icon' => $EndEnrole->typeAlert, 'error' => false, 'message' => $EndEnrole->msg, 'QrSincronice' => $QrSincronice]);
    }
    return redirect()->route('dashboard');
  }

  public function sincronizeApp(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      $EndTelmov = new \stdClass;
      $EndTelmov->success = false;
      $EndTelmov->typeAlert = 'alert-danger';
      $EndTelmov->msg = "No se puede continuar, faltan datos.";

      if (!empty($input['dni'])) {
        $isprocess = TelmovPay::inProcess(session('user'), ['C'], $input['dni']);
        if (!empty($isprocess)) {

          $finishTelmov = TelmovPay::getConnect('W')
            ->where([
              ['seller_mail', session('user')],
              ['dni', $input['dni']]])
            ->whereIn('status', ['C'])
            ->update([
              'status' => 'CF',
              'date_process' => date('Y-m-d H:i:s'),
            ]);
          $EndTelmov->success = true;
          $EndTelmov->typeAlert = 'alert-success';
          $EndTelmov->msg = "Configuracion finalizada";

        } else {
          $EndTelmov->msg = "No se puede continuar, no hay registros de procesos de inicio de enrolamiento de equipo.";
        }
      }
      return response()
        ->json(['success' => $EndTelmov->success, 'icon' => $EndTelmov->typeAlert, 'error' => false, 'message' => $EndTelmov->msg]);
    }
    return redirect()->route('dashboard');
  }

}
