<?php
namespace App\Utilities;

use App\Utilities\Common;
use Illuminate\Support\Facades\Log;

/*
Clase que contiene diversos metodos para para conectarse con la api de 815.
 */
class Api815
{
  public static function sendRequest($request = '', $data = [])
  {

    $response = Common::executeCurl(
      env('URL_815') . $request,
      'POST',
      [
        'Content-Type: application/json',
        'cache-control: no-cache',
        'Authorization: Bearer ' . env('TOKEN_815'),
      ],
      $data,
      [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
      ]
    );

    //Log::info($response);

    if ($response['success'] && $response['data']->success) {
      return ['success' => true, 'data' => $response['data']->data];
    }

    Log::alert('Ocurrio un error al consultar el request ' . $request . ' en el API-intermedia 815: ' . (String) json_encode($response));

    $msg815 = '';
    $code = 'FAIL';

    if (!empty($response['data']->data) && isset($response['data']->data->errores)) {
      $errores = $response['data']->data->errores;
      //  Log::info('errores ' . (String) json_encode($errores));
      if (isset($errores->error)) {
        $msg815 = $errores->error;
        $MsjError = '';

        if (isset($errores->detail)) {
          /* EJEMPLO
          {"success":true,"data":{"success":false,"data":{"errores":{"error":"No se pudo registrar el cliente en 815 previamente a la creacion de la conexion. Verifica la informacion del cliente en Netwey","detail":{"success":false,"data":{"errores":{"error":{"errores":{"ciudad":"Escoja una opci\u00f3n v\u00e1lida. Esa opci\u00f3n no est\u00e1 entre las disponibles."}}}}}}}},"original":"{\"success\":false,\"data\":{\"errores\":{\"error\":\"No se pudo registrar el cliente en 815 previamente a la creacion de la conexion. Verifica la informacion del cliente en Netwey\",\"detail\":{\"success\":false,\"data\":{\"errores\":{\"error\":{\"errores\":{\"ciudad\":\"Escoja una opci\\u00f3n v\\u00e1lida. Esa opci\\u00f3n no est\\u00e1 entre las disponibles.\"}}}}}}}}","code":200}
           */
          if (!empty($errores->detail)) {
            if (isset($errores->detail->data->errores->error->errores)) {
              $Detail = $errores->detail->data->errores->error->errores;
              //Log::info('>>>>>>>>>> ' . (String) json_encode($Detail));

              $jsonIterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator(json_decode(json_encode($Detail), true)),
                \RecursiveIteratorIterator::SELF_FIRST);

              foreach ($jsonIterator as $key => $val) {
                if (is_array($val)) {
                  $MsjError .= "$key: <br />";
                } else {
                  $MsjError .= "$key => $val <br />";
                }
              }
            }
          }
        } elseif (str_contains(strtolower($msg815), 'obtener una direccion ip')) {
          $code = "EMPTY_IP";
          $msg815 = "Es posible que el nodo de red de la zona no cuente con direcciones IP disponibles. Contacta a tu supervisor para habilitar nuevas direcciones IP";
        } else {
          $code = "FAIL_ERROR";
        }

        $msjSufix = "";
        if (!empty($MsjError)) {
          $msjSufix = " > Detalles: " . $MsjError;
        }
        $msg815 .= $msjSufix;

      } elseif (isset($errores->general)) {
        $msg815 = $errores->general . ' Puedes intentar de nuevo en unos instantes';
        $code = "FAIL_GENERAL";
      } elseif (isset($errores->direccion_mac)) {
        $msg815 = $errores->direccion_mac;
        $code = "FAIL_MAC";
      } elseif (isset($errores->nombre)) {
        $msg815 = $errores->nombre;
        $code = "FAIL_NAME";
      }
    }

    return [
      'success' => false,
      'msg' => 'No se obtuvo respuesta exitosa del request ' . $request . ' en API-intermedia 815.',
      'msg-815' => $msg815,
      'code' => $code];
  }
/*
public static function getCities()
{
$res  = self::sendRequest('get-citys');
$data = [];

if ($res['success']) {
if (!empty($res['data']->eightFifteen) && !empty($res['data']->eightFifteen->object)) {
foreach ($res['data']->eightFifteen->object as $val) {
$name = '';
if (!empty($val->field)) {
foreach ($val->field as $field) {
if (!empty($field->attributes)) {
if ($field->attributes->name == 'nombre') {
$name = !empty($field->value) ? $field->value : '';
}
}
}
}

$pk = '';
if (!empty($val->attributes)) {
$pk = !empty($val->attributes->pk) ? $val->attributes->pk : '';
}

$data[] = ['pk' => $pk, 'name' => $name];
}
}
}

return $data;
}
 */
  public static function doRegistration($data = [])
  {
    $res = self::sendRequest('conections-new', $data);

    if ($res['success']) {
      return ['success' => true];
    }

    return ['success' => false, 'msg' => (!empty($res['msg-815'])) ? $res['msg-815'] : $res['msg'], 'code' => $res['code']];
  }

  public static function statusFirewallBD($fiber_zone)
  {
    $datain = array('fiber_zone' => $fiber_zone);

    $res = self::sendRequest('status-bd', $datain);

    if ($res['success']) {
      return ['success' => true];
    }

    return ['success' => false, 'msg' => (!empty($res['msg-815']) ? $res['msg-815'] : $res['msg'])];
  }

  public static function getArticle($fiber_zone, $pkArt)
  {
    $datain = array(
      'fiber_zone' => $fiber_zone,
      'pk' => $pkArt,
    );

    $res = self::sendRequest('get-equipos', $datain);

    if ($res['success']) {
      if (!empty($res['data']->eightFifteen) && !empty($res['data']->eightFifteen->object)) {
        foreach ($res['data']->eightFifteen->object as $val) {
          $nameArt = '';
          if (!empty($val->field)) {
            $nameArt = !empty($val->field->value) ? $val->field->value : '';
            return array('success' => true, 'data' => $nameArt);
          }
        }
      }
    }

    if (!empty($res['msg'])) {
      $msg = $res['msg'];
    } else {
      $msg = 'Ocurrio un error obteniendo articulos de la zona';
    }

    return array('success' => false, 'data' => $msg);
  }

  public static function verifyEndPointFiberZone($url)
  {
    if (empty($url)) {
      return false;
    }
    stream_context_set_default([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ],
    ]);
    $array = @get_headers($url);
    if (is_array($array)) {
      $string = $array[0];
      $accepted_response = array('200', '301', '302');
      foreach ($accepted_response as $key => $value) {
        if (strpos($string, $value)) {
          return true;
        }
      }
    }
    return false;
  }

  public static function verifyEndPointCredencial($fiberZone_id)
  {
    $datain = array('fiber_zone' => $fiberZone_id);
    $credencial = self::sendRequest('autenticate', $datain);

    if ($credencial['success']) {
      if (isset($credencial['data']->eightFifteen->token)) {
        return true;
      }
    }
    return false;
  }

  public static function provisioning($msisdn)
  {
    $data = array('msisdn' => $msisdn);

    $res = self::sendRequest('set-provisioning', $data);

    if ($res['success']) {
      return ['success' => true];
    }

    return ['success' => false, 'msg' => (!empty($res['msg-815']) ? $res['msg-815'] : $res['msg'])];
  }
}
