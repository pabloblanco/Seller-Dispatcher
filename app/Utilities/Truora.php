<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common;

/*
	Clase que contiene diversos métodos para para conectarse con Truora.
*/

class Truora{
  public static function getUrlToRedirect($user_id = ''){
    $data = [
      'url_redirect'
    ];

    if(!empty($user_id)){
      $data['user_id'] = $user_id;
    }

    $res = self::getToken($data);

    if($res['success']){
      return [
        'success' => true, 
        'url' => env('TRUORA_URL').'?token='.$res['token'],
        'account_id' => $res['account_id'],
        'process_id' => $res['process_id']
      ];
    }

    return ['success' => false, 'msg' => $res['msg']];
  }

  /*
  * doc de resultados en truora https://docs.accounts.truora.com/#tag--API-Key,
  * https://developer.truora.com/products/digital-identity/getting_started.html
  */
  public static function getToken($data = []){
    $send = [
      'key_name' => env('TRUORA_KEY_NAME'),
      'key_type' => 'web',
      'grant' => 'digital-identity',
      'api_key_version' => '1',
      'country' => 'MX',
      'redirect_url' => route('seller.redirectTruora'),
      'flow_id' => env('TRUORA_FLOW_ID'),
      'document_type' => 'national-id'
    ];

    if(!empty($data['user_id'])){
      $send['account_id'] = $data['user_id'];
    }

    $response = Common::executeCurl(
      env('TRUORA_API').'api-keys',
      'POST',
      [
        'Truora-API-Key: '.env('TRUORA_KEY'),
        'Content-Type: application/x-www-form-urlencoded'
      ],
      http_build_query($send)
    );

    if($response['success'] && $response['code'] == 200){
      $decode = Common::decodeJWT($response['data']->api_key);

      if(!empty($decode) && !empty($decode->additional_data)){
        $dataAd = json_decode($decode->additional_data);

        if(!empty($dataAd)){
          return [
            'success' => true, 
            'token' => $response['data']->api_key,
            'account_id' => $dataAd->account_id,
            'process_id' => $dataAd->process_id
          ];
        }
      }
    }

    //Log::error('No se pudo obtener token de Truora enviado: ', $send);
    Log::error('No se pudo obtener token de Truora recibido: ', $response);
    return ['success' => false, 'msg' => 'No se pudo obtener el token'];
  }

  /*
  * doc de resultados en truora https://docs.identity.truora.com/#get-/v1/verifications
  */
  public static function processVerification($processId){
    //Array con motivos de fallos en Truora
    $reasonDetailArr = [
      'image_analysis_not_passed' => 'Las imágenes no superaron el proceso de verificación',
      'document_not_recognized' => 'Documento no reconocido',
      'data_inconsistency' => 'Datos inconsistentes',
      'government_database_check_failed' => 'Verificación en base de dato del gobierno fallida',
      'ocr_no_text_detected' => 'No se detecto texto en imagen',
      'empty_input_file' => 'No se envió la imagen',
      'invalid_curp' => 'CURP no válido',
      'missing_text' => 'Texto incompleto',
      'invalid_mrz' => 'MRZ no válido',
      'age_above_limit' => 'Edad por sobre el limite',
      'underage' => 'Menor de edad',
      'invalid_issue_date' => 'Fecha de emisión no válida',
      'national_registrar_inconsistency' => 'Inconsistencia en registro nacional',
      'production_data_inconsistency' => 'Inconsistencia en datos de producción',
      'identity_belongs_to_dead_person' => 'La identidad pertenece a una persona fallecida',
      'face_not_found_in_document' => 'No se encontro el rostro en el documento',
      'max_retries_reached' => 'Número máximo de intentos alcanzados',
    ];

    $response = Common::executeCurl(
      env('TRUORA_API_IDENTITY').'processes/'.$processId.'/result',
      'GET',
      [
        'Truora-API-Key: '.env('TRUORA_KEY')
      ]
    );

    if($response['success'] && $response['code'] == 200){
      if(!empty($response['data']->status)){
        $detail = 'En espera de finalización del proceso de verificación de identidad.';
        $detail_code = strtolower($response['data']->status);

        if(strtolower($response['data']->status) == 'failure'){
          $detail = 'Falló la verificación de identidad.';

          if(!empty($response['data']->failure_status)){
            $detail_code = strtolower($response['data']->failure_status);

            if(strtolower($response['data']->failure_status) == 'expired'){
              $detail = 'La verificación de identidad falló porque finalizo el tiempo permitido para realizar el proceso.';
            }

            if(strtolower($response['data']->failure_status) == 'system_error'){
              $detail = 'La verificación de identidad falló porque finalizo el tiempo permitido para realizar el proceso.';
            }

            if(strtolower($response['data']->failure_status) == 'declined'){
              $detail = 'La verificación de identidad falló porque los datos enviados no superaron el proceso de validación.';
              if(!empty($response['data']->declined_reason)){
                if(!empty($reasonDetailArr[$response['data']->declined_reason])){
                  $detail = 'La verificación de identidad falló porque: '.$reasonDetailArr[$response['data']->declined_reason];
                }
                $detail_code = strtolower($response['data']->declined_reason);
              }
            }
          }
        }

        return [
          'success' => true, 
          'status' => strtolower($response['data']->status),
          'status_detail' => $detail,
          'status_code' => !empty($detail_code) ? $detail_code : '',
          'response' => !empty($response['original']) ? $response['original'] : null
        ];
      }
    }

    Log::error('No se pudo verificar el proceso: ', $response);
    return [
      'success' => false, 
      'msg' => 'No se pudo verificar el proceso',
      'response' => !empty($response['original']) ? $response['original'] : null
    ];
  }
}