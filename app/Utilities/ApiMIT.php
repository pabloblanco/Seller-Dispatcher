<?php
namespace App\Utilities;

use App\Utilities\Common;
use Illuminate\Support\Facades\Log;

/*
Helper de conexion con api intermedia de MIT.
 */
class ApiMIT
{
  public static function sendRequest($request = '', $data = [], $methods = "POST")
  {
    $response = Common::executeCurl(
      env('URL_API_MIT') . $request,
      $methods,
      [
        // "accept: */*",
        // "cache-control: no-cache",
        "Content-Type: application/json",
        // "accept-language: en-US,en;q=0.8",
        "Authorization: Bearer " . env('TOKEN_API_MIT')],
      $data,
      [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true]
    );

    // Log::info("CURL " . (String) json_encode($response));
    // Log::info("METHOD " . (String) $methods);
    // Log::info("URL " . (String) env('URL_API_MIT') . $request);
    // Log::info("DATA " . (String) json_encode($data));

    if ($response['success'] && $response['data']->success && !empty($response['data']->data)) {

      $infomit = $response['data']->data;
      return [
        'success' => true,
        'msg' => $response['data']->message,
        'data' => $infomit];
    }

    Log::alert("Ocurrio un error al consultar el request '" . $request . "' en el API-intermedia MIT: " . (String) json_encode($response));

    $msgMIT = isset($response['data']->message) ? $response['data']->message : "";

    return [
      'success' => false,
      'msg' => "No se obtuvo respuesta exitosa del request '" . $request . "' en API-intermedia MIT.",
      'msg-MIT' => $msgMIT,
    ];
  }

}
