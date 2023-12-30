<?php
namespace App\Utilities;

use App\Utilities\Common;
use Illuminate\Support\Facades\Log;

/*
Clase que contiene diversos metodos para para conectarse con la api de telmovpay.
 */
class ApiTelmovPay
{

  public static function sendRequest($request = '', $data = [], $typeSend = 'POST')
  {
    $fullUrl = env('URL_API_TELMOVPAY', 'https://t3st.v2.netwey.com.mx/telmov-api/') . $request;
    $response = Common::executeCurl(
      $fullUrl,
      $typeSend,
      [
        'Content-Type: application/json',
        'cache-control: no-cache',
        'Authorization: Bearer ' . env('TOKEN_TELMOVPAY', 'CT5tVXBRa6vBgFpmDSdiZbTJgtNdiMygukhyVKchedYR2q274E6u6MnDDtiEc9UpzgCa5d92gyxySyUydCbedhqz8MLEije8qgd6'),
      ],
      $data,
      [
        // CURLOPT_SSL_VERIFYPEER => false,
        //  CURLOPT_SSL_VERIFYHOST => false,
      ]
    );

    if ($response['success'] && $response['data']) {
      return ['success' => true, 'data' => $response['data']];
    }

    Log::error('Ocurrio un error al consultar el request ' . $request . ' en el API-intermedia TelmovPay: ', $response);

    return [
      'success' => false,
      'msg' => 'No se obtuvo respuesta exitosa del request ' . $request . ' en API-intermedia TelmovPay.',
      'msg-telmov' => (!empty($response['data']->message)) ? $response['data']->message : '',
      'original' => $response,
      'url' => $fullUrl];
  }
}
