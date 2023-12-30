<?php

namespace App\Http\Controllers;

use App\Models\AssignedSales;
use App\Models\ConfigIstallments;
use App\Models\History_conect_platform;
use App\Models\Supports;
use App\Models\Installations;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SellerInventory;
use App\Models\SellerInventoryTemp;
use App\Models\User;
use App\Models\UserDeposit;
use App\Utilities\Altan;
use App\Utilities\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;

//use DateTime;

class dashboardController extends Controller
{
  public function index($email = null)
  {
    if (hasPermit('APV-DSE')) {
      //Array que contendrá los datos a mostrar en el dashboard
      $data = [
        'showSellers' => false,
        'sellersHasDebt' => false,
        'sellersLowHasDebt' => false,
        'balanceUser' => 0,
        'resAmount' => 0,
        'sales_inst' => [],
        'datesInstalations' => [],
        'caduce_instalations' => 0,
        'agendaInstalations' => [],
        'caduce_agenda' => 0,
        'asigneInstalations' => [],
        'caduce_asigne' => 0,
        'instalationsNotPaid' => [],
        'articles' => [],
        'articlesTe' => [],
        'articlesMI' => [],
        'articlesF' => [],
        'total_mount_e' => 0,
        'total_mount_e_tel' => 0,
        'total_mount_e_mi' => 0,
        'total_mount_e_mih' => 0,
        'total_mount_e_f' => 0,
        'due_coord' => 0,
        'due_coordTE' => 0,
        'due_coordMI' => 0,
        'due_coordMIH' => 0,
        'due_coordF' => 0,
        'detailCash' => false,
        'detailCashFSeller' => false,
        'detailCashInstFSeller' => false,
        'due' => 0,
        'dueTE' => 0,
        'dueMI' => 0,
        'dueMIH' => 0, //No se usa porque no se puede diferenciar el producto
        'dueF' => 0,
        'total_sales' => 0,
        'total_sales_t' => 0,
        'total_sales_mi' => 0,
        'total_sales_mih' => 0,
        'total_sales_inst' => 0,
        'total_sales_f' => 0,
        'salesDetail' => false,
        'salesDetailT' => false,
        'salesDetailMI' => false,
        'salesDetailMIH' => false,
        'salesDetail_inst' => false,
        'salesDetailF' => false,
        'redStatusAlert' => false,
        'preAssinedAlert' => false];

      //Usuario logueado
      $user = session('user');

      //Seleccionando usuario a consultar
      $userCon = $user;

      //Buscando usuarios asociados al usuario logueado
      if (session('hierarchy') >= env('HIERARCHY')) {
        $parents = User::getParents($user);

        if (!empty($email) && $user != $email && $parents->count()) {
          $isParent = $parents->filter(function ($value, $key) use ($email) {
            return $value->email == $email;
          });

          if ($isParent->count()) {
            $userCon = $email;
          } else {
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'No tienes permiso para consultar el usuario seleccionado.');
          }
        }

        $lowParents = User::getLowParents($user);

        if (!empty($email) && $user != $email && $lowParents->count()) {
          $isLowParent = $lowParents->filter(function ($value, $key) use ($email) {
            return $value->email == $email;
          });

          if ($isLowParent->count()) {
            $userCon = $email;
          } else {
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'No tienes permiso para consultar el usuario seleccionado.');
          }
        }
      }

      $data['select'] = $userCon;

      //Usuario superior
      $parentUser = User::getParentUser($user);

      //Consultando código de depósito
      if (!empty($parentUser)) {
        $data['cod_dep'] = UserDeposit::getCodDeposit($parentUser);
      }

      if (empty($data['cod_dep'])) {
        $data['cod_dep'] = UserDeposit::getCodDeposit($user);
      }

      //Balance para recargas
      $balance = User::getBalances($userCon);

      if (!empty($balance)) {
        $data['balanceUser'] = (float) trim($balance->charger_balance);
        $data['resAmount'] = $balance->residue_amount;
        $data['typeuser'] = $balance->platform;
      }

      if (!empty($parents) && $parents->count()) {
        //$loop = session('low_hierarchy') - session('hierarchy');
        $data['showSellers'] = true;

        //Calculando deuda de los vendedores
        foreach ($parents as $seller) {

          $sales = Sale::getSalesByuser($seller->email, 'E')
            ->orderBy('date_reg', 'ASC')
            ->get();

          $seller->debtcount = $sales->count();
          $seller->debtamount = $sales->sum('amount');

          if ($seller->debtcount > 0) {
            $data['sellersHasDebt'] = true;
            $seller->debtdays = Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d H:i:s', $sales[0]->date_reg));
          }
        }
        $data['sellers'] = $parents;
      }

      if (!empty($lowParents) && $lowParents->count()) {
        //$loop = session('low_hierarchy') - session('hierarchy');
        $data['showSellers'] = true;

        //Calculando deuda de los vendedores
        foreach ($lowParents as $lowSeller) {

          $sales = Sale::getSalesByuser($lowSeller->email, 'E')
            ->orderBy('date_reg', 'ASC')
            ->get();

          $lowSeller->debtcount = $sales->count();
          $lowSeller->debtamount = $sales->sum('amount');

          if ($lowSeller->debtcount > 0) {
            $data['sellersLowHasDebt'] = true;
            $lowSeller->debtdays = Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d H:i:s', $sales[0]->date_reg));
          }
        }
        $data['lowSellers'] = $lowParents;
      }

      //buscando pagos en abono
      $salesI = SaleInstallment::getPendingSales(!empty($email) ? $email : $user, session('user_type'));

      //Calculando data restante (cuotas, fecha de vencimiento,...)
      $today = time();
      foreach ($salesI as $sale) {
        //Si no se tiene una configuración consultada o el id la que se tiene es diferente a la asociada a la venta se obtiene de la bd la configuracion
        if (empty($config) || $config->id != $sale->config_id) {
          $config = ConfigIstallments::getConfigById($sale->config_id);
        }

        //Verificando si la fecha limite para pago de la proxima cuota expiro
        $dateSale = strtotime('+ ' . ($config->days_quote * $sale->quotes) . ' days',
          strtotime($sale->date_reg_alt)
        );
        $sale->date_expired = date('d-m-Y', $dateSale);

        //Separando resultados por vencidos y no vencidos.
        if ($today > $dateSale) {
          $orderSales['expired'][] = $sale;
        } elseif ($today >= strtotime('- ' . env('DAYS_NEXT_EXP') . ' days', $dateSale)) {
          $orderSales['nextExp'][] = $sale;
        }
      }

      if (!empty($orderSales)) {
        $data['sales_inst'] = $orderSales;
      }

      //Consultando inventario asignado
      $invAssig = SellerInventory::getArticsAssignData($userCon, 'H');

      if ($invAssig->count() > 0) {
        $data['articles'] = $invAssig;

        foreach ($invAssig as $article) {
          if (!empty($article->price_pay)) {
            $data['due'] += $article->price_pay;
          }
        }
      }

      $invAssigTe = SellerInventory::getArticsAssignData($userCon, 'T');

      if ($invAssigTe->count() > 0) {
        $data['articlesTe'] = $invAssigTe;

        foreach ($invAssigTe as $article) {
          if (!empty($article->price_pay)) {
            $data['dueTE'] += $article->price_pay;
          }
        }
      }

      $invAssigTe = SellerInventory::getArticsAssignData($userCon, 'M');

      if ($invAssigTe->count() > 0) {
        $data['articlesMI'] = $invAssigTe;

        foreach ($invAssigTe as $article) {
          if (!empty($article->price_pay)) {
            $data['dueMI'] += $article->price_pay;
          }
        }
      }

      $invAssigF = SellerInventory::getArticsAssignData($userCon, 'F');

      if ($invAssigF->count() > 0) {
        $data['articlesF'] = $invAssigF;

        foreach ($invAssigF as $article) {
          if (!empty($article->price_pay)) {
            $data['dueF'] += $article->price_pay;
          }
        }
      }

      //Consultando deuda en efectivo
      $saleInsAum = SaleInstallment::getAmountAndQty($userCon);

      $saleIns = SaleInstallment::getSalesDetailByUser($userCon);

      //Efectivo HBB
      $ventasTE = Sale::getSumSalesIns($userCon, 'H', $saleIns->pluck('unique_transaction'));

      $data['total_mount_e'] += !empty($ventasTE) ? $ventasTE->total_mount : 0;
      $data['total_mount_e'] += !empty($saleInsAum) ? $saleInsAum->total_mount : 0;

      //Efectivo MBB
      $ventasTETel = Sale::getSumSalesIns($userCon, 'T', $saleIns->pluck('unique_transaction'));
      $data['total_mount_e_tel'] = !empty($ventasTETel) ? $ventasTETel->total_mount : 0;

      //Efectivo MIFI
      $ventasTEMI = Sale::getSumSalesIns($userCon, 'M', $saleIns->pluck('unique_transaction'));
      $data['total_mount_e_mi'] = !empty($ventasTEMI) ? $ventasTEMI->total_mount : 0;

      //Efectivo MIFI Huella altan
      $ventasTEMIH = Sale::getSumSalesIns($userCon, 'MH', $saleIns->pluck('unique_transaction'));
      $data['total_mount_e_mih'] = !empty($ventasTEMIH) ? $ventasTEMIH->total_mount : 0;

      //Efectivo Fibra
      $ventasTEF = Sale::getSumSalesIns($userCon, 'F', $saleIns->pluck('unique_transaction'));
      $data['total_mount_e_f'] = !empty($ventasTEF) ? $ventasTEF->total_mount : 0;

      //Deuda en efectivo para el coordinador
      if (session('user_type') != 'vendor') {
        $assignSales = AssignedSales::getAmountAssignedSales($userCon, 'H');

        $data['due_coord'] += !empty($assignSales) ? $assignSales->total_due_assig : 0;

        $asigsalq = SaleInstallment::getAmountWS($userCon);

        $data['due_coord'] += !empty($asigsalq) ? $asigsalq->total_mount : 0;

        $assignSalesTe = AssignedSales::getAmountAssignedSales($userCon, 'T');
        $data['due_coordTE'] = !empty($assignSalesTe) ? $assignSalesTe->total_due_assig : 0;

        $assignSalesMi = AssignedSales::getAmountAssignedSales($userCon, 'M');
        $data['due_coordMI'] = !empty($assignSalesMi) ? $assignSalesMi->total_due_assig : 0;

        $assignSalesMiH = AssignedSales::getAmountAssignedSales($userCon, 'MH');
        $data['due_coordMIH'] = !empty($assignSalesMiH) ? $assignSalesMiH->total_due_assig : 0;

        $assignSalesF = AssignedSales::getAmountAssignedSales($userCon, 'F');
        $data['due_coordF'] = !empty($assignSalesF) ? $assignSalesF->total_due_assig : 0;
      }

      //Detalle de ventas (Efectivo) no conciliadas
      $detailNotCon = Sale::getUnConciSales($userCon);

      if ($detailNotCon->count() > 0) {
        $data['detailCash'] = $detailNotCon;
      }

      //Detalle de ventas del efectivo entregado por el vendedor
      $detailCashFSeller = AssignedSales::getSalesInfo($userCon);

      if ($detailCashFSeller->count() > 0) {
        $data['detailCashFSeller'] = $detailCashFSeller;
      }

      //Detalle de ventas en abono del efectivo entregado por el vendedor
      $detailCashInstFSeller = SaleInstallment::getSalesInfo($userCon);

      if ($detailCashInstFSeller->count() > 0) {
        $data['detailCashInstFSeller'] = $detailCashInstFSeller;
      }

      //Calculando las ventas de la semana
      $day = date('w');
      $day = $day == 0 ? 6 : ($day - 1);
      $data['dateSaleB'] = date('Y-m-d', strtotime('-' . $day . ' days')) . ' 00:00:00';
      $data['dateSaleE'] = date('Y-m-d', strtotime('+' . (6 - $day) . ' days')) . ' 23:59:59';

      $ventasT = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'H',
        'whtI' => true]);

      $saleDetail = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'H',
        'whtI' => true]);

      if (!empty($ventasT)) {
        $data['total_sales'] = $ventasT->total_sales;
        //$data->total_mount = $ventasT->total_mount;
        $data['salesDetail'] = $saleDetail;
      }

      //Ventas telefonía
      $ventasTel = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'T',
        'whtI' => true]);

      $saleDetailTE = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'T',
        'whtI' => true]);

      if (!empty($ventasTel)) {
        $data['total_sales_t'] = $ventasTel->total_sales;
        $data['salesDetailT'] = $saleDetailTE;
      }

      //ventas MIFI
      $ventasMi = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'M',
        'whtI' => true]);

      $saleDetailMI = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'M',
        'whtI' => true]);

      if (!empty($ventasMi)) {
        $data['total_sales_mi'] = $ventasMi->total_sales;
        $data['salesDetailMI'] = $saleDetailMI;
      }

      //Ventas Fibra
      $ventasF = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'F',
        'whtI' => true]);

      $saleDetailF = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'F',
        'whtI' => true]);

      if (!empty($ventasF)) {
        $data['total_sales_f'] = $ventasF->total_sales;
        $data['salesDetailF'] = $saleDetailF;
      }

      //Ventas MIFI huella altan total_sales_mih
      $ventasMih = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'MH',
        'whtI' => true]);

      $saleDetailMIH = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        'type' => 'MH',
        'whtI' => true]);

      if (!empty($ventasMih)) {
        $data['total_sales_mih'] = $ventasMih->total_sales;
        $data['salesDetailMIH'] = $saleDetailMIH;
      }

      //Ventas en abono
      $ventasInst = Sale::getTotalSalesByType([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        //'type' => 'T',
        'whtI' => false]);

      $saleDetailInst = Sale::getDetailSalesUser([
        'user' => $userCon,
        'dateB' => $data['dateSaleB'],
        'dateE' => $data['dateSaleE'],
        //'type' => 'T',
        'whtI' => false]);

      if (!empty($ventasInst)) {
        $data['total_sales_inst'] = $ventasInst->total_sales;
        //$data->total_mount_inst = $ventasInst->total_mount;
        $data['salesDetail_inst'] = $saleDetailInst;
      }

      if (hasPermit('SEL-INF')) {
        //Citas que tiene asignada la persona logeada
        //
        //consultando citas de instalción, se comento el paginado semanal que se tenia
        //$data['datesInstalations'] = Installations::getPendingDatesForInstaller(session('user'), false, $data['dateSaleE']);
        //
        $data['datesInstalations'] = Installations::getPendingDatesForInstaller(session('user'));

        $data['caduce_instalations'] = Installations::getInstallationCaduce(session('user'));
      }
      if (hasPermit('FIB-VLC')) {
        //citas por instalar que ya tiene instalador
        $data['asigneInstalations'] = Installations::getPendingDatesForInstaller(session('user'), false, false, 'ListinstallerBoss');

        $data['caduce_asigne'] = Installations::getInstallationCaduce(session('user'), "ListinstallerBoss");
      }
      if (hasPermit('FIB-VAC')) {
        //citas por asignar instalador
        $data['agendaInstalations'] = Installations::getPendingDatesForInstaller(session('user'), false, false, 'installerBoss');

        $data['caduce_agenda'] = Installations::getInstallationCaduce(session('user'), "installerBoss");
      }

      if (hasPermit('FIB-VLD')) {

        //citas por asignar para soporte
        $data['agendaSupports'] = Supports::getPendingDatesForSupport(session('user'), false, false, 'installerBoss');

        $data['caduce_support_agenda'] = Supports::getSupportCaduce(session('user'), "installerBoss");

        $data['showNextSupports'] = false;
        
      }

      //Verificando si hay instalaciones para la siguiente semana
      //$nextWeek = date('Y-m-d', strtotime('+7 days', strtotime($data['dateSaleE']))).' 23:59:59';
      //nextED = Installations::getPendingDatesForInstaller(session('user'), $data['dateSaleE'], $nextWeek);
      $data['showNextInstalations'] = false;
      //Muestra el botón de cargar mas siempre y cuando hayan citas para la siguiente semana y la actual tenga citas
      /*if($nextED->count() && $data['datesInstalations']->count()){
      $data['showNextInstalations'] = $nextWeek;
      $data['datesInstalationsB'] = $data['dateSaleE'];
      }*/
      //Si la semana actual no tiene data, envia la data de la siguiente semana
      /*if(!$data['datesInstalations']->count()){
      $data['datesInstalations'] = $nextED;
      }*/

      //Instalaciones pendientes de pago
      $data['instalationsNotPaid'] = Installations::getPendingPay(session('user'))->get();

      //Notificaciones de estatus rojo
      $data['redStatusAlert'] = SellerInventory::redStatusNotificationsPendingExists(session('user'));

      if ($data['redStatusAlert']) {
        $data['preAssinedAlert'] = false;
        $data['preAssinedRejectAlert'] = false;
      } else {
        $data['preAssinedAlert'] = false;
        $data['preAssinedRejectAlert'] = false;
        if (showMenu(['SEL-ARI']) && session('user_type') == 'vendor') {
          $data['preAssinedAlert'] = SellerInventoryTemp::preAssinedNotificationsPendingExists(session('user'));
        } else {
          $data['preAssinedRejectAlert'] = SellerInventoryTemp::preAssinedNotificationsRejectsExists(session('user'));
        }
      }

      return view('dashboard.home_seller', compact('data'));
    }

    session()->flash('message_class', 'alert-danger');
    session()->flash('message_error', 'No tienes permiso para ver esta sección.');
    return redirect()->route('logout');
  }

  public function getTotalSalesByDate(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = new \stdClass;

      $data->salesDetail = false;
      $data->total_sales = 0;

      $userCon = $request->user;

      if ($request->user != session('user')) {
        if (session('hierarchy') >= env('HIERARCHY')) {
          $parents = User::getParents(session('user'));
        }

        if (empty($parents) || !$parents->count()) {
          return response()->json(array(
            'error' => true,
            'msg' => 'No tienes permisos para consular el usuario',
          ));
        }

        $isParents = $parents->filter(function ($value, $key) use ($userCon) {
          return $value->email == $userCon;
        });

        if (empty($isParents) || !$parents->count()) {
          return response()->json(array(
            'error' => true,
            'msg' => 'No tienes permisos para consular el usuario',
          ));
        }
      }

      if (!empty($request->date)) {
        $date = explode(' / ', $request->date);
        if (count($date) == 2) {
          $data->dateSaleB = date('Y-m-d H:i:s', strtotime($date[0]));
          $data->dateSaleE = date('Y-m-d H:i:s', strtotime($date[1]) + (3600 * 23) + 3599);
        }
      }

      if (empty($data->dateSaleB) || empty($data->dateSaleE)) {
        return response()->json(array(
          'error' => true,
          'msg' => 'Faltan datos, no se puede ejecutar la consulta',
        ));
      }

      $ventasT = Sale::getSalesMetric(
        $request->type,
        $data->dateSaleB,
        $data->dateSaleE,
        $userCon
      );

      $salesDetail = Sale::getSalesMetricDetail(
        $request->type,
        $data->dateSaleB,
        $data->dateSaleE,
        $userCon
      );

      if (!empty($ventasT)) {
        $data->total_sales = $ventasT->total_sales;
        $data->salesDetail = $salesDetail;
      }

      $html = view('dashboard.tableSalesDetail', compact('salesDetail'))->render();

      return response()->json(array(
        'error' => false,
        'data' => $data,
        'dates' => $request->date,
        'detail' => $html,
        'type' => '-' . $request->type,
      ));
    }
  }

  public function serviciability(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $response = [
        'error' => true,
        'message' => 'Falta geoposición.',
      ];

      if (!empty($inputs['lat']) && !empty($inputs['lon'])) {
        $resHbb = Altan::serviceability($inputs['lat'], $inputs['lon']);
        $resMH = Altan::serviceability($inputs['lat'], $inputs['lon'], '', true);

        if ($resHbb['success'] && $resMH['success']) {
          $wide = Common::getWide($resHbb['data']);

          $response['error'] = false;
          $response['message'] = 'Zona apta solo para venta de internet hogar hasta ' . $wide . ' mbps e internet móvil.';
        } elseif ($resHbb['success'] && !$resMH['success']) {
          $wide = Common::getWide($resHbb['data']);

          $response['error'] = false;
          $response['message'] = 'Zona apta solo para venta de internet hogar hasta ' . $wide . ' mbps.';
        } elseif (!$resHbb['success'] && $resMH['success']) {
          $response['error'] = false;
          $response['message'] = 'Zona apta solo para venta de internet móvil.';
        } else {
          $response['error'] = false;
          $response['message'] = 'Zona sin cobertura.';
        }

        /*if($res['success'] || (!empty($res['service']) && ($res['service'] == 'E-BLK' || $res['service'] == 'E-RES'))){
      if($res['success']){
      $wide = Common::getWide($res['data']);

      $response['error'] = false;
      $response['message'] = 'Zona apta para venta de internet hogar hasta '.$wide.' mbps, internet móvil e internet móvil nacional.';
      }else{
      $response['error'] = false;
      $response['message'] = 'Zona apta para venta de internet móvil e internet móvil nacional.';
      }
      }else{
      $response['message'] = 'Zona apta para venta de internet móvil nacional, sujeto a cobertura de roaming en México.';
      }*/
      }

      return response()->json($response);
    }
  }

  public static function downloadInv(Request $request)
  {
    if ($request->isMethod('post')) {
      $user = $request->input('user');

      if (!empty($user)) {
        $ban = false;
        if ($user != session('user')) {
          //Buscando usuarios asociados al usuario logueado y validando que el consultado este a su cargo
          if (session('hierarchy') >= env('HIERARCHY')) {
            $parents = User::getParents(session('user'));

            if ($parents->count()) {
              $isParent = $parents->filter(function ($value, $key) use ($user) {
                return $value->email == $user;
              });

              if ($isParent->count()) {
                $ban = true;
              }
            }
          }
        }

        if ($user == session('user') || $ban) {
          $invAssig = SellerInventory::getAllArticsAssign($user);

          $fileName = 'inv_asig_' . date('Ymd');

          $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $fileName . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
          );

          $columns = array(
            'Numero',
            'Equipo',
            'MSISDN',
            'TIPO',
            'IMEI',
            'ICCID',
            'Fecha asig.',
          );

          $callback = function () use ($invAssig, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $pos = 1;
            foreach ($invAssig as $inv) {
              $data = [
                $pos,
                $inv->title,
                $inv->msisdn,
                $inv->artic_type == 'H' ? 'Internet Hogar' : ($inv->artic_type == 'T' ? 'Telefonia Celular' : 'MIFI'),
                !empty($inv->imei) ? $inv->imei : 'N/A',
                $inv->artic_type == 'T' ? $inv->iccid : 'N/A',
                date('d-m-Y', strtotime($inv->date_reg)),
              ];

              $pos++;

              fputcsv($file, $data);
            }
            fclose($file);
          };
          return response()->stream($callback, 200, $headers);
        }
      }
    }

    return redirect()->route('dashboard');
  }

/**
 * [registerConection Registro de la plataforma usada por los vendedores que ahcen uso del seller]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function registerConection(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $idLast = History_conect_platform::insertHistoryConection($request->all(), $request->ip());
      //actualizamos el user la ultima conexion del seller

      User::setLastConection(session('user'), $idLast);
      return response()->json(['success' => true, 'msg' => "Conexion establecida"]);
    }
    return redirect()->route('dashboard');
  }

}
