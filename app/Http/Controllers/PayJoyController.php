<?php

namespace App\Http\Controllers;

use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\ClientNetwey;
use App\Models\Inventory;
use App\Models\Payjoy;
use App\Models\PayJoyLog;
use App\Models\PayJoyPayments;
use App\Models\Product;
use App\Models\Sale;
use App\Utilities\Common;
use Illuminate\Http\Request;

class PayJoyController extends Controller
{
  public static function webhook(Request $request)
  {
    $response = [
      'success' => false,
      'message' => 'No vienen todos los campos necesarios para procesar el request',
    ];

    $log = PayJoyLog::saveLog(
      $request->ip(),
      (String) json_encode($request->header()),
      (String) json_encode($request->all())
    );

    $headers = $request->header();
    $inputs  = $request->all();

    //Habilitar cuando payjoy corrija la firma
    /*if(!empty($headers['x-payjoy-signature'])){
    $hash = hash_hmac('sha256', (String) json_encode($inputs), env('PAYJOY_KEY'));

    print_r(base64_encode($hash)); exit();

    if(base64_encode($hash) == $headers['X-PayJoy-Signature']){

    }else{
    $response['success'] = false;
    $response['message'] = 'La firma '.$headers['X-PayJoy-Signature'].' no es valida';
    }
    }*/

    if (!empty($inputs['type'])) {
      if (strtolower($inputs['type']) == 'finance') {
        if (!empty($inputs['device']) && !empty($inputs['device']['simNumber']) &&
          !empty($inputs['financeOrder']) && !empty($inputs['financeOrder']['financeAmount']) &&
          !empty($inputs['financeOrder']['purchaseAmount']) && !empty($inputs['customer']) &&
          !empty($inputs['customer']['phoneNumber']) && !empty($inputs['customer']['id']) &&
          !empty($inputs['financeOrder']['id']) &&
          (!empty($inputs['financeOrder']['monthlyCost']) || !empty($inputs['financeOrder']['weeklyCost'])) && !empty($inputs['financeOrder']['months'])

        ) {
          $msisdn = Inventory::getDnByiccid($inputs['device']['simNumber'] . 'F');

          if (!empty($msisdn)) {
            $financing = Payjoy::getFinancingBydn($msisdn->msisdn);

            if (empty($financing)) {
              Payjoy::getConnect('W')
                ->insert([
                  'msisdn'        => $msisdn->msisdn,
                  'iccid'         => $inputs['device']['simNumber'] . 'F',
                  'amount'        => $inputs['financeOrder']['financeAmount'],
                  'total_amount'  => $inputs['financeOrder']['purchaseAmount'],
                  'phone_payjoy'  => $inputs['customer']['phoneNumber'],
                  'customer_id'   => $inputs['customer']['id'],
                  'customer_name' => !empty($inputs['customer']['name']) ? $inputs['customer']['name'] : null,
                  'monthly_cost'  => !empty($inputs['financeOrder']['monthlyCost']) ? $inputs['financeOrder']['monthlyCost'] : null,
                  'weekly_cost'   => !empty($inputs['financeOrder']['weeklyCost']) ? $inputs['financeOrder']['weeklyCost'] : null,
                  'finance_id'    => $inputs['financeOrder']['id'],
                  'months'        => $inputs['financeOrder']['months'],
                  'status'        => 'A',
                  'date_reg'      => date('Y-m-d H:i:s')]);

              $response['success'] = true;
              $response['message'] = 'Registro de financiamiento exitoso. ID ' . $inputs['financeOrder']['id'];
            } else {
              $response['success'] = false;
              $response['message'] = 'Ya hay registrado un financiamiento con el mismo ID ' . $inputs['financeOrder']['id'];
            }
          } else {
            $response['success'] = false;
            $response['message'] = 'No se consiguio el msisdn del iccid ' . $inputs['device']['simNumber'];
          }
        }
      } elseif (strtolower($inputs['type']) == 'cash') {
        if (!empty($inputs['financeOrder']) && !empty($inputs['financeOrder']['id']) &&
          !empty($inputs['payment']) && !empty($inputs['payment']['amount'] && $inputs['payment']['id'])
        ) {
          $financing = Payjoy::getActiveFinancingByReference($inputs['financeOrder']['id']);

          if (!empty($financing)) {
            PayJoyPayments::getConnect('W')
              ->insert([
                'id_payjoy'  => $financing->id,
                'payment_id' => $inputs['payment']['id'],
                'amount'     => $inputs['payment']['amount'],
                'date_reg'   => date('Y-m-d H:i:s'),
                'status'     => 'A']);

            $response['success'] = true;
            $response['message'] = 'Pago registrado exitosamente para el financiamiento: ' . $financing->id;
          } else {
            $response['success'] = false;
            $response['message'] = 'No se consiguio el financiamiento ID ' . $inputs['financeOrder']['id'];
          }
        }
      } else {
        $response['success'] = true;
        $response['message'] = 'No se proceso la solicitud tipo: ' . $inputs['type'];
      }
    }

    if (!$response['success']) {
      $log->type = 'E';
    }

    $log->data_out = (String) json_encode($response);
    $log->save();

    return response()->json($response);
  }

  public function verifyPayjoy(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->msisdn)) {
        $sale = Sale::getSaleByDn($request->msisdn);

        if (!empty($sale) && $sale->users_email == session('user')) {
          $fin = Payjoy::getActiveFinancingBydn($request->msisdn);

          if (!empty($fin)) {
            if ($fin->total_amount == $sale->amount) {
              $client = ClientNetwey::getClientByDN($request->msisdn);

              $html = view('payjoy.financing', compact('fin', 'client'))->render();

              return response()->json([
                'error' => false,
                'data'  => [
                  'isFinancing' => true,
                  'amountPay'   => round(($fin->total_amount - $fin->amount), 2),
                  'html'        => $html],
              ]);
            } else {
              return response()->json([
                'error'   => true,
                'message' => 'El monto del financiamiento solicitado no coincide con el monto del plan con el que se vendio el MSISDN.',
              ]);
            }
          } else {
            return response()->json([
              'error'   => true,
              'message' => 'No se consiguio financiamiento registrado con el MSISDN cosultado.',
            ]);
          }
        } else {
          return response()->json([
            'error'   => true,
            'message' => 'No se consiguio la venta.']);
        }
      }

      return response()->json(['error' => true, 'message' => 'Faltan datos para procesar la solicitud.']);
    }
  }

  public function savePayjoy(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->msisdn)) {
        $sale = Sale::getSaleByDn($request->msisdn);

        if (!empty($sale) && $sale->users_email == session('user')) {
          $fin = Payjoy::getActiveFinancingBydn($request->msisdn);

          if (!empty($fin)) {
            if ($fin->total_amount == $sale->amount) {
              //Asignando financiamiento al cliente
              ClientNetwey::getConnect('W')
                ->where('msisdn', $request->msisdn)
                ->update([
                  'payjoy_id' => $fin->id]);

              //Marcando financiamiento como procesado
              $client = ClientNetwey::getClientByDN($request->msisdn);
              Payjoy::marckAsProcess($request->msisdn, $sale->packs_id, $client->clients_dni);

              $amount = ($sale->amount - $fin->amount) - Common::getDiscount('PAYJOY');

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
                    'amount_text' => $amount]);

                $saleA     = AssignedSales::getSale($detailA->asigned_sale_id);
                $newAmount = ($saleA->amount - ($fin->amount - Common::getDiscount('PAYJOY')));
                AssignedSales::getConnect('W')
                  ->where('id', $saleA->id)
                  ->update([
                    'amount'      => $newAmount,
                    'amount_text' => $newAmount]);
              }

              return ['error' => false];
            }
          }
        }

        return response()->json(['error' => true, 'message' => 'No se pudo procesar la solicitud por favor vuelva a verificar el MSISDN.']);
      }

      return response()->json(['error' => true, 'message' => 'Faltan datos para procesar la solicitud.']);
    }
  }

  public function associatePayjoy()
  {
    return view('payjoy.associate');
  }

  public function devices()
  {
    $return   = [];
    $products = Product::getProductsForPayJoy();

    foreach ($products as $product) {
      $hasInv = Inventory::hasInventory($product->id);

      if ($hasInv->count()) {
        $price    = $product->price_pack + $product->price_serv;
        $taxPrice = $price / env('TAX');

        $return[] = [
          'preTaxPrice'  => round($taxPrice, 2),
          'taxPrice'     => round(($price - $taxPrice), 2),
          'fullPrice'    => round($price, 2),
          'model'        => $product->model,
          'manufacturer' => $product->brand,
          'description'  => $product->description];
      }
    }

    return response()->json($return);
  }
}
