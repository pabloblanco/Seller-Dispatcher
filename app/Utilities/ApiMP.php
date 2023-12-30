<?php
namespace App\Utilities;

use App\Utilities\Common;
use Illuminate\Support\Facades\Log;

/*
Clase que contiene diversos metodos para para conectarse con la api de MP.
NOTA:
Este helper queda deprecado motivado que no se usara Mercado Pago en el seller para subscripciones desde el alta, se reemplazo por MIT
 */
class ApiMP
{
  public static function sendRequest($request = '', $data = [], $methods = "POST")
  {
    $response = Common::executeCurl(
      env('URL_API_MP') . $request,
      $methods,
      [
        "accept: */*",
        "cache-control: no-cache",
        "Content-Type: application/json",
        "accept-language: en-US,en;q=0.8",
        'Authorization: Bearer ' . env('TOKEN_API_MP'),
      ],
      $data,
      [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
      ]
    );

    //Log::info("CURL " . (String) json_encode($response));
    // Log::info("METHOD " . (String) $methods);
    // Log::info("URL " . (String) env('URL_API_MP') . $request);
    // Log::info("DATA " . (String) json_encode($data));

    if ($response['success'] && $response['data']->success) {
      return ['success' => true, 'data' => $response['data']->data];
    }

    Log::alert("Ocurrio un error al consultar el request '" . $request . "' en el API-intermedia MP: " . (String) json_encode($response));

    $msgMP = isset($response['data']->message) ? $response['data']->message : "";

    if (isset($response['data']->data->body->message)) {
      $msgMP = $response['data']->data->body->message;
      /*if (str_contains($anality, 'does not exist')) {
    $msgMP = "La información enviada a Mercado Pago no se encuentra disponible";
    }*/
    } elseif (isset($response['data']->message)) {
      $msgMP = $response['data']->message;
    } elseif (!is_object($response['data']) && is_string($response['data'])) {
      $msgMP = $response['data'];
    } else {
      $msgMP = "";
    }

    return [
      'success' => false,
      'msg' => "No se obtuvo respuesta exitosa del request '" . $request . "' en API-intermedia MP.",
      'msg-MP' => $msgMP,
    ];
  }

}
