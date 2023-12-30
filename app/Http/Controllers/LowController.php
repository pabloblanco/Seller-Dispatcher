<?php

namespace App\Http\Controllers;

use App\Models\LowEvidence;
use App\Models\LowRequest;
use App\Models\Low_Reason;
use App\Models\Sale;
use App\Models\SellerInventory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para las bajas de vendedores
 */
class LowController extends Controller
{
  /**
   * [viewNewRequest Vista de nueva solicitud de baja de vendedor]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function viewNewRequest(Request $request)
  {
    $lock   = User::getOnliyUser(session('user'));
    $Reason = Low_Reason::getReason();
    return view('low.newRequest', compact('lock', 'Reason'));
  }

  /**
   * [getSalesUser Obtiene las ventas de los ultimos quince dias cuando el motivo de baja es por productividad]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function getSalesUser(Request $request)
  {
    $email = $request->has('seller') ? $request->input('seller') : false;

    if (!empty($email)) {
      $hoy       = date("Y-m-d H:i:s");
      $Last2Week = date("Y-m-d H:i:s", strtotime($hoy . "- 15 day"));
#ventas normales
      $filter = array(
        'user'  => $request->seller,
        'whtI'  => true,
        'dateB' => $Last2Week,
        'dateE' => $hoy);
      $infosales = Sale::getTotalSalesByType($filter);

      $htmlSales   = false;
      $total_sales = 0;
      $total_mount = 0;
      $viewMount   = true;

#inicializacion =0;
      $efect_cant = 0;
      $efect      = 0;
      $abono_cant = 0;
      $abono      = 0;
#end inicializacion
      //OJO islim_sales.type EL TIPO V
      $stock = Sale::getSaleUser($filter);

      $efect_cant = !empty($infosales->total_sales) ? $infosales->total_sales : 0;
      $efect      = !empty($infosales->total_mount) ? $infosales->total_mount : 0;

#abonos
      $filter2 = array(
        'user'  => $request->seller,
        'whtI'  => false,
        'dateB' => $Last2Week,
        'dateE' => $hoy);
      $ventasAbono = Sale::getTotalSalesByType($filter2);

      //OJO islim_sales.type EL TIPO V
      $stockAbono = Sale::getSaleUser($filter2);
      $abono_cant = !empty($ventasAbono->total_sales) ? $ventasAbono->total_sales : 0;
      $abono      = !empty($ventasAbono->total_mount) ? $ventasAbono->total_mount : 0;

      $total_sales = $efect_cant + $abono_cant;
      $total_mount = $efect + $abono;

      if (!empty($stock)) {
        $stock = $stock->union($stockAbono);
      } else {
        $stock = $stockAbono;
      }

      $stock = $stock->get();

      $htmlSales = view('low.inv_detail_low', compact('stock', 'viewMount'))->render();

      return response()->json(array(
        'success'    => true,
        'date_star'  => $Last2Week,
        'date_end'   => $hoy,
        'cantSales'  => $total_sales,
        'mountSales' => $total_mount,
        'salesView'  => $htmlSales));
    }
    return response()->json(array('success' => false));
  }

  /**
   * [getDeudaUser Obtencion de los datos de dueda de el usuario que esta pidiendo la baja]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function getDeudaUser(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      session(['userLow' => null]);
      $infoLow = new \stdClass;

      $sales = Sale::getSalesByuser($request->input('seller'), 'E')
        ->orderBy('date_reg', 'ASC')
        ->get();

      $infoLow->debtcount  = $sales->count();
      $infoLow->debtamount = $sales->sum('amount');
      $infoLow->debtdays   = 0;

      if ($infoLow->debtcount > 0) {
        $infoLow->debtdays = Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d H:i:s', $sales[0]->date_reg));
      }

      $infoLow->inv_deuda_hbb   = 0;
      $infoLow->inv_cant_hbb    = 0;
      $infoLow->inv_deuda_telf  = 0;
      $infoLow->inv_cant_telf   = 0;
      $infoLow->inv_deuda_mifi  = 0;
      $infoLow->inv_cant_mifi   = 0;
      $infoLow->inv_deuda_fibra = 0;
      $infoLow->inv_cant_fibra  = 0;

      // $infoLow->deuda_abono     = 0;
      // $infoLow->count_abono     = 0;

      //Deuda en inventario HBB
      $invAssig = SellerInventory::getArticsAssignData($request->input('seller'), 'H');

      if ($invAssig->count() > 0) {
        $infoLow->inv_cant_hbb = $invAssig->count();

        foreach ($invAssig as $article) {
          if (!empty($article->price_pay)) {
            $infoLow->inv_deuda_hbb += $article->price_pay;
          }
        }
      }
      //Deuda en inventario telefonia
      $invAssigTe = SellerInventory::getArticsAssignData($request->input('seller'), 'T');

      if ($invAssigTe->count() > 0) {
        $infoLow->inv_cant_telf = $invAssigTe->count();

        foreach ($invAssigTe as $article) {
          if (!empty($article->price_pay)) {
            $infoLow->inv_deuda_telf += $article->price_pay;
          }
        }
      }
      //Deuda en inventario mifi
      $invAssigTe = SellerInventory::getArticsAssignData($request->input('seller'), 'M');

      if ($invAssigTe->count() > 0) {
        $infoLow->inv_cant_mifi = $invAssigTe->count();

        foreach ($invAssigTe as $article) {
          if (!empty($article->price_pay)) {
            $infoLow->inv_deuda_mifi += $article->price_pay;
          }
        }
      }
      //Deuda en inventario fibra
      $invAssigF = SellerInventory::getArticsAssignData($request->input('seller'), 'F');

      if ($invAssigF->count() > 0) {
        $infoLow->inv_cant_fibra = $invAssigF->count();

        foreach ($invAssigF as $article) {
          if (!empty($article->price_pay)) {
            $infoLow->inv_deuda_fibra += $article->price_pay;
          }
        }
      }

      //Deuda Ventas en abono
      $ventasAbono = Sale::getTotalSalesByType([
        'user' => $request->input('seller'),
        'whtI' => false]);

      if (!empty($ventasAbono)) {
        $infoLow->deuda_abono = $ventasAbono->total_mount;
        $infoLow->count_abono = $ventasAbono->total_sales;
      }

      session(['userLow' => $infoLow]);

      return array('success' => true, 'error' => false,
        'cantDeuda'            => $infoLow->debtcount,
        'mountDeuda'           => $infoLow->debtamount,
        'dayDeuda'             => $infoLow->debtdays,
        'inv_deuda_hbb'        => $infoLow->inv_deuda_hbb,
        'inv_deuda_telf'       => $infoLow->inv_deuda_telf,
        'inv_deuda_mifi'       => $infoLow->inv_deuda_mifi,
        'inv_deuda_fibra'      => $infoLow->inv_deuda_fibra,
        'inv_cant_hbb'         => $infoLow->inv_cant_hbb,
        'inv_cant_telf'        => $infoLow->inv_cant_telf,
        'inv_cant_mifi'        => $infoLow->inv_cant_mifi,
        'inv_cant_fibra'       => $infoLow->inv_cant_fibra,

        //'deuda_abono'          => $infoLow->deuda_abono,
        //'count_abono'          => $infoLow->count_abono
      );
    }
    return array('success' => false);
  }

  /**
   * [regLowUser Registrar una nueva solicitud de baja]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function regLowUser(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      if (LowRequest::getAvailableRequest($request->input('sellerMail'))) {

        // $infoSolicitud = new \stdClass;
        $infoSolicitud                   = array();
        $infoSolicitud['user_dismissal'] = $request->input('sellerMail');
        $infoSolicitud['id_reason']      = $request->input('reason');
        $infoSolicitud['user_req']       = session('user');
        $infoSolicitud['date_reg']       = date("Y-m-d H:i:s");

        //se calcula la deuda del vendedor
        //
        $infoLow = session('userLow');

        //END se calcula la deuda del vendedor
        //

        $infoSolicitud['cash_request']      = !empty($infoLow->debtamount) ? $infoLow->debtamount : null;
        $infoSolicitud['days_cash_request'] = !empty($infoLow->debtdays) ? $infoLow->debtdays : null;
        $deudaInv                           = $infoLow->inv_deuda_hbb + $infoLow->inv_deuda_telf + $infoLow->inv_deuda_mifi + $infoLow->inv_deuda_fibra;
        //Deuda en inventario
        $infoSolicitud['article_request'] = !empty($deudaInv) ? $deudaInv : null;
        $infoSolicitud['cash_hbb']        = !empty($infoLow->inv_deuda_hbb) ? $infoLow->inv_deuda_hbb : null;
        $infoSolicitud['cash_telf']       = !empty($infoLow->inv_deuda_telf) ? $infoLow->inv_deuda_telf : null;
        $infoSolicitud['cash_mifi']       = !empty($infoLow->inv_deuda_mifi) ? $infoLow->inv_deuda_mifi : null;
        $infoSolicitud['cash_fibra']      = !empty($infoLow->inv_deuda_fibra) ? $infoLow->inv_deuda_fibra : null;
        $infoSolicitud['cash_abonos']     = !empty($infoLow->deuda_abono) ? $infoLow->deuda_abono : null;
        $infoSolicitud['cant_abonos']     = !empty($infoLow->count_abono) ? $infoLow->count_abono : null;
//La deuda en abono no se suma, ya esta incorporado en la deuda en efectivo
        $deudaTotal                  = $deudaInv + $infoLow->debtamount;
        $infoSolicitud['cash_total'] = $deudaTotal;

        $saveinfo   = false; //Se registro en BD
        $savefile   = false; //Se guardo en s3 el archivo
        $errorFile  = ''; //Error al subir el archivo
        $isLoadFile = false; //Se envio del formulario un archivo
        try {
          LowRequest::getConnect('W')->insert($infoSolicitud);
          $saveinfo = true;
        } catch (Exception $e) {
          $saveinfo = false;
          return response()->json(array('success' => false, 'msg' => "No se pudo guardar la solicitud de baja. " . $e->getMessage()));
        }
        session(['userLow' => null]);
        sleep(2);
        usleep(1000000);

        $newLow = null;
        if ($saveinfo) {
          //Busco el ultimo registro que se cargo
          $newLow = LowRequest::getPullRequest(session('user'), $request->input('sellerMail'), $request->input('reason'));
        }

        if (!empty($newLow)) {

          $EvidenceSolicitud                     = array();
          $EvidenceSolicitud['id_req_dismissal'] = $newLow->id;
          //Se configuro para solo cargar 3 evidencias fotograficas
          for ($i = 1; $i <= 3; $i++) {
            $variablePhoto = null;

            if ($i == 1 && isset($request->photo1)) {
              $variablePhoto = $request->photo1;
            } elseif ($i == 2 && isset($request->photo2)) {
              $variablePhoto = $request->photo2;
            } elseif ($i == 3 && isset($request->photo3)) {
              $variablePhoto = $request->photo3;
            }
            //  $variablePhoto = $request->photo . $i;
            if (!empty($variablePhoto)) {
              //print_r($request->file('photo' . $i));
              //SellerFiberController line 189 imagenes S3

              $path = 'low/evidence-photo/';

              if ($request->hasFile('photo' . $i) && !empty($request->file('photo' . $i))) {
                $isLoadFile = true;

                $photo = $request->file('photo' . $i);
                //print_r($photo);
                $photoPath = $path . uniqid() . time() . '.' . $photo->getClientOriginalExtension();

                try {
                  Storage::disk('s3')->put(
                    $photoPath,
                    file_get_contents($photo->getPathname(), FILE_USE_INCLUDE_PATH),
                    'public'
                  );
                  $urlPhoto                      = (String) Storage::disk('s3')->url($photoPath);
                  $EvidenceSolicitud['date_reg'] = date("Y-m-d H:i:s");
                  $EvidenceSolicitud['url']      = $urlPhoto;
                  //Log::info($urlPhoto);
                  LowEvidence::getConnect('W')->insert($EvidenceSolicitud);
                  $savefile = true;
                } catch (Exception $e) {
                  $savefile  = false;
                  $errorFile = $e->getMessage();
                }
              }
            }
          }
          if ($savefile) {
            return array('success' => true, 'msg' => "Se registro exitosamente la solicitud de baja", 'icon' => 'success');
          }
        }
        if ($saveinfo && !$savefile && $isLoadFile) {
          if (!empty($errorFile)) {
            $errorFile = ' +Info: ' . $errorFile;
          }
          return array('success' => true, 'msg' => "La solicitud se registro pero no se logro cargar la evidencia" . $errorFile, 'icon' => 'info');
        } elseif ($saveinfo && !$isLoadFile) {
          return array('success' => true, 'msg' => "Se registro exitosamente la solicitud de baja", 'icon' => 'success');
        } else {
          return response()->json(array('success' => false, 'msg' => "La solicitud no pudo ser registrada", 'icon' => 'warning'));
        }
      }
      return response()->json(array('success' => false, 'msg' => "Lo sentimos, el usuario " . $request->input('sellerMail') . " se encuentra en un proceso activo de baja"));
    }
  }

  /**
   * [viewRequestsList Vista de solicitudes de baja en proceso]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function viewRequestsList(Request $request)
  {
    $lock     = User::getOnliyUser(session('user'));
    $userslow = LowRequest::getUsersLowsInProcess(session('user'));

    $html_list = view('low.view_requests_list', compact('userslow'))->render();

    return view('low.viewRequests', compact('lock', 'html_list'));
  }

  /**
   * [getRequestsList obtiene listado de solicitudes de baja en proceso]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function getRequestsList(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $status = null;
      if (!empty($request->status)) {
        if ($request->status != '') {
          $status = $request->status;
        }
      }

      $vendor = null;
      if (!empty($request->vendor)) {
        if ($request->vendor != '') {
          $vendor = $request->vendor;
        }
      }

      $userslow = LowRequest::getUsersLowsInProcess(session('user'), [
        'status' => $status,
        'vendor' => $vendor]);

      $html_list = view('low.view_requests_list', compact('userslow'))->render();

      return response()->json(['success' => true, 'html' => $html_list]);
    }
    return array('success' => false);
  }
}
