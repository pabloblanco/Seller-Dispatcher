<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ConfigIstallments;
use App\Models\Inventory;
use App\Models\Pack;
use App\Models\PayInstallment;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentDetail;
use App\Models\Service;
use App\Models\TokensInstallments;
use App\Models\User;
use App\Utilities\ProcessRegAlt;
use DateTime;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
  /*
  Retorna usuarios o clientes dado el nombre o el apellido como pista, los resultados retornados estan sujetos a la visibilidad permitida para el usuario logueado.
   */
  public function findClient(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q) && !empty($request->t)) {
        $find = $request->q;

        //Buscando usuarios asociados al usuario logueado
        if (session('hierarchy') >= env('HIERARCHY')) {
          $parents = User::getParents(session('user'));
        }

        //Si quieren consultar un vendedor se valida que el usuario autenticado sea un coordinador o superior
        if ($request->t == 'se' && session('user_type') != 'vendor') {
          //Se consulta los usuarios asociados al coordiador y se incluye el coordinador logueado
          $results = User::getUsersByFilter([
            'users' => !empty($parents) ? $parents->pluck('email') : [],
            'likeName' => $find,
            'platform' => 'vendor']);
        } else {
          //Se consultas los clientes
          $results = Client::getClientNetweyByfilter(
            ['name' => !empty($find) ? $find : null],
            ['islim_clients.dni as email',
              'islim_clients.name',
              'islim_clients.last_name'])
            ->limit(10)
            ->get();
        }

        return response()->json(['error' => false, 'results' => $results]);
      }
    }

    return response()->json(array('error' => true));
  }

  /*
  Retorna solicitudes pendientes, solo pueden ser consutadas por un coordiandor
   */
  public function requests()
  {
    $req = SaleInstallment::getRequestSales(session('user'));

    return view('installments.request', compact('req'));
  }

  /*
  Marca una solicitud como aprobada o rechazada
   */
  public function acceptRequest(Request $request)
  {
    if ($request->isMethod('post') && session('user_type') != 'vendor') {
      $msg = [
        'message_class' => 'alert-danger',
        'message_error' => 'No se puede procesar la solicitud.',
      ];

      if (!empty($request->{'sale-req'})) {
        $rext = SaleInstallment::getSalesRequest($request->{'sale-req'}, session('user'));

        if (!empty($rext)) {
          $st = 'D';
          $msg['message_error'] = 'Solicitud rechazada exitosamente.';
          $msg['message_class'] = 'alert-success';

          //Marcando solicitud como aceptada, para esto se valida la cantidad de tokens disponibles
          if (!empty($request->{'btn-accept'})) {
            $tokens = TokensInstallments::getTokensByUser(session('user'));

            if (!empty($tokens)) {
              //Descontando token por aceptar solicitud
              TokensInstallments::updateToken(
                ($tokens->tokens_available - 1),
                $tokens->id,
                false
              );

              $st = 'A';
              $msg['message_error'] = 'Solicitud aprobada exitosamente.';
            } else {
              $msg['message_error'] = 'Solicitud rechazada por falta de cupos para ventas en abono.';
              $msg['message_class'] = 'alert-error';
            }

          }

          SaleInstallment::updateSale($request->{'sale-req'}, $st);
        }

      }

      session()->flash('message_class', $msg['message_class']);
      session()->flash('message_error', $msg['message_error']);

      return redirect()->route('installments.requests');
    }

    return redirect()->route('dashboard');
  }

  /*
  Vista de pagos en abono pendientes
   */
  public function pendingPay(Request $request, $saleid = false)
  {
    return view('installments.pending_pay', compact('saleid'));

    return redirect()->route('dashboard');
  }

  /*
  Retorna html de los pagos en abono pendientes, basado en los filtros de cliente o usuario, de igual forma se toma en cuenta el tipo de usuario logueado para filtar la informacion que puede ver
   */
  public function getPendingPay(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      //Buscando usuarios asociados al usuario logueado
      if (session('hierarchy') >= env('HIERARCHY')) {
        $parents = User::getParents(session('user'));
        $coordinadores = $parents->filter(function ($value, $key) {
          return $value->platform == 'coordinador';
        });
      }

      $orderSales = [];

      if (!empty($coordinadores) && $coordinadores->count()) {
        $coords = $coordinadores->pluck('email')->push(session('user'))->toArray();
      } elseif (!empty($parents) && $parents->count()) {
        $coords = [session('user')];
      }

      $sales = SaleInstallment::getPendingSalesWf([
        'coord' => !empty($coords) ? $coords : null,
        'seller' => (session('user_type') == 'vendor') ? session('user') : null,
        'detail' => (!empty($request->detail) && $request->detail) ? $request->detail : null,
        'client_dni' => ($request->type == 'cl' && $request->value != 'ALL') ? $request->value : null,
        'client_email' => ($request->type == 'se' && $request->value != 'ALL') ? $request->value : null]);

      //Calculando data restante (cuotas, fecha de vencimiento,...)
      $today = time();
      foreach ($sales as $sale) {
        //Si no se tiene una configuracion consultada o el id la que se tiene es diferente a la asociada a la venta se obtiene de la bd la configuracion
        if (empty($config) || $config->id != $sale->config_id) {
          $config = ConfigIstallments::getConfigById($sale->config_id);
        }

        //Verificando si la fecha limite para pago de la proxima cuota expiro
        $dateSale = strtotime('+ ' . ($config->days_quote * $sale->quotes) . ' days',
          strtotime($sale->date_reg_alt)
        );
        $sale->date_expired = date('d-m-Y', $dateSale);

        //Calculando cuotas restantes
        $sale->quotes_rest = ($config->quotes == ($sale->quotes + 1)) ? 'Última' : ($config->quotes - $sale->quotes);

        /*
        Aqui se puede hacer un ciclo con las cuotas restantes para verificar si hay mas de una vencida y poder informar la cantidad de cuotas vencidas.
         */

        //Calculando monto de la cuota
        $sale->quote_amount = ($sale->amount - $sale->first_pay) / ($config->quotes - 1);

        //Separando resultados por vencidos y no vencidos.
        if ($today > $dateSale) {
          $orderSales['expired'][] = $sale;
        } else {
          $orderSales['uptodate'][] = $sale;
        }

      }

      $html = view('installments.pending_pay_detail', compact('orderSales'))->render();

      return response()->json(['error' => false, 'html' => $html]);
    }

    return redirect()->route('dashboard');
  }

  /*Vista de pagos pendientes para el vendedor*/
  public function pendingPaySeller($saleid = false)
  {
    if (session('user_type') == 'vendor') {
      return view('installments.pending_pay_seller', compact('saleid'));
    }

    return redirect()->route('dashboard');
  }

  /*
  Registra el pago de una cuota
   */
  public function doPay(Request $request)
  {
    if ($request->isMethod('post')) {
      $msg = [
        'message_class' => 'alert-danger',
        'message_error' => 'No se puede procesar el pago.',
      ];

      if (!empty($request->sale)) {
        //Buscando venta
        $sale = SaleInstallment::getActiveSale($request->sale, session('user'));

        if (!empty($sale)) {
          //Buscando configuracion asociada a la venta
          $conf = ConfigIstallments::getConfigById($sale->config_id);

          if (!empty($conf)) {
            $date = date('Y-m-d H:i:s');
            $amount = ($sale->amount - $sale->first_pay) / ($conf->quotes - 1);
            $quote = $sale->quotes + 1;

            $conc = 'CV';
            if (session('user_type') != 'vendor') {
              $conc = 'C';
            }

            SaleInstallmentDetail::getConnect('W')
              ->insert([
                'unique_transaction' => $sale->unique_transaction,
                'amount' => $amount,
                'n_quote' => $quote,
                'conciliation_status' => $conc,
                'date_reg' => $date,
                'date_update' => $date,
                'status' => 'A']);

            $st = 'P';
            if ($quote == $conf->quotes) {
              $st = 'F';

              $tokens = TokensInstallments::getTokensByUser($sale->coordinador);

              //retornando el token siempre y cuando el coordinador no este en el limite de tokens que puede tener disponibles
              if (!empty($tokens)
                && ($tokens->tokens_available + 1) <= $tokens->tokens_assigned) {
                TokensInstallments::updateToken(($tokens->tokens_available + 1), $tokens->id, false);
              }
            }

            SaleInstallment::getConnect('W')
              ->where('id', $sale->id)
              ->update([
                'quotes' => $quote,
                'date_update' => $date,
                'status' => $st,
                'alert_exp' => 'P']);

            $msg = [
              'message_class' => 'alert-success',
              'message_error' => 'Pago registrado con exito.',
            ];
          }
        }
      }

      session()->flash('message_class', $msg['message_class']);
      session()->flash('message_error', $msg['message_error']);

      $route = 'installments.pendingPaySeller';
      if (session('user_type') != 'vendor') {
        $route = 'installments.pendingPay';
      }

      return redirect()->route($route);
    }

    return redirect()->route('dashboard');
  }

  /*
  Verifica cantidad de solicitudes pendientes por aprobacion y solicitudes pendientes por cobro
   */
  public function checkRequest(Request $request)
  {
    if ($request->isMethod('post')) {
      if (session('user_type') == 'vendor') {
        $req = SaleInstallment::getCountSales('A', session('user'), false);
        $pend = SaleInstallment::getCountSales('P', session('user'), false);
      } else {
        $req = SaleInstallment::getCountSales('R', false, session('user'));
        $pend = SaleInstallment::getCountSales('P', false, session('user'));
      }

      return response()->json([
        'success' => true,
        'count' => $req,
        'count_pending_S' => $pend,
      ]);
    }

    return redirect()->route('dashboard');
  }

  /*
  Solicitudes hechas por el vendedor
   */
  public function sellerRequests()
  {
    if (session('user_type') == 'vendor') {
      //Solicitudes aprobadas
      $reqA = SaleInstallment::getSalesforSeller(session('user'), ['status' => 'A']);

      //Solicitudes pendientes
      $reqP = SaleInstallment::getSalesforSeller(session('user'), ['status' => 'R']);

      //Solicitudes Rechazadas
      $reqD = SaleInstallment::getSalesforSeller(
        session('user'),
        [
          'status' => 'D',
          'date' => date('Y-m-d H:i:s', strtotime('- 2 days', time()))]
      );

      return view('installments.request_seller', compact('reqA', 'reqP', 'reqD'));
    }

    return redirect()->route('dashboard');
  }

  /*
  Realiza la activacion de una venta solicitada
   */
  public function finalStep(Request $request)
  {
    if ($request->isMethod('post') && session('user_type') == 'vendor') {
      $msg = [
        'message_class' => 'alert-danger',
        'message_error' => 'No se puede procesar la solicitud.',
      ];

      if (!empty($request->{'sale-req'}) && !empty($request->action)) {
        $saleid = $request->{'sale-req'};

        $sale = SaleInstallment::getAprovedSale($saleid, session('user'));

        if (!empty($sale)) {
          //Si el vendedo quiere cancelar la venta,se procede a marcarla como eliminada y a retornar el token utilizado para la misma
          if ($request->action == 'CANCEL') {
            $tokens = TokensInstallments::getTokensByUser($sale->coordinador);

            //retornando el token siempre y cuando el coordinador no este en el limite de tokens que puede tener disponibles
            if (!empty($tokens)
              && ($tokens->tokens_available + 1) <= $tokens->tokens_assigned) {
              TokensInstallments::updateToken(($tokens->tokens_available + 1), $tokens->id);
            }

            SaleInstallment::denyRequest($saleid);

            $msg['message_error'] = 'Solicitud rechazada exitosamente.';
          } else {
            //Flujo de activacion
            $service = Service::getService($sale->service_id);

            if (!empty($service)) {
              //Validadno que el dn esta disponible para la venta
              $artiDetail = Inventory::getDetail($sale->msisdn);

              if (!empty($artiDetail)) {
                $client = Client::getClientByDNI($sale->client_dni);

                if (!empty($client)) {
                  $plan = Pack::getInfoPack($sale->pack_id, $sale->type_pack);

                  if (!empty($plan)) {
                    //Realizando alta del dn
                    $result = ProcessRegAlt::doProcessRegAlt(
                      'home', /*1*/
                      $sale->msisdn, /*2*/
                      false, /*3*/
                      $sale->lat, /*4*/
                      $sale->lng, /*5*/
                      $service, /*6*/
                      $artiDetail, /*7*/
                      $sale->type_pack, /*8*/
                      $sale->unique_transaction, /*9*/
                      $client, /*10*/
                      $plan, /*11*/
                      false, /*12*/
                      false, /*13*/
                      false, /*14*/
                      false, /*15*/
                      false, /*16*/
                      false/*17*/
                    );

                    if ($result['success']) {
                      $date = date('Y-m-d H:i:s');
                      //Marcando la venta como procesada y asignando la primera cuota pagada
                      SaleInstallment::setFirstQuote($sale->id);

                      SaleInstallmentDetail::getConnect('W')
                        ->insert([
                          'unique_transaction' => $sale->unique_transaction,
                          'amount' => $sale->first_pay,
                          'n_quote' => 1,
                          'conciliation_status' => 'CV',
                          'date_reg' => $date,
                          'date_update' => $date,
                          'status' => 'A']);

                      $msg['message_class'] = 'alert-success';
                      $msg['message_error'] = 'Alta procesada exitosamente.';
                    }
                  }
                }
              }
            }
          }
        }
      }

      session()->flash('message_class', $msg['message_class']);
      session()->flash('message_error', $msg['message_error']);

      return redirect()->route('installments.sellerRequests');
    }

    return redirect()->route('dashboard');
  }

  /*
  Notificar entrega de efectivo a coordinador
   */
  public function payNotification(Request $request)
  {
    if ($request->isMethod('post') && session('user_type') == 'vendor') {
      $salesIds = $request->item;

      if (!empty($salesIds) && count($salesIds)) {
        $saleIns = SaleInstallmentDetail::getSalesDetailSeller(session('user'), $salesIds);

        if ($saleIns->count()) {
          $date = date('Y-m-d H:i:s');
          $report = uniqid() . time();

          foreach ($saleIns as $sale) {
            PayInstallment::getConnect('W')
              ->insert([
                'sale_installment_detail' => $sale->id,
                'amount' => $sale->amount,
                'id_report' => $report,
                'date_reg' => $date,
                'date_update' => $date,
                'alert_orange_send' => 'P',
                'alert_red_send' => 'P',
                'status' => 'V']);

            SaleInstallmentDetail::markDetailSaled($sale->id);
          }

          session()->flash('message_class', 'alert-success');
          session()->flash('message_error', 'Efectivo entregado exitosamente.');
          return redirect()->route('dashboard');
        }
      }

      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'No se proceso el reporte de entrega de efectivo.');
      return redirect()->route('seller.cashDelivery');
    }
    return redirect()->route('dashboard');
  }

  public static function reportsMI($userc = false)
  {
    $users = $userc ? [$userc] : [session('user')];

    //Buscando usuarios asociados al usuario logueado
    if (session('hierarchy') >= env('HIERARCHY')) {
      $parents = User::getParents(session('user'));
      $coordinadores = $parents->filter(function ($value, $key) {
        return $value->platform == 'coordinador';
      });
    }

    if ($userc && (empty($coordinadores) || empty($coordinadores->firstWhere('email', $userc)))) {
      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'No se consiguio el usuario.');
      return redirect()->route('installments.reportsMI');
    }

    if (!empty($coordinadores) && count($coordinadores) && !$userc) {
      $users = $coordinadores->pluck('email');
    }

    $coord = TokensInstallments::getTokenbyUsers(
      $users,
      ['A']
    );

    $histT = TokensInstallments::getTokenbyUsers(
      $users,
      ['F', 'P']
    )->count();

    $histR = TokensInstallments::getTokenbyUsers(
      $users,
      ['F']
    )->count();

    $sales = SaleInstallment::getOpenSales(
      $users,
      ['P']
    );

    $today = time();
    foreach ($sales as $sale) {
      $dateSale = strtotime(
        '+ ' . ($sale->days_quote * $sale->quotes) . ' days',
        strtotime($sale->date_reg_alt)
      );

      //No esta vencido
      if ($today <= $dateSale) {
        $sale->expired = false;
        $sale->pendingAmount = (($sale->amount - $sale->first_pay) / ($sale->cq - 1)) * ($sale->cq - $sale->quotes);
      } else {
        $sale->expired = true;
        $sale->expiredAmount = (($sale->amount - $sale->first_pay) / ($sale->cq - 1)) * ($sale->cq - $sale->quotes);
        $date1 = new DateTime(date('Y-m-d H:i:s', $dateSale));
        $date2 = new DateTime(date('Y-m-d H:i:s', $today));
        $diff = $date1->diff($date2);
        $sale->expDays = $diff->days === 0 ? 1 : $diff->days;
      }
    }

    return view(
      'installments.report_modems_installment',
      compact('coord', 'histT', 'histR', 'sales', 'coordinadores', 'userc')
    );
  }
}
