<?php

namespace App\Http\Controllers;

use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\ClientNetwey;
use App\Models\Paguitos;
use App\Models\Sale;
use App\Utilities\ApiPaguitos;
use App\Utilities\Common;
use Illuminate\Http\Request;

class PaguitosController extends Controller
{
  public function associatePaguitos()
  {
    return view('paguitos.associate');
  }

  public function verifyPaguitos(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->msisdn)) {
        $sale = Sale::getSaleByDn($request->msisdn);
        //valido que el vendedor que realizo el alta es el mismo que esta haciendo la asociacion
        if (!empty($sale) && $sale->users_email == session('user')) {
          $fin = Paguitos::getActiveFinancingBydn($request->msisdn);
          //valido que solo se asocie DN que aun estan activos en paguitos
          if (!empty($fin)) {
            if ($fin->total_amount == $sale->amount) {
              $client = ClientNetwey::getClientByDN($request->msisdn);

              //Consultamos en paguitos si tenemos un financiamiento para el DN
              $paguitoRequest = ApiPaguitos::queryInit(
                $request,
                $fin->date_reg,
                (!empty($client->phone_home) ? $client->phone_home : false)
              );

              if ($paguitoRequest['success']) {

                $updateData = Paguitos::UpdateFinancingBydn($fin->id, $paguitoRequest['data'][0]);
                $fin        = Paguitos::getActiveFinancingBydn($request->msisdn);
                $html       = view('paguitos.financing', compact('fin', 'client'))->render();

                return response()->json([
                  'error' => false,
                  'data'  => [
                    'isFinancing' => true,
                    'amountPay'   => round(($fin->total_amount - $paguitoRequest['data'][0]->monto_pagado), 2),
                    'html'        => $html,
                  ],
                ]);
              } else {
                return response()->json([
                  'error'   => true,
                  'message' => $paguitoRequest['msg'],
                ]);
              }
            } else {
              return response()->json([
                'error'   => true,
                'message' => 'El monto del financiamiento solicitado no coincide con el monto del plan con el que se vendio el MSISDN.',
              ]);
            }
          } else {
            return response()->json([
              'error'   => true,
              'message' => 'No se consiguio financiamiento registrado con el MSISDN consultado.',
            ]);
          }
        } else {
          return response()->json([
            'error'   => true,
            'message' => 'No se consiguio la venta del MSISDN.',
          ]);
        }
      }
      return response()->json(['error' => true, 'message' => 'Se requiere el msisdn dado de alta para procesar la solicitud.']);
    }
  }

  public function savePaguitos(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->msisdn)) {
        $sale = Sale::getSaleByDn($request->msisdn);
        //valido que solo se asocie DN que aun estan activos en paguitos

        if (!empty($sale) && $sale->users_email == session('user')) {
          //Consultamos en BD si tenemos un financiamiento para el DN

          $fin = Paguitos::getActiveFinancingBydn($request->msisdn);
          if (!empty($fin)) {
            if ($fin->total_amount == $sale->amount) {
              //Asignando financiamiento al cliente
              ClientNetwey::getConnect('W')
                ->where('msisdn', $request->msisdn)
                ->update([
                  'paguitos_id' => $fin->id,
                ]);

              //Marcando financiamiento como procesado
              Paguitos::marckAsProcess($request->msisdn);

              $amount = $fin->initial_amount - Common::getDiscount('PAGUITOS');

              //Actualizando monto que recibio el vendedor en efectivo
              Sale::getConnect('W')
                ->where('id', $sale->id)
                ->update([
                  'amount'     => $amount,
                  'amount_net' => ($amount / env('TAX')),
                ]);

              $detailA = AssignedSalesDetail::getLastDetail($sale->unique_transaction);

              if (!empty($detailA)) {
                AssignedSalesDetail::getConnect('W')
                  ->where('id', $detailA->id)
                  ->update([
                    'amount'      => $amount,
                    'amount_text' => $amount,
                  ]);

                $saleA = AssignedSales::getSale($detailA->asigned_sale_id);

                if (!empty($saleA)) {
                  $newAmount = ($saleA->amount - ($detailA->amount - ($fin->initial_amount - Common::getDiscount('PAGUITOS'))));

                  AssignedSales::getConnect('W')
                    ->where('id', $saleA->id)
                    ->update([
                      'amount'      => $newAmount,
                      'amount_text' => $newAmount,
                    ]);
                }
              }
              return response()->json(['error' => false]);
            }
          }
        }
        return response()->json(['error' => true, 'message' => 'No se pudo procesar la solicitud por favor vuelva a verificar el MSISDN.']);

      }
      return response()->json(['error' => true, 'message' => 'Faltan datos para procesar la solicitud.']);
    }
  }

}
