<?php

namespace App\Http\Controllers;

use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\BankDeposits;
use App\Models\HistoryDebts;
use App\Models\HistoryDebtUps;
use App\Models\HistoryDebtCash;
use App\Models\HistoryDebtConciliates;
use App\Models\Inventory;
use App\Models\PayInstallment;
use App\Models\Policy;
use App\Models\ProfileDetail;
use App\Models\Sale;
use App\Models\SaleInstallmentDetail;
use App\Models\SellerInventory;
use App\Models\SellerInventoryTemp;
use App\Models\SellerInventoryTrack;
use App\Models\User;
use App\Models\UserDeposit;
use App\Models\LowRequest;
use App\Utilities\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;

class coordinationController extends Controller
{
  public function stock()
  {
    $lock = User::getOnliyUser(session('user'));

    $invHbb  = false;
    $invMbb  = false;
    $invMIFI = false;

    if (!empty($lock) && $lock->is_locked == 'N') {
      $invHbb = SellerInventory::getArticsAssignData(session('user'), 'H');

      $invMbb = SellerInventory::getArticsAssignData(session('user'), 'T')->sortBy('iccid');

      $invMIFI = SellerInventory::getArticsAssignData(session('user'), 'M');
    }

    return view('coordination.stock', compact('lock', 'invHbb', 'invMbb', 'invMIFI'));
  }

  public function findInveSeller(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->seller)) {
        $seller = User::getOnliyUser($request->seller, 'R', ['A', 'D']);

        if (!empty($seller)) {
          //Consultando stock por tipo del vendedor
          $stockHbb = SellerInventory::getArticsAssignData($request->seller, 'H');
          $stockPHbb = SellerInventoryTemp::getArticsPreAssignData($request->seller, 'H');

          $stockMbb = SellerInventory::getArticsAssignData($request->seller, 'T');
          $stockPMbb = SellerInventoryTemp::getArticsPreAssignData($request->seller, 'T');

          $stockMIFI = SellerInventory::getArticsAssignData($request->seller, 'M');
          $stockPMIFI = SellerInventoryTemp::getArticsPreAssignData($request->seller, 'M');

          //$stockF = SellerInventory::getArticsAssignData($request->seller, 'F');
          //$stockPF = SellerInventoryTemp::getArticsPreAssignData($request->seller, 'F');

          //Uniendo stock en una sola colección
          $stock = Common::joinColection($stockHbb, $stockPHbb);

          $stock = Common::joinColection($stockMbb, $stock);
          $stock = Common::joinColection($stockPMbb, $stock);

          $stock = Common::joinColection($stockMIFI, $stock);
          $stock = Common::joinColection($stockPMIFI, $stock);

          //$stock = Common::joinColection($stockF, $stock);
          //$stock = Common::joinColection($stockPF, $stock);

          $isReq = false;
          if (!isset($request->low)) {
            $htmlStock = view('components.inv_detail', compact('stock'))->render();
          } else {
            $isReq = LowRequest::getRequestByStatusAndUser($request->seller, 'R');
            $isReq = !empty($isReq);
            $htmlStock = view('low.inv_detail_low', compact('stock'))->render();
          }

          //ventas activas del vendedor
          $ventas = Sale::getActiveSalesBySeller($request->seller);

          //Politicas de limite de inventario
          $lh  = Policy::getUserPolicy($request->seller, 'LIV-DSE');
          $lm  = Policy::getUserPolicy($request->seller, 'LIV-DSM');
          $lmi = Policy::getUserPolicy($request->seller, 'LIV-MIF');

          return response()->json([
            'error'         => false,
            'isSalesActive' => ($ventas->count() > 0),
            'isDismissal'   => $seller->status == 'D',
            'isInProcess'   => $isReq,
            'seller'        => $seller,
            'stockHbb'      => $stockHbb->count(),
            'stockMbb'      => $stockMbb->count(),
            'stockMIFI'     => $stockMIFI->count(),
            'stock'         => $stock,
            'htmlStock'     => $htmlStock,
            'limitInvMbb'   => !empty($lm) ? ((int) $lm->value) : 0,
            'limitInvHbb'   => !empty($lh) ? ((int) $lh->value) : 0,
            'limitInvMIFI'  => !empty($lmi) ? ((int) $lmi->value) : 0]);
        }
      }

      return response()->json(['error' => true, 'message' => 'Faltan datos.']);
    }
  }

  public function addStock(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {
      $message = 'Faltan datos.';

      if (!empty($request->msisdn) && !empty($request->seller) && !empty($request->type)) {
        $seller = $request->seller;

        //Validando que el usuario al que le van a asignar el inventario este activo
        if(empty(User::isActive($seller))){
          return response()->json(['error' => true, 'message' => 'El usuario no puede recibir inventario']);
        }

        if (session('hierarchy') >= env('HIERARCHY')) {
          $parents = User::getParents(session('user'));
        }

        if (!empty($parents) && $parents->count()) {
          $userSeller = $parents->filter(function ($value, $key) use ($seller) {
            return $value->email == $seller;
          });

          if ($userSeller->count()) {
            if ($request->type == 'HBB') {
              $limitInv = Policy::getUserPolicy($seller, 'LIV-DSE');

              $invAssig = SellerInventory::getArticsAssignData($seller, 'H')->count();
            }

            if ($request->type == 'MBB') {
              $limitInv = Policy::getUserPolicy($seller, 'LIV-DSM');

              $invAssig = SellerInventory::getArticsAssignData($seller, 'T')->count();
            }

            if ($request->type == 'MIFI') {
              $limitInv = Policy::getUserPolicy($seller, 'LIV-MIF');

              $invAssig = SellerInventory::getArticsAssignData($seller, 'M')->count();
            }

            //Pasando a array el o los msisdns recibidos
            if (!is_array($request->msisdn)) {
              $msisdnArr = [$request->msisdn];
            } else {
              $msisdnArr = $request->msisdn;
            }

            if (!empty($limitInv) && $limitInv->value >= ($invAssig + count($msisdnArr))) {
              $articles = Inventory::getArticsByDns($msisdnArr, session('user'));

              if (count($articles)) {
                $stock = [];
                foreach ($articles as $article) {

                  $is_seller=false;

                  $is_seller=ProfileDetail::getConnect('R')
                    ->join('islim_profiles','islim_profiles.id','islim_profile_details.id_profile')
                    ->where([
                      'islim_profile_details.user_email' => $request->seller,
                      'islim_profile_details.status' => 'A',
                      'islim_profiles.platform' => 'vendor'
                    ])
                    ->count();

                  if($is_seller > 0){ //es vendedor, se preasigna el inventario

                    $preassigment = SellerInventoryTemp::getConnect('W');
                    $preassigment->user_email = $request->seller;
                    $preassigment->inv_arti_details_id  = $article->id;
                    $preassigment->status = 'P';
                    $preassigment->assigned_by = session('user');
                    $preassigment->date_reg = date('Y-m-d H:i:s', time());
                    $preassigment->date_status = date('Y-m-d H:i:s', time());
                    $preassigment->notification_view = 'N';
                    $preassigment->save();

                    $article->preassigned = 'Y';

                  }
                  else{ //si no es vendedor se asigna

                    $invs = SellerInventory::getConnect('R')
                    ->where('inv_arti_details_id', $article->id)
                    ->where('status', 'A')
                    ->first();

                    SellerInventory::getConnect('W')
                    ->where('inv_arti_details_id', $article->id)
                    ->where('status', 'A')
                    ->update([
                      'status' => "T"
                    ]);

                    /* foreach ($invs as $key => $inv) {

                    $inventory = Inventory::getConnect('R')->find($invs->inv_arti_details_id);

                    SellerInventoryTrack::setInventoryTrack(
                    $invs->inv_arti_details_id,
                    $invs->users_email,
                    null,
                    null,
                    $inventory->warehouses_id,
                    session('user')
                    );
                    }*/

                    $ifExistsAssig = SellerInventory::getAsignmentUser($article->id, $request->seller);

                    if (!empty($ifExistsAssig)) {
                      SellerInventory::getConnect('W')
                        ->where([
                          ['inv_arti_details_id', $article->id],
                          ['users_email', $request->seller],
                        ])
                        ->update([
                          'date_reg'         => date("Y-m-d H:i:s"),
                          'status'           => 'A',
                          'date_orange'      => !empty($invs) ? $invs->date_orange : null,
                          'last_assignment'  => date('Y-m-d H:i:s'),
                          'last_assigned_by' => session('user')]);
                    } else {
                      SellerInventory::getConnect('W')
                        ->insert([
                          'users_email'         => $request->seller,
                          'inv_arti_details_id' => $article->id,
                          'date_reg'            => date("Y-m-d H:i:s"),
                          'status'              => 'A',
                          'date_orange'         => !empty($invs) ? $invs->date_orange : null,
                          'last_assignment'     => date('Y-m-d H:i:s'),
                          'last_assigned_by'    => session('user')]);
                    }

                    $inventory = Inventory::getConnect('R')->find($article->id);
                    SellerInventoryTrack::setInventoryTrack(
                      $article->id,
                      !empty($invs) ? $invs->users_email : null,
                      !empty($invs) ? null : $inventory->warehouses_id,
                      $request->seller,
                      null,
                      session('user')
                    );

                    $article->preassigned = 'N';

                  }
                  $stock[] = $article;
                }

                $htmlStock = view('components.inv_detail', compact('stock'))->render();

                return response()->json([
                  'error'     => false,
                  'articles'  => $stock,
                  'htmlStock' => $htmlStock,
                  'message'   => 'Articulo(s) asignados o pre-asignados al vendedor.']);
              }
            } else {
              $message = 'El inventario asignado excede el límite que puede recibir el vendedor.';
            }
          }
        }
      }

      return response()->json(['error' => true, 'message' => $message]);
    }
  }

  public function removeStock(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->seller) && !empty($request->msisdn)) {
        $pre='N';
        if(!empty($request->preasignado)){
          if($request->preasignado == 'Y'){
            $pre = 'Y';
          }
        }

        if($pre == 'N'){
          $article = Inventory::getDetail($request->msisdn, ['activeAsigne' => $request->seller]);

          if (!empty($article)) {
            $assigArt = SellerInventory::getAsignmentUser($article->id, session('user'));

            $invs = SellerInventory::getConnect('R')
              ->where('inv_arti_details_id', $article->id)
              ->where('status', 'A')
              ->first();

            SellerInventory::getConnect('W')
              ->where('inv_arti_details_id', $article->id)
              ->where('status', 'A')
              ->update([
                'status' => 'T'
              ]);

            //Descontando deuda para los usuarios con baja en proceso
            $user = User::getUserByEmail($request->seller);
            if(!empty($user) && $user->status == 'D'){
              $userDismissal = LowRequest::getInProcessRequestByUser($request->seller);

              if(!empty($userDismissal)){
                if($article->artic_type == 'H'){
                  $userDismissal->cash_hbb = $userDismissal->cash_hbb - $article->price_pay;
                }
                if($article->artic_type == 'M'){
                  $userDismissal->cash_mifi = $userDismissal->cash_mifi - $article->price_pay;
                }
                if($article->artic_type == 'T'){
                  $userDismissal->cash_telf = $userDismissal->cash_telf - $article->price_pay;
                }
                if($article->artic_type == 'F'){
                  $userDismissal->cash_fibra = $userDismissal->cash_fibra - $article->price_pay;
                }

                $userDismissal->article_request = $userDismissal->article_request - $article->price_pay;
                $userDismissal->cash_total = $userDismissal->cash_total - $article->price_pay;
                $userDismissal->save();
              }
            }

            if(!empty($invs) && !empty($invs->date_orange)){
              $date_reg = Carbon::createFromFormat('Y-m-d H:i:s', $invs->date_orange)->subDays(20)->format('Y-m-d H:i:s');
            }else{
              $date_reg = date("Y-m-d H:i:s");
            }

            if (!empty($assigArt)) {              
              SellerInventory::getConnect('W')
                ->where([
                  ['inv_arti_details_id', $article->id],
                  ['users_email', session('user')]])
                ->update([
                  'date_reg'         => $date_reg,
                  'status'           => 'A',
                  'date_orange'      => !empty($invs) ? $invs->date_orange : null,
                  'last_assignment'  => date('Y-m-d H:i:s'),
                  'last_assigned_by' => session('user')]);
            } else {
              SellerInventory::getConnect('W')
                ->insert([
                  'users_email'         => session('user'),
                  'inv_arti_details_id' => $article->id,
                  'date_reg'            => $date_reg,
                  'status'              => 'A',
                  'date_orange'         => !empty($invs) ? $invs->date_orange : null,
                  'last_assignment'     => date('Y-m-d H:i:s'),
                  'last_assigned_by'    => session('user')]);
            }

            $band = true;
            if(!empty($invs)){
              if($invs->users_email == session('user')){
                $band = false;
              }
            }

            if($band){
              $inventory = Inventory::getConnect('R')->find($article->id);
              SellerInventoryTrack::setInventoryTrack(
                $article->id,
                !empty($invs) ? $invs->users_email : null,
                !empty($invs) ? null : $inventory->warehouses_id,
                session('user'),
                null,
                session('user')
              );
            }

            $mess = 'Artículo retornado al coordinador.';
          }
          else{
            return response()->json(['error' => true, 'message' => 'No se encontro el artículo.']);
          }
        }
        else{

          $seller = $request->seller;

          $article = Inventory::getConnect('R')
              ->select(
                  'islim_inv_arti_details.id',
                  'islim_inv_articles.artic_type',
                  'islim_inv_arti_details.iccid'
              )
              ->join(
                  'islim_inv_articles',
                  'islim_inv_articles.id',
                  'islim_inv_arti_details.inv_article_id'
              )
              ->join(
                      'islim_inv_assignments_temp',
                      function($join) use ($seller){
                          $join->on(
                              'islim_inv_assignments_temp.inv_arti_details_id',
                              'islim_inv_arti_details.id'
                          )
                          ->where('islim_inv_assignments_temp.user_email', $seller)
                          ->where('islim_inv_assignments_temp.status', 'P');
                      }
                   )
              ->where([
                  ['islim_inv_arti_details.status', 'A'],
                  ['islim_inv_arti_details.msisdn', $request->msisdn]
              ])->first();

          if (!empty($article)) {
            SellerInventoryTemp::getConnect('W')
                ->where([
                  ['inv_arti_details_id', $article->id],
                  ['user_email', $seller],
                  ['status', 'P'],
                ])
                ->update([
                  'date_status' => date("Y-m-d H:i:s"),
                  'status' => 'T'
                ]);


            $invs = SellerInventory::getConnect('R')
              ->where('inv_arti_details_id', $article->id)
              ->where('status', 'A')
              ->first();

            if($invs){
              $mess = 'Artículo retornado al coordinador.';
            }
            else{
              $mess = 'Artículo retornado a la bodega.';
            }
          }
          else{
            return response()->json(['error' => true, 'message' => 'No se encontro el artículo.']);
          }
        }

        return response()->json([
          'error'   => false,
          'msisdn'  => $request->msisdn,
          'type'    => $article->artic_type,
          'iccid'   => $article->iccid,
          'message' => $mess
        ]);

      }
      return response()->json(['error' => true, 'message' => 'Faltan datos.']);
    }
  }

  public static function findRelationUsers(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {
        //Buscando usuarios asociados al usuario logueado
        if (session('hierarchy') >= env('HIERARCHY')) {
          $parents = User::getParentsWD(session('user'));
        }
        
        if (!empty($parents) && $parents->count()) {
          $response = User::getUsersByFilter([
            'users'    => $parents->pluck('email'),
            'likeName' => $request->q,
            'me'       => !empty($request->me) ? session('user') : null,
            'status' => (!empty($request->dismissal) && $request->dismissal) ? ['A', 'D'] : 'A'
          ]);
          if ($response->count()) {
            return response()->json(['error' => false, 'users' => $response]);
          }
        }
      }

      return response()->json(['error' => true]);
    }
  }

  public function reception(Request $request, $email = false)
  {
    if (session('user_type') != 'vendor') {
      return view('coordination.reception', compact('email'));
    }

    session()->flash('message_class', 'alert-danger');
    session()->flash('message_error', 'No tiene permisos para acceder.');
    return redirect()->route('dashboard');
  }

  public function receptionNoti(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = AssignedSales::getSalesAssignedByUser(session('user'), 3);

      $dataIns = PayInstallment::getPayNotiBySup(session('user'));

      $numAct = User::getSalesByUser([
        'user'  => session('user'),
        'dateB' => Carbon::now()->startOfDay()->toDateTimeString(),
        'dateE' => Carbon::now()->endOfDay()->toDateTimeString()]);

      return response()->json([
        'success'     => true,
        'msg'         => '',
        'data'        => $data,
        'data_inst'   => $dataIns,
        'activations' => $numAct->count(),
      ]);
    }

    return redirect()->route('dashboard');
  }

  public function receptionList(Request $request, $email = false)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = AssignedSales::getSalesAssignedByUser(session('user'), 0, $email);

      foreach ($data as $sale) {
        $sale->msisdns = AssignedSalesDetail::getDnBySale($sale->id)->pluck('msisdn');
      }

      $dataIns = PayInstallment::getInstallmentReception([
        'user'   => session('user'),
        'seller' => $email ? $email : null,
      ]);

      $dataInstF = [];
      foreach ($dataIns as $sale) {
        if (empty($dataInstF[$sale->id_report])) {
          $dataInstF[$sale->id_report] = [
            'name'        => $sale->name . ' ' . $sale->last_name,
            'email'       => $sale->email,
            'date'        => $sale->date_update,
            'transaction' => $sale->id_report,
          ];
        }

        if (empty($dataInstF[$sale->id_report]['amount'])) {
          $dataInstF[$sale->id_report]['amount'] = 0;
        }

        $dataInstF[$sale->id_report]['amount'] += $sale->amount;
        $dataInstF[$sale->id_report]['msisdns'][] = [
          'dn'    => $sale->msisdn,
          'quote' => $sale->n_quote,
        ];
      }

      $html = view('coordination.listCashDelivery', compact('data', 'dataInstF'))->render();
      return response()->json(['success' => true, 'html' => $html, 'count' => $data->count()]);
    }

    return redirect()->route('dashboard');
  }

  public function receptionStatus(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->status) && !empty($request->id) && !empty($request->type)) {
        if ($request->type == 'N') {
          if ($request->status == 'A') {
            //Descontando deuda para los usuarios con baja en proceso
            $assig = AssignedSales::getAsignedSaleById($request->id);
            if(!empty($assig) && $assig->status == 'V'){
              $user = User::getUserByEmail($assig->users_email);
              if(!empty($user) && $user->status == 'D'){
                $userDismissal = LowRequest::getInProcessRequestByUser($assig->users_email);

                if(!empty($userDismissal)){
                  $userDismissal->cash_request = $userDismissal->cash_request - $assig->amount;
                  $userDismissal->cash_total = $userDismissal->cash_total - $assig->amount;
                  $userDismissal->save();
                }
              }
            }

            AssignedSales::aceptReceptionVU(
              $request->id,
              session('user'),
              [
                'date_accepted' => date('Y-m-d H:i:s'),
                'status'        => 'P'
              ]
            );

            sleep(2);

            $unique = AssignedSales::getSaleDataVU($request->id, session('user'))
              ->pluck('unique_transaction');

            Sale::markAssign($unique);

            return response()->json([
              'success' => true,
              'msg'     => 'Recepción aceptada.',
            ]);
          }

          if ($request->status == 'D') {
            AssignedSales::aceptReceptionVU(
              $request->id,
              session('user'),
              [
                'date_reject' => date('Y-m-d H:i:s'),
                'reason'      => $request->reason,
                'view'        => 'N',
                'status'      => 'I']
            );

            return response()->json([
              'success' => true,
              'msg'     => 'Recepción rechazada.',
            ]);
          }

        } elseif ($request->type == 'I') {
          $reports = PayInstallment::getListReception($request->id, session('user'));

          $date = date('Y-m-d H:i:s');
          foreach ($reports as $sale) {
            //Descontando deuda para los usuarios con baja en proceso
            $user = User::getUserByEmail($sale->seller);
            if(!empty($user) && $user->status == 'D'){
              $userDismissal = LowRequest::getInProcessRequestByUser($sale->seller);

              if(!empty($userDismissal)){
                $userDismissal->cash_request = $userDismissal->cash_request - $sale->amount;
                $userDismissal->cash_abonos = $userDismissal->cash_abonos - $sale->amount;
                $userDismissal->cash_total = $userDismissal->cash_total - $sale->amount;
                $userDismissal->save();
              }
            }

            PayInstallment::updateRecptionStatus($sale->id, [
              'status'      => $request->status == 'A' ? 'C' : 'R',
              'date_update' => $date,
              'date_acept'  => $request->status == 'A' ? $date : null,
              'reason'      => $request->status == 'A' ? null : $request->reason,
              'view'        => $request->status == 'A' ? 'Y' : 'N']);

            SaleInstallmentDetail::updateRecptionStatus($sale->sale_installment_detail, [
              'conciliation_status' => $request->status == 'A' ? 'C' : 'CV',
              'date_update'         => $date]);

            if ($request->status == 'A') {
              Sale::markAssign([$sale->unique_transaction]);
            }
          }

          return response()->json([
            'success' => true,
            'msg'     => $request->status == 'A' ? 'Recepción aceptada.' : 'Recepción rechazada.',
          ]);
        }

        return response()->json([
          'success' => false,
          'msg'     => 'No se pudo actualizar el estatus de la recepción de efectivo.',
        ]);
      } else {
        return response()->json([
          'success' => false,
          'msg'     => 'No se pudo actualizar el estatus de la recepción de efectivo.',
        ]);
      }
    }

    return redirect()->route('dashboard');
  }

  public function reportUnConcSales(Request $request)
  {
    return view('coordination.unConcSales');
  }

  public function getReportUnConcSales(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $parents = User::getParentsOneLevel(session('user'));

      $dateB = null;
      if (!empty($request->dateb)) {
        $dateB = str_replace(' ', '', $request->dateb);
        $db    = !empty($request->dateb) ? Carbon::createFromFormat('d-m-Y', $dateB)->startOfDay()->toDateTimeString() : '';
      }

      $dateE = null;
      if (!empty($request->datee)) {
        $dateE = str_replace(' ', '', $request->datee);
        $de    = !empty($request->datee) ? Carbon::createFromFormat('d-m-Y', $dateE)->endOfDay()->toDateTimeString() : '';
      }

      $seller_email = !empty($request->seller_email) ? $request->seller_email : '';

      $data = User::getSalesNotConcReport([
        'seller' => $seller_email,
        'dateB'  => !empty($db) ? $db : null,
        'dateE'  => !empty($de) ? $de : null,
        'user'   => session('user'),
        'parent' => (!empty($parents) && $parents->count()) ? $parents->pluck('email') : null,
      ]);

      //Paginado
      $total = $data->count();

      $pos = 0;
      if (!empty($request->action)) {
        if ($request->action === 'prev') {
          $pos = $request->skip - 5;
        }

        if ($request->action === 'next') {
          $pos = $request->skip + 5;
        }

      }

      $skip = ($request->skip === null) ? 0 : $pos;

      $data = $data->skip($skip)
        ->take(5);

      //Ejecutando consulta
      $data = $data->get();

      $html = view(
        'coordination.listUnconcSales',
        compact(
          'data',
          'seller_email',
          'dateB',
          'dateE',
          'skip',
          'total'
        )
      )->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function reportActivations(Request $request)
  {
    return view('coordination.activationsReport');
  }

  public function getReportActivarions(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (session('hierarchy') >= env('HIERARCHY')) {
        $parents = User::getParents(session('user'));
      }

      $dateB = null;
      if (!empty($request->dateb)) {
        $dateB = str_replace(' ', '', $request->dateb);
        $db    = !empty($request->dateb) ? Carbon::createFromFormat('d-m-Y', $dateB)->startOfDay()->toDateTimeString() : '';
      }

      $dateE = null;
      if (!empty($request->datee)) {
        $dateE = str_replace(' ', '', $request->datee);
        $de    = !empty($request->datee) ? Carbon::createFromFormat('d-m-Y', $dateE)->endOfDay()->toDateTimeString() : '';
      }

      $seller_email = !empty($request->seller_email) ? $request->seller_email : '';

      $data = User::getSalesReport([
        'seller' => $seller_email,
        'dateB'  => !empty($db) ? $db : null,
        'dateE'  => !empty($de) ? $de : null,
        'user'   => session('user'),
        'parent' => (!empty($parents) && $parents->count()) ? $parents->pluck('email') : null,
      ]);

      //Paginado
      $total = $data->count();

      $pos = 0;
      if (!empty($request->action)) {
        if ($request->action === 'prev') {
          $pos = $request->skip - 5;
        }

        if ($request->action === 'next') {
          $pos = $request->skip + 5;
        }

      }

      $skip = ($request->skip === null) ? 0 : $pos;

      $data = $data->skip($skip)
        ->take(5);

      //Ejecutando consulta
      $data = $data->get();

      $html = view(
        'coordination.listActivation',
        compact(
          'data',
          'seller_email',
          'dateB',
          'dateE',
          'skip',
          'total'
        )
      )->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function reportConcilations()
  {
    return view('coordination.conciliationsReport');
  }

  public function reportgetConcilations(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = BankDeposits::getConcilations([
        'dateB' => !empty($request->dateb) ? Carbon::createFromFormat('d-m-Y', $request->dateb)->startOfDay()->toDateTimeString() : '',
        'dateE' => !empty($request->datee) ? Carbon::createFromFormat('d-m-Y', $request->datee)->endOfDay()->toDateTimeString() : '',
        'user'  => session('user')]);

      $data = $data->orderBy('islim_bank_deposits.date_process', 'DESC')
        ->limit(5)
        ->get();

      $html = view(
        'coordination.listConcilations',
        compact(
          'data'
        )
      )->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function downloadReportConc(Request $request)
  {
    if ($request->isMethod('post')) {
      $data = BankDeposits::getConcilations([
        'dateB' => !empty($request->dateb) ? Carbon::createFromFormat('d-m-Y', $request->dateb)->startOfDay()->toDateTimeString() : '',
        'dateE' => !empty($request->datee) ? Carbon::createFromFormat('d-m-Y', $request->datee)->endOfDay()->toDateTimeString() : '',
        'user'  => session('user')]);

      $data = $data->orderBy('islim_bank_deposits.date_process', 'DESC')
        ->limit(5)
        ->get();

      $fileName = 'rep_conciliacion_' . date('Ymd');

      $headers = array(
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=" . $fileName . ".csv",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0",
      );

      $columns = array(
        'Deposito',
        'Monto',
        'Banco',
        'Operario',
        'Cod. Deposito',
        'Fecha',
        'Motivo',
      );

      $callback = function () use ($data, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        $pos = 1;
        foreach ($data as $conc) {
          $data = [
            $conc->id,
            '$' . number_format($conc->amount, 2, '.', ','),
            !empty($conc->bank) ? $conc->bank : 'Otro',
            $conc->ope_name . ' ' . $conc->ope_last_name,
            $conc->id_deposit,
            $conc->date_process,
            !empty($conc->reason_deposit) ? $conc->reason_deposit : 'N/A',
          ];

          $pos++;

          fputcsv($file, $data);
        }
        fclose($file);
      };
      return response()->stream($callback, 200, $headers);

    }

    return redirect()->route('dashboard');
  }

  public static function reportDebtStatus(){
    return view('coordination.debtStatusReport');
  }

  public function reportgetDebtStatus(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $data = HistoryDebts::getHistoryDebts([
        'dateB' => !empty($request->dateb) ? Carbon::createFromFormat('d-m-Y', $request->dateb)->startOfDay()->toDateTimeString() : '',
        'dateE' => !empty($request->datee) ? Carbon::createFromFormat('d-m-Y', $request->datee)->endOfDay()->toDateTimeString() : '',
        'user'  => session('user')]);

      $data = $data->orderBy('islim_history_debts.date', 'DESC')
              ->limit(5);

      // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      //           return is_numeric($binding) ? $binding : "'{$binding}'";
      //       })->toArray());
      // return response()->json(['success' => true, 'html' => $query]);

      $data = $data->get();

      $html = view(
        'coordination.listDebtStatus',
        compact(
          'data'
        )
      )->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function reportgetDebtStatusUps(Request $request)
  {
    if($request->isMethod('post') && $request->ajax()){
      if(!empty($request->id) && !empty($request->user_type)){

        $idHist = $request->id;
        $usertype = $request->user_type;

        $upsdetSellers = HistoryDebtUps::getConnect('R')
          ->select('islim_sales.users_email')
          ->join('islim_sales', function ($join){
            $join->on('islim_history_debt_ups_details.id_sales', 'islim_sales.id')
              ->whereIn('islim_sales.type',['P','V']);
          })
          ->where([
            [ 'islim_history_debt_ups_details.status', 'A' ],
            [ 'islim_history_debt_ups_details.id_history_debt', $idHist ]
          ])
          ->distinct()
          ->get();

        if(count($upsdetSellers)){
          $sellers = User::getConnect('R')
               ->select('dni', 'name', 'last_name', 'email')
               ->whereIn('email', $upsdetSellers->pluck('users_email'))
               ->get();

          foreach ($sellers as $seller){
            $seller->sales = Sale::getConnect('R')
              ->select(
                  'islim_sales.id',
                  'islim_sales.unique_transaction',
                  'islim_sales.msisdn',
                  'islim_sales.sale_type',
                  'islim_sales.amount',
                  'islim_sales.date_reg',
                  'islim_packs.title as pack',
                  'islim_inv_articles.title as arti'
              )
              ->join(
                  'islim_packs',
                  'islim_packs.id',
                  'islim_sales.packs_id'
              )
              ->join(
                  'islim_inv_arti_details',
                  'islim_inv_arti_details.id',
                  'islim_sales.inv_arti_details_id'
              )
              ->join(
                  'islim_inv_articles',
                  'islim_inv_articles.id',
                  'islim_inv_arti_details.inv_article_id'
              )
              ->join('islim_history_debt_ups_details', function ($join) use($idHist){
                $join->on('islim_history_debt_ups_details.id_sales', 'islim_sales.id')
                  ->where('islim_history_debt_ups_details.id_history_debt',$idHist);
              })
              ->where([
                  ['islim_sales.users_email', $seller->email],
                  ['islim_sales.amount', '>', 0]
              ])
              ->get();
          }

          $html = view('coordination.detailDebtStatusUps', compact('sellers','usertype'))->render();

          return response()->json(array('success' => true, 'html' => $html));

        }
      }
    }

    return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de las altas.'));

  }

  public function reportgetDebtStatusRec(Request $request)
  {
    if($request->isMethod('post') && $request->ajax()){
      if(!empty($request->id) ){

        $idHist = $request->id;
        //$idHist = 2;

        $recDetSellers = HistoryDebtCash::getConnect('R')
          ->select('islim_asigned_sales.users_email')
          ->join('islim_asigned_sales', function ($join){
            $join->on('islim_history_debt_cash_details.id_asigned_sales', 'islim_asigned_sales.id')
              ->whereNotIn('islim_asigned_sales.status',['I','T']);
          })
          ->where([
            [ 'islim_history_debt_cash_details.status', 'A' ],
            [ 'islim_history_debt_cash_details.id_history_debt', $idHist ],
            [ 'islim_history_debt_cash_details.type', 'R' ],
            [ 'islim_asigned_sales.parent_email', session('user') ]
          ])
          ->distinct()
          ->get();

        if(count($recDetSellers)){
          $sellers = User::getConnect('R')
               ->select('dni', 'name', 'last_name', 'email')
               ->whereIn('email', $recDetSellers->pluck('users_email'))
               ->get();

          foreach ($sellers as $seller){
            $totalAmountAS = 0;
            $seller->asigned_sales = AssignedSales::getConnect('R')
              ->select(
                  'islim_asigned_sales.id',
                  'islim_asigned_sales.amount',
                  'islim_asigned_sales.status',
                  'islim_asigned_sales.date_accepted',
                  'islim_asigned_sales.date_process'
              )
              ->join('islim_history_debt_cash_details', function ($join) use($idHist){
                $join->on('islim_history_debt_cash_details.id_asigned_sales', 'islim_asigned_sales.id')
                  ->where('islim_history_debt_cash_details.id_history_debt',$idHist)
                  ->where('islim_history_debt_cash_details.type', 'R');
              })
              ->where([
                  ['islim_asigned_sales.users_email', $seller->email],
                  ['islim_asigned_sales.amount', '>', 0]
              ])
              ->whereNotIn('islim_asigned_sales.status',['I','T'])
              ->get();

              foreach ($seller->asigned_sales as $asigned_sale){
                if($asigned_sale->status = 'A'){

                  $totalAmountAS += $asigned_sale->amount;

                  $asigned_sale->details = Sale::getConnect('R')
                    ->select(
                        'islim_sales.id',
                        'islim_sales.unique_transaction',
                        'islim_sales.msisdn',
                        'islim_sales.sale_type',
                        'islim_sales.amount',
                        'islim_sales.date_reg',
                        'islim_packs.title as pack',
                        'islim_inv_articles.title as arti'
                    )
                    ->join(
                        'islim_packs',
                        'islim_packs.id',
                        'islim_sales.packs_id'
                    )
                    ->join(
                        'islim_inv_arti_details',
                        'islim_inv_arti_details.id',
                        'islim_sales.inv_arti_details_id'
                    )
                    ->join(
                        'islim_inv_articles',
                        'islim_inv_articles.id',
                        'islim_inv_arti_details.inv_article_id'
                    )
                    ->join('islim_asigned_sale_details', function ($join) use($asigned_sale){
                      $join->on('islim_asigned_sale_details.unique_transaction', 'islim_sales.unique_transaction')
                        ->where('islim_asigned_sale_details.asigned_sale_id',$asigned_sale->id);
                    })
                    ->where([
                        ['islim_sales.users_email', $seller->email],
                        ['islim_sales.amount', '>', 0]
                    ])
                    ->get();
                }
              }

              $seller->amount = $totalAmountAS;
          }

          $html = view('coordination.detailDebtStatusCashRec', compact('sellers'))->render();

          return response()->json(array('success' => true, 'html' => $html));

        }
      }
    }

    return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de recepciones de efectivo.'));

  }

  public function reportgetDebtStatusDel(Request $request)
  {
    if($request->isMethod('post') && $request->ajax()){
      if(!empty($request->id) ){

        $idHist = $request->id;
        //$idHist = 2;

        $asigned_sales = AssignedSales::getConnect('R')
        ->select(
            'islim_asigned_sales.id',
            'islim_asigned_sales.amount',
            'islim_asigned_sales.status',
            'islim_asigned_sales.date_accepted',
            'islim_asigned_sales.date_process'
        )
        ->join('islim_history_debt_cash_details', function ($join) use($idHist){
          $join->on('islim_history_debt_cash_details.id_asigned_sales', 'islim_asigned_sales.id')
            ->where('islim_history_debt_cash_details.id_history_debt',$idHist)
            ->where('islim_history_debt_cash_details.type', 'D');
        })
        ->where([
            ['islim_asigned_sales.users_email', session('user') ],
            ['islim_asigned_sales.amount', '>', 0]
        ])
        ->whereNotIn('islim_asigned_sales.status',['I','T'])
        ->get();

        foreach ($asigned_sales as $asigned_sale){
          if($asigned_sale->status = 'A'){

            $asigned_sale->details = Sale::getConnect('R')
              ->select(
                  'islim_sales.id',
                  'islim_sales.unique_transaction',
                  'islim_sales.msisdn',
                  'islim_sales.sale_type',
                  'islim_sales.amount',
                  'islim_sales.date_reg',
                  'islim_packs.title as pack',
                  'islim_inv_articles.title as arti'
              )
              ->join(
                  'islim_packs',
                  'islim_packs.id',
                  'islim_sales.packs_id'
              )
              ->join(
                  'islim_inv_arti_details',
                  'islim_inv_arti_details.id',
                  'islim_sales.inv_arti_details_id'
              )
              ->join(
                  'islim_inv_articles',
                  'islim_inv_articles.id',
                  'islim_inv_arti_details.inv_article_id'
              )
              ->join('islim_asigned_sale_details', function ($join) use($asigned_sale){
                $join->on('islim_asigned_sale_details.unique_transaction', 'islim_sales.unique_transaction')
                  ->where('islim_asigned_sale_details.asigned_sale_id',$asigned_sale->id);
              })
              ->where([
                  ['islim_sales.users_email', session('user')],
                  ['islim_sales.amount', '>', 0]
              ])
              ->get();
          }
        }

        $html = view('coordination.detailDebtStatusCashDel', compact('asigned_sales'))->render();

        return response()->json(array('success' => true, 'html' => $html));


      }
    }

    return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de entregas de efectivo.'));

  }

  public function reportgetDebtStatusDep(Request $request)
  {
    if($request->isMethod('post') && $request->ajax()){
      if(!empty($request->id) ){

        $idHist = $request->id;
        //$idHist = 2;
        $bankUser = UserDeposit::BankUser(session('user'));

        // return response()->json(['success' => true, 'html' => $bankUser]);

        $deposits = BankDeposits::getConnect('R')
          ->select(
            'islim_bank_deposits.id',
            'islim_bank_deposits.cod_auth',
            'islim_bank_deposits.amount',
            'islim_bank_deposits.date_dep',
            'islim_bank_deposits.date_reg',
            'islim_bank_deposits.status',
            'islim_banks.name'
          )
          ->leftJoin(
            'islim_banks',
            'islim_banks.id',
            'islim_bank_deposits.bank'
          )
          ->join('islim_history_debt_conciliate_details', function($join) use ($idHist){
            $join->on('islim_history_debt_conciliate_details.id_bank_dep','islim_bank_deposits.id')
                  ->where('islim_history_debt_conciliate_details.id_history_debt',$idHist)
                  ->where('islim_history_debt_conciliate_details.status','A');
          })
          ->where([
            ['islim_bank_deposits.email', session('user')],
            ['islim_bank_deposits.status', 'A']
          ]);

          // $query = vsprintf(str_replace('?', '%s', $deposits->toSql()), collect($deposits->getBindings())->map(function ($binding) {
          //       return is_numeric($binding) ? $binding : "'{$binding}'";
          //   })->toArray());

          // return response()->json(['success' => true, 'html' => $query]);

          $deposits = $deposits->get();

          $html = view('coordination.detailDebtStatusConciliations', compact('deposits', 'bankUser'))->render();

          return response()->json(array('success' => true, 'html' => $html));

      }

    }
    return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de depositos conciliados.'));
  }
}
