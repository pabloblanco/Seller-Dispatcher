<?php
namespace App\Utilities;

use App\Models\PaguitosLogs;
use App\Utilities\Common;
use Illuminate\Support\Facades\Log;

//use App\Models\CoppelLogs;
/*
Clase que contiene diversos métodos para para conectarse con paguitos.
 */
class ApiPaguitos
{

  public static function queryInit($request, $date_sales, $phone = false){
    $res = self::doRequest($request, 'ConsultaEnganche', $date_sales, false, false, false, false);

    if(!$res['success'] && $phone){
      $res = self::doRequest($request, 'ConsultaEnganche', $date_sales, false, false, false, $phone);
    }

    return $res;
  }

  public static function doRequest($request, $doRequest, $date_sales = false, $RangeDate = false, $cve_sucursal = false, $cve_vendedor = false, $phone = false)
  {
    $data['cve_socio'] = env('CVE_SOCIO');
    $valid             = false;

    if ($doRequest == 'Financiamientos') {
      if (is_array($RangeDate)) {
        if (!empty($RangeDate['dateStar']) && !empty($RangeDate['dateEnd'])) {
          $data['fecha_inicio'] = date("Y-m-d", strtotime($RangeDate['dateStar']));
          $data['fecha_fin']    = date("Y-m-d", strtotime($RangeDate['dateEnd']));
        } else {
          return ['success' => false, 'msg' => 'Se requiere el rango de fecha a consultar'];
        }
      }
      if ($cve_sucursal) {
        $data['cve_sucursal'] = $cve_sucursal;
      }
      if ($cve_vendedor) {
        $data['cve_vendedor'] = $cve_vendedor;
      }
      $valid = true;
    } elseif ($doRequest == 'ConsultaEnganche') {
      if (!empty($request->msisdn) && !empty($date_sales)) {
        $valid               = true;
        $data['folio']       = ($phone ? $phone : trim($request->msisdn));
        $data['fecha_venta'] = date("Y-m-d", strtotime($date_sales));
      } else {
        return ['success' => false, 'msg' => 'Se requiere el DN y la fecha de la venta'];
      }
    }

    if ($valid) {

      $headerRequest = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(env('USER_PAGUITOS') . ':' . env('PASS_PAGUITOS')),
        'cache-control: no-cache',
        'Connection: keep-alive'];

      $dateInitial = microtime(true);
      $response    = Common::executeCurl(
        env('URL_PAGUITOS') . $doRequest,
        'POST',
        $headerRequest,
        $data
      );
      $dateEnd = microtime(true);

      if ($response['success'] && $response['data']->consulta_exitosa) {

        PaguitosLogs::saveLog(
          $request->ip(),
          (String) json_encode($headerRequest),
          (String) json_encode($data),
          (String) json_encode($response),
          $doRequest,
          $dateEnd - $dateInitial,
          'OK'
        );
        return ['success' => true, 'data' => $response['data']->enganche];
      } else {

        PaguitosLogs::saveLog(
          $request->ip(),
          (String) json_encode($headerRequest),
          (String) json_encode($data),
          (String) json_encode($response),
          $doRequest,
          $dateEnd - $dateInitial,
          'FAIL'
        );
        if (isset($response['data']->mensaje)) {
          $msg = $response['data']->mensaje;
        } else {
          $msg = $response['data'];
        }

        Log::alert('Alerta API Paguitos: ' . $msg . '. Info enviada: ' . (String) json_encode($data));
        return ['success' => false, 'msg' => $msg];
      }
    } else {
      return ['success' => false, 'msg' => 'El request no esta definido'];
    }
  }
}
