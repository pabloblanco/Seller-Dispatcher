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
use App\Models\Client;
use App\Models\ConfigIstallments;
use App\Models\Pack;
use App\Models\SaleInstallment;
use App\Models\Service;
use App\Models\TokensInstallments;
use App\Utilities\ProcessRegAlt;
use DateTime;

class InventoryInstallersController extends Controller
{
  public function stock()
  {
    $lock = User::getOnliyUser(session('user'));

    $invHbb  = false;
    $invMbb  = false;
    $invMIFI = false;
    $invFiber = false;

    if (!empty($lock) && $lock->is_locked == 'N') {
      $invHbb = SellerInventory::getArticsAssignData(session('user'), 'H');

      $invMbb = SellerInventory::getArticsAssignData(session('user'), 'T')->sortBy('iccid');

      $invMIFI = SellerInventory::getArticsAssignData(session('user'), 'M');

      $invFiber = SellerInventory::getArticsAssignData(session('user'), 'F');
    }

    return view('inventory.installers.stock', compact('lock', 'invHbb', 'invMbb', 'invMIFI','invFiber'));
  }

  
  public static function findRelationInstallers(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {
        //Buscando usuarios asociados al usuario logueado
        
        //Jerarquia igual/mayor a 5 o nivel 2 = Jefe de instaladores 
        if (session('hierarchy') >= env('HIERARCHY') || session('hierarchy') == 2 ) {
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

  public function findInveInstaller(Request $request)
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

          $stockF = SellerInventory::getArticsAssignData($request->seller, 'F');
          $stockPF = SellerInventoryTemp::getArticsPreAssignData($request->seller, 'F');

          //Uniendo stock en una sola colección
          $stock = Common::joinColection($stockHbb, $stockPHbb);

          $stock = Common::joinColection($stockMbb, $stock);
          $stock = Common::joinColection($stockPMbb, $stock);

          $stock = Common::joinColection($stockMIFI, $stock);
          $stock = Common::joinColection($stockPMIFI, $stock);

          $stock = Common::joinColection($stockF, $stock);
          $stock = Common::joinColection($stockPF, $stock);

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
          $lmf = Policy::getUserPolicy($request->seller, 'LIV-FIB');

          return response()->json([
            'error'         => false,
            'isSalesActive' => ($ventas->count() > 0),
            'isDismissal'   => $seller->status == 'D',
            'isInProcess'   => $isReq,
            'seller'        => $seller,
            'stockHbb'      => $stockHbb->count(),
            'stockMbb'      => $stockMbb->count(),
            'stockMIFI'     => $stockMIFI->count(),
            'stockPF'     => $stockPF->count(),
            'stock'         => $stock,
            'htmlStock'     => $htmlStock,
            'limitInvMbb'   => !empty($lm) ? ((int) $lm->value) : 0,
            'limitInvHbb'   => !empty($lh) ? ((int) $lh->value) : 0,
            'limitInvMIFI'  => !empty($lmi) ? ((int) $lmi->value) : 0,
            'limitInvFiber'  => !empty($lmf) ? ((int) $lmf->value) : 0
          ]);
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

        //Jerarquia igual/mayor a 5 o nivel 2 = Jefe de instaladores
        if (session('hierarchy') >= env('HIERARCHY') || session('hierarchy') == 2 ) {
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

            if ($request->type == 'FIBER') {
              $limitInv = Policy::getUserPolicy($seller, 'LIV-FIB');

              $invAssig = SellerInventory::getArticsAssignData($seller, 'F')->count();
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
                  'message'   => 'Articulo(s) asignados o pre-asignados al instalador.']);
              }
            } else {
              $message = 'El inventario asignado excede el límite que puede recibir el instalador.';
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

            $mess = 'Artículo retornado al jefe de instalación.';
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
              $mess = 'Artículo retornado al jefe de instalación.';
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

}
