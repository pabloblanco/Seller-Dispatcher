<?php

function currentUser($data = '')
{
  $data = session($data);
  return !empty($data) ? $data : '';
}

/**
 * Funcion para devolver hash sin el caracter '='
 * @return String
 * @param $data hash a depurar
 */
function compoundId($data = null)
{
  if (!empty($data)) {
    return str_replace('=', '', $data);
  }
  return '';
}

/*Funcion para devolver fecha formateada dado un timestamp
 * @return String
 * @param $date time stamp
 * @param $format formato en el que se quiere la fecha
 */
function getFormatDate($date = null, $format = null)
{
  if (!empty($date)) {
    if (empty($format)) {
      $format = 'd/m/Y';
    }

    $tmpDate = new DateTime($date);
    return $tmpDate->format($format);
  }
  return false;
}

/*Funcion para validar permisos de usuario por código
 *
 */
function hasPermit($code = false)
{
  if ($code) {
    $codePermits = session('permits_code');
    if (!empty($codePermits)) {
      return in_array($code, $codePermits);
    }
  }
  return false;
}

function showMenu($arr = false)
{
  if ($arr) {
    foreach ($arr as $code) {
      if (hasPermit($code)) {
        return true;
      }

    }
  }
  return false;
}

function getUrlRegister()
{
  if (hasPermit('RCC-C00')) {
    return route('coord.register');
  } elseif (hasPermit('RCC-CA1')) {
    return route('call.register');
  } else {
    return route('client.register');
  }

}

function getUrlNewSchedule()
{
  if (hasPermit('ACC-CA1')) {
    return route('call.newschedule');
  } elseif (hasPermit('RCC-MV3')) {
    return route('client.schedule');
  } elseif (hasPermit('ACV-C00')) {
    return route('coord.newschedule');
  } else {
    return '#';
  }

}

function getRouteMenu($menu = false)
{
  if ($menu) {
    if ($menu == 'register') {
      if (hasPermit('RCL-MV3')) {
        return route('client.register');
      }

      if (hasPermit('RCC-CA1')) {
        return route('call.register');
      }

      if (hasPermit('RCC-C00')) {
        return route('coord.register');
      }

    }
    if ($menu == 'listarClientes') {
      if (hasPermit('LCL-MV3')) {
        return route('client.list');
      }

      if (hasPermit('LCC-CA1')) {
        return route('call.list');
      }

      if (hasPermit('LCC-C00')) {
        return route('coord.list');
      }

    }
    if ($menu == 'listarCitas') {
      if (hasPermit('LCC-MV3')) {
        return route('client.scheduleList');
      }

      if (hasPermit('LSC-CA1')) {
        return route('client.scheduleList');
      }

      if (hasPermit('LCV-C00')) {
        return route('coord.scheduleList');
      }

    }
    return '#';
  }
}

function getMonth($num = false)
{
  if ($num) {
    $months = array(
      1 => 'enero',
      2 => 'febrero',
      3 => 'marzo',
      4 => 'abril',
      5 => 'mayo',
      6 => 'junio',
      7 => 'julio',
      8 => 'agosto',
      9 => 'septiembre',
      10 => 'octubre',
      11 => 'noviembre',
      12 => 'diciembre',
    );
    return !empty($months[$num]) ? $months[$num] : '';
  }
  return '';
}

/*Elimina acentos y la ñ en una cadena de caracteres*/
function removeAccent($cadena = false)
{
  if ($cadena) {
    //Codificamos la cadena en formato utf8 en caso de que nos de errores
    $cadena = utf8_encode($cadena);

    //Ahora reemplazamos las letras
    $cadena = str_replace(
      array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
      array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
      $cadena
    );

    $cadena = str_replace(
      array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
      array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
      $cadena);

    $cadena = str_replace(
      array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
      array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
      $cadena);

    $cadena = str_replace(
      array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
      array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
      $cadena);

    $cadena = str_replace(
      array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
      array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
      $cadena);

    $cadena = str_replace(
      array('ñ', 'Ñ', 'ç', 'Ç'),
      array('n', 'N', 'c', 'C'),
      $cadena
    );
    return $cadena;
  }
  return false;
}

/*Verifica re-captcha de google (DEPRECATED) usar common.php*/
function veifyCaptchaGoogle($data = false)
{
  if ($data) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => env('URL_VERIFY_CAPTCHA'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        "accept: */*",
        "Content-Type: application/x-www-form-urlencoded",
        "cache-control: no-cache",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ['success' => false, 'data' => $err];
    } else {
      return ['success' => true, 'data' => json_decode($response)];
    }

  }

  return ['success' => false, 'data' => 'Faltan datos.'];
}

/*
Metodo para hacer altas, el usuario debe estar logueado para poderlo utilizar
DEPRECATED
 */
function doRegisterAltan($msisdn, $addrs = '', $lat, $lng, $service, $artiDetail, $typeBuyP, $unique, $client, $plan)
{

  $environment = env('APP_ENV', 'local');
  $doRegAltan = env('DO_REG_ALTAN', true);

  $url = env('URL_API_ALTAM') . 'activation/' . $msisdn;

  $data = array(
    'apiKey' => env('API_KEY_ALTAM'),
    'address' => empty($addrs) ? '' : $addrs,
    'lat' => $lat,
    'lng' => $lng,
    'offer' => $service->codeAltan,
  );

  if ($environment != 'local' && $doRegAltan) {
    $response = Curl::to($url)
      ->withData($data)
      ->asJson()
      ->returnResponseObject()
      ->post();
  } else {
    $response = new \stdClass;
    $response->status = 200;
    $response->content = new \stdClass;
    $response->content->status = 'success';
    $response->content->transactionId = '12345';
  }

  if ($response->status == 200) {
    if ($response->content->status == 'success') {
      //Marcando articulos como vendidos
      //if($environment != 'local')
      markArticle($artiDetail->id);

      if ($environment != 'local') {
        try {
          DB::connection('netwey-w')
            ->table('islim_inv_assignments')
            ->where([
              ['inv_arti_details_id', $artiDetail->id],
              ['users_email', '!=', session('user')],
            ])
            ->update(array(
              'status' => 'T',
            ));

        } catch (Exception $e) {
          Log::error('Error al actualizar asignacion de inventario. ' . (String) json_encode($e->getMessage()));
        }

        //Consultando si el articulo vendido ya estaba asignado al vendedor logueado
        $artd = DB::connection('netwey-r')
          ->table('islim_inv_assignments')
          ->select('users_email')
          ->where([
            ['users_email', session('user')],
            ['inv_arti_details_id', $artiDetail->id],
          ])
          ->first();

        //Si exite se marca como vendido
        if (!empty($artd)) {
          try {
            DB::connection('netwey-w')
              ->table('islim_inv_assignments')
              ->where([
                ['inv_arti_details_id', $artiDetail->id],
              ])
              ->update(array(
                'status' => 'P',
              ));

          } catch (Exception $e) {
            Log::error('Error al actualizar asignacion de inventario. ' . (String) json_encode($e->getMessage()));
          }

        } elseif (session('org_type') == 'R') {
          //Si no se le ha asignado el articulo se hace la asignacion y se marca como vendido
          try {
            DB::connection('netwey-w')
              ->table('islim_inv_assignments')
              ->insert([
                'users_email' => session('user'),
                'inv_arti_details_id' => $artiDetail->id,
                'date_reg' => date("Y-m-d H:i:s"),
                'status' => 'P',
                'obs' => 'Auto asignado - Retail',
              ]);
          } catch (Exception $e) {
            Log::error('Error al insertar asignacion de inventario. ' . (String) json_encode($e->getMessage()));
          }

        }
      }

      $point = DB::raw("(GeomFromText('POINT(" . $lat . " " . $lng . ")'))");
      $date = date("Y-m-d H:i:s");

      $periodicity = DB::connection('netwey-r')
        ->table('islim_periodicities')
        ->where('id', $service->periodicity_id)
        ->first();

      if (!empty($periodicity)) {
        $periodicity = $periodicity->periodicity;
      } else {
        $periodicity = '';
      }

      $dataClient = array(
        'msisdn' => $msisdn,
        'clients_dni' => $client->dni,
        'service_id' => $service->id,
        'type_buy' => $typeBuyP,
        'periodicity' => $periodicity,
        'num_dues' => 0,
        'paid_fees' => 0,
        'unique_transaction' => $unique,
        'serviceability' => $service->broadband,
        'lat' => $lat,
        'lng' => $lng,
        'point' => $point,
        'date_buy' => $date,
        'date_reg' => $date,
        'status' => 'A',
      );

      if ($typeBuyP == 'CR') {
        $dataClient['price_remaining'] = $plan->total_amount;
        $dataClient['total_debt'] = $plan->total_amount;
        $dataClient['credit'] = 'A';
      }

      try {
        DB::connection('netwey-w')
          ->table('islim_client_netweys')->insert($dataClient);

      } catch (IntlException $e) {
        Log::error('Error al insertar cliente de netwey. ' . (String) json_encode($e->getMessage()));
      }

      $statusSale = 'E';
      if (session('user_type') == 'coordinador' || session('org_type') == 'R') {
        $statusSale = 'A';
      }

      $amount = $plan->price_pack + $plan->price_serv;

      $dataSale = array(
        'services_id' => $service->id,
        'inv_arti_details_id' => $artiDetail->id,
        'concentrators_id' => 1,
        'api_key' => env('API_KEY_ALTAM'),
        'users_email' => session('user'),
        'packs_id' => $plan->id,
        'unique_transaction' => $unique,
        'codeAltan' => $service->codeAltan,
        'type' => 'V',
        'id_point' => 'VENDOR',
        'description' => 'ARTICULO',
        'amount' => $amount,
        'amount_net' => ($amount / env('TAX')),
        'com_amount' => 0,
        'msisdn' => $msisdn,
        'date_reg' => $date,
        'status' => $statusSale,
      );

      //if($environment != 'local')
      try {
        DB::connection('netwey-w')
          ->table('islim_sales')->insert($dataSale);
      } catch (Exception $e) {
        Log::error('Error al insertar venta de netwey. ' . (String) json_encode($e->getMessage()));
      }

      //En caso de que sea un coordinador el que hace la venta se ejecuta el flujo de recepción de dinero automaticamente
      if (session('user_type') == 'coordinador' || session('org_type') == 'R') {
        $pemail = session('user');

        if (session('org_type') == 'R') {
          $sup = DB::connection('netwey-r')
            ->table('islim_users')
            ->select('parent_email')
            ->where('email', session('user'))
            ->first();

          $pemail = !empty($sup) ? $sup->parent_email : session('user');
        }

        $dataAssigSale = array(
          'parent_email' => $pemail,
          'users_email' => session('user'),
          'amount' => $amount,
          'amount_text' => $amount,
          'date_reg' => $date,
          'date_accepted' => $date,
          'status' => 'P',
        );

        if ($environment != 'local') {
          $idAssig = DB::connection('netwey-w')
            ->table('islim_asigned_sales')->insertGetId($dataAssigSale);

          $dataDetailAssig = array(
            'asigned_sale_id' => $idAssig,
            'amount' => $amount,
            'amount_text' => $amount,
            'unique_transaction' => $unique,
          );

          try {
            DB::connection('netwey-w')
              ->table('islim_asigned_sale_details')->insert($dataDetailAssig);
          } catch (Exception $e) {
            Log::error('Error al insertar detalle de venta de netwey. ' . (String) json_encode($e->getMessage()));
          }

        }
      }

      $amount = 0;

      $dataSale = array(
        'services_id' => $service->id,
        'inv_arti_details_id' => $artiDetail->id,
        'concentrators_id' => 1,
        'api_key' => env('API_KEY_ALTAM'),
        'users_email' => session('user'),
        'packs_id' => $plan->id,
        'order_altan' => $response->content->transactionId,
        'unique_transaction' => $unique,
        'codeAltan' => $service->codeAltan,
        'type' => 'P',
        'id_point' => 'VENDOR',
        'description' => 'ALTA',
        'amount' => $amount,
        'amount_net' => ($amount / env('TAX')),
        'com_amount' => 0,
        'msisdn' => $msisdn,
        'lat' => $lat,
        'lng' => $lng,
        'position' => $point,
        'date_reg' => $date,
        'status' => $statusSale,
      );

      //if($environment != 'local')
      try {
        DB::connection('netwey-w')
          ->table('islim_sales')->insert($dataSale);

      } catch (Exception $e) {
        Log::error('Error al insertar venta de netwey. ' . (String) json_encode($e->getMessage()));
      }

      //envio de sms de Alta
      $data = [
        "msisdn" => $msisdn,
        "service" => $service->title,
        "pack" => $plan->id,
        "concentrator" => 1,
        "type_sms" => "A",
      ];

      if ($environment != 'local' && $doRegAltan) {
        $sendSms = Curl::to(env('URL_SMS'))
          ->withData($data)
          ->asJson()
          ->post();
      }

      return ['error' => false];
    }
  }
  return ['error' => true];
}

function markArticle($fatherId = false)
{
  if ($fatherId) {
    //OJO Quitar comentarios cuando quieran activar cambio de estatus de articulos recursivos
    /*$articles = DB::table('islim_inv_arti_details')
    ->select('id')
    ->where([
    ['id', $fatherId]
    ])
    ->orWhere('parent_id', $fatherId)
    ->get()->pluck('id');

    if(count($articles) > 0){
    $updateartD = DB::table('islim_inv_arti_details')
    ->whereIn('id', $articles)
    ->update(array(
    'status' => 'V'
    ));
    }*/

    try {
      $updateartD = DB::connection('netwey-w')
        ->table('islim_inv_arti_details')
        ->where('id', $fatherId)
        ->update(array(
          'status' => 'V',
        ));
    } catch (Exception $e) {
      Log::error('Error al actualizar el inventario de netwey. ' . (String) json_encode($e->getMessage()));
    }

  }
}

/**
 * [customerSMS Formatea el mensaje de texto con los campos dinamicos de fibra]
 * @param  [type]  $sms           [mensaje configurado en el admin]
 * @param  boolean $dni           [dni del cliente]
 * @param  boolean $name          [nombre del cliente]
 * @param  boolean $msisdn        [msisdn de fibra]
 * @param  boolean $service       [id del servicio instalado]
 * @param  boolean $expireService [fecha que se vence el servicio]
 * @param  boolean $dateInstall   [fecha pautada de instalacion]
 * @return [type]                 [mensaje formateado]
 */
function customerSMS(
  $sms,
  $dni = false,
  $name = false,
  $msisdn = false,
  $service = false,
  $expireService = false,
  $dateInstall = false) {

  if ($dni || $name) {
    //busco el nombre por medio del dni
    if ($dni) {

      $client = DB::connection('netwey-r')
        ->table('islim_clients')
        ->select('name')
        ->where('dni', $dni)
        ->first();
      if (!empty($client)) {
        $name = $client->name;
      } else {
        $name = '';
      }
    }
    if (!empty($name)) {
      $firstName = explode(" ", $name);
      $name = $firstName[0];
    }
    $sms = str_replace('#name#', $name, $sms);
  }
  if ($msisdn) {
    $sms = str_replace('#msisdn#', $msisdn, $sms);
  }
  if ($service) {
    //Con el id del servicio se sabe servicio y vencimiento
    $serviceDat = DB::connection('netwey-r')
      ->table('islim_services')
      ->select('description', 'periodicity_id')
      ->where('id', $service)
      ->first();

    if (!empty($serviceDat)) {
      $serviceDescrip = $serviceDat->description;

      if (!$expireService) {
        $periodoDat = DB::connection('netwey-r')
          ->table('islim_periodicities')
          ->select('days')
          ->where('id', $serviceDat->periodicity_id)
          ->first();

        if (!empty($periodoDat)) {
          $hoy = date('d-m-Y');
          $sum = $periodoDat->days + 1;
          $mod_date = strtotime($hoy . "+ " . $sum . " days");
          $expireService = date('d-m-Y', $mod_date);

        } else {
          $expireService = '';
        }
      }
      $sms = str_replace('#expiration#', $expireService, $sms);
    } else {
      $serviceDescrip = '';
    }
    $sms = str_replace('#service#', $serviceDescrip, $sms);
  }
  if ($dateInstall) {
    $sms = str_replace('#scheduling#', $dateInstall, $sms);
  }
  return $sms;
}
