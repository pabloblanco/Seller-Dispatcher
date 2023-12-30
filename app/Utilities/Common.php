<?php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Common
{
  public static function normaliza($cadena = '', $isANS = true)
  {
    $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    $cadena = utf8_decode($cadena);
    $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
    $cadena = strtolower($cadena);
    $cadena = utf8_encode($cadena);
    if ($isANS) {
      $cadena = str_replace(',', '', $cadena);
    }

    return $cadena;
  }

  /**
   *  Ejecuta un curl
   *
   *  @param String url endpoint
   *  @param String type tipo de ejecucion [GET, POST, DELETE, ..]
   *  @param Array header campo opcional, de ser enviado reemplaza la cabecera que se envia en el curl
   *  @return Array
   */
  public static function executeCurl($url = false, $type = false, $header = [], $data = [], $opts = [])
  {
    if ($url && $type) {
      $curl = curl_init();

      if (!count($header)) {
        $header = [
          "accept: */*",
          "Content-Type: application/json",
          "cache-control: no-cache",
          "accept-language: en-US,en;q=0.8"];
      }

      $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        //CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $type,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false];

      if (count($opts)) {
        foreach ($opts as $key => $val) {
          $options[$key] = $val;
        }
      }

      if (is_array($data) && count($data)) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      } elseif (!empty($data)) {
        $options[CURLOPT_POSTFIELDS] = $data;
      }

      curl_setopt_array($curl, $options);

      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $err = curl_error($curl);
      // Log::info(' COMMON err  - ' . (String) $err);
      // Log::info(' COMMON httpcode  - ' . (String) $httpcode);
      curl_close($curl);
      //   Log::info((String) $url . ' - ' . (String) $response);
      if ($err) {
        return [
          'success' => false,
          'data' => $err,
          'code' => !empty($httpcode) ? $httpcode : 0];
      } else {
        $dataJson = json_decode($response);

        if (!empty($dataJson)) {
          return [
            'success' => true,
            'data' => $dataJson,
            'original' => $response,
            'code' => !empty($httpcode) ? $httpcode : 0];
        } else {
          return [
            'success' => false,
            'data' => 'No se pudo obtener json.',
            'original' => $response,
            'code' => !empty($httpcode) ? $httpcode : 0];
        }
      }
    }

    return ['success' => false, 'data' => 'Faltan datos.'];
  }

  /**
   *  Optiene los ultimos 3 números como máximo de un texto
   *
   *  @param String wide
   *  @return Int
   */
  public static function getWide($wide)
  {
    $wide = substr($wide, strlen($wide) - 3, strlen($wide));
    if (is_numeric($wide)) {
      return (int) $wide;
    } else {
      $wide = substr($wide, strlen($wide) - 2, strlen($wide));
      if (is_numeric($wide)) {
        return (int) $wide;
      } else {
        $wide = substr($wide, strlen($wide) - 1, strlen($wide));
        if (is_numeric($wide)) {
          return (int) $wide;
        }

      }
    }
    return 0;
  }

  /**
   *  Optiene mapa de rutas asociadas a las politicas de permisologia
   *
   *  @return Array
   */
  public static function getMapRoute()
  {
    return [
      //Ingreso al seller
      'APV-DSE' => [
        'login',
        'dashboard',
        'dashboard.serviciability',
        'getTotalSalesByDate',
        'installments.checkRequest',
        'installments.acceptRequest',
        'installments.sellerRequests',
        'installments.pendingPaySeller',
        'installments.findClient',
        'installments.finalStep',
        'installments.getPendingPay',
        'installments.doPay',
        'installments.payNotification',
        'seller.comparative',
        'testCoppels',
        'test.viewEmail',
        'test.testEmail',
        'findRelationUsers',
        'dashboard.regconex'],

      //Listar prospectos
      'LCL-DSE' => [
        'client.list',
        'client.listAjax',
        'seller.findClient',
        'seller.showClientN'],

      //Registrar prospecto
      'RCL-DSE' => [
        'client.register',
        'client.registerAjax'],

      //crear nueva cita
      'CDV-DSE' => [
        'date.new',
        'call.newschedule',
        'call.listDate'],

      //Lista de citas
      'LCV-DSE' => [
        'client.scheduleList',
        'client.getSchedule'],

      //Altas
      'ACV-DSE' => [
        'seller.index',
        'seller.showPacks',
        'seller.showPackMov',
        //'seller.find',
        'seller.getArticle',
        'seller.processSale',
        //'seller.showClient',
        'seller.validNumberSale',
        'seller.validQtyDns',
        'inventory.listDNOOR',
        'inventory.searchListDN_OR',
        'inventory.downloadInvNoty',
        'seller.validIdentity',
        'seller.checkValidIdentity',
        'inventory.changeStatus',
        'inventory.verifyDnStatus',
        'inventory.chekingRequestStatus'],

      //Altas en abono, para vender en abono se necesita tambien ACV-DSE
      'DSI-DSE' => [],

      //Desbloqueo de produto
      'ARV-DSE' => [
        'seller.onlyProduct',
        'seller.getPackProduct',
        'seller.confirmSaleProduct',
        'seller.doSaleProduct'],

      //Permiso para mostrar mapa en dashboard
      'MAP-DSE' => [],

      //Permiso para mostrar mapa a un vendedor (venta producto)
      'MAP-SEL' => [],

      //Permiso para descargar csv de inventario en el dashboard
      'DCI-DSE' => [
        'dashboard.downloadInv'],

      //Estatus de la linea
      'SMV-DSE' => [
        'seller.statusNumber'],

      //No se esta usando
      'HSV-DSE' => [
        'seller.history'],

      //recargas
      'RSC-DSE' => [
        'charger.index',
        'charger.find',
        //'charger.process',
      ],

      //Edita prospecto
      'EPD-DSE' => [
        'client.edit'],

      //Edita una cita
      'ECV-DSE' => [
        'client.editschedule',
        'call.listDate'],

      //Listado de clientes
      'LCN-DSE' => [
        'client.listClient',
        'client.listClientAjax'],

      //movimiento de inventario
      'A1V-G1V' => [
        'coordination.stock',
        'coordination.findSeller',
        'coordination.findInveSeller',
        'coordination.addStock',
        'coordination.removeStock',
        'findRelationUsers',
        'inventory.preassignedStatus'],

      //Movimiento de inventarion para jefe de instaladores
      'A2V-G2V' => [
        'inventory.installers.stock',
        'findRelationInstallers',
        'inventory.installers.findInveInstaller',
        'inventory.installers.addStock',
        'inventory.installers.removeStock'],
      //Recepcion de dinero
      'RMV-DSE' => [
        'coordination.reception',
        'coordination.findSeller',
        'coordination.getSalesPayoff',
        'coordination.receptionNoti',
        'coordination.receptionList',
        'coordination.receptionStatus'],

      //Editar un cliente
      'ECD-DSE' => [
        'clientNP.edit'],

      //registrar dn de brightstar
      /*'REG-BRS' => [
      'brightstar.registerDn',
      'brightstar.getOrders',
      'brightstar.processOrders',
      ],*/

      //Notificar entrega de efectivo - vendedor
      'NMD-DSE' => [
        'seller.cashDelivery',
        'seller.cashDeliveryDeny'],

      //Reporte de altas para coordinador
      'ACT-DSE' => [
        'coordination.reportActivations',
        'coordination.getReportActivarions',
        'coordination.setPermits',
        'coordination.getReportUnConcSales',
        'coordination.reportUnConcSales'],

      //Reporte de fibra
      'REP-IPS' => [
        'fiber.getFiberPendingReport'],

      //Nomina
      'NOM-DSE' => [
        'Nomina.index',
        'Nomina.getFile',
        'Nomina.getFileContract'],

      //Gestion de ventas en abono
      'SEL-PSI' => [
        'installments.reportsMI',
        'installments.requests',
        'installments.pendingPay'],

      //Reporte de conciliaciones
      'CRV-DSE' => [
        'coordination.reportConcilations',
        'coordination.reportgetConcilations',
        'coordination.downloadReportConc'],

      //Venta movilidad
      'SEL-MOV' => [
        'seller.index',
        'seller.validImei',
        'seller.validQtyDns',
        'payjoy.savePayjoy',
        'payjoy.verifyPayjoy',
        'payjoy.associatePayjoy',
        'inventory.listDNOOR',
        'inventory.searchListDN_OR',
        'inventory.downloadInvNoty',
        'seller.validIdentity',
        'seller.checkValidIdentity',
        'paguitos.associatePaguitos',
        'paguitos.verifyPaguitos',
        'paguitos.savePaguitos',
        'client.getByDn'],

      //Venta de mifi
      'SEL-MIF' => [
        'seller.index',
        'inventory.listDNOOR',
        'inventory.searchListDN_OR',
        'inventory.downloadInvNoty',
        'seller.validIdentity',
        'seller.checkValidIdentity'],

      //Migraciones
      'MIG-DSE' => [
        'seller.migrations',
        'seller.findClientMigration',
        'seller.updateClientM',
        'seller.doMigration'],

      //Venta de fibra
      'SEL-FIB' => [
        'sellerFiber.index',
        'seller.findClient',
        'client.registerAjax',
        'sellerFiber.showClient',
        'sellerFiber.getMapCoverage',
        'sellerFiber.getPlanes',
        'sellerFiber.getPlan',
        'seller.findInstaller',
        'seller.updateClientM',
        'sellerFiber.regInstall',
        'sellerFiber.detailPendingPaidInsModal',
        'sellerFiber.markAsPaidInstall',
        'sellerFiber.getCompAddress',
        'inventory.listDNOOR',
        'inventory.searchListDN_OR',
        'inventory.downloadInvNoty',
        'seller.validIdentity',
        'seller.checkValidIdentity',
        'sellerFiber.payPending',
        'sellerFiber.payPendingAjax',
        'sellerFiber.getCitys',
        'sellerFiber.getOlts',
        'sellerFiber.getNodesRed',
        'sellerFiber.chekingCoverageFiber',
        'sellerFiber.getCoordFromAddress',
        'sellerFiber.getCalendar',
        'sellerFiber.getClock',
        'sellerFiber.getQrForce',
        'sellerFiber.sendMailQr',
        'sellerFiber.verifyQr',
        'seller.validImei',
        'sellerFiber.cantToken',
        'sellerFiber.newToken',
        'sellerFiber.verifyPhone',
        'sellerFiber.requestAutorized',
        'sellerFiber.checkingAutorized',
        'sellerFiber.reSendForceURL'],

      //Instalador de fibra
      'SEL-INF' => [
        'seller.findInstaller',
        'sellerFiber.detailInsModal',
        'sellerFiber.saveDetailInsModal',
        'sellerFiber.deleteInstall',
        'sellerFiber.loadMoredetailInsModal',
        'sellerFiber.doInstall',
        'sellerFiber.getMSISDNSFiber',
        'sellerFiber.doRegister',
        'sellerFiber.getMSISDNGenerate',
        'sellerFiber.chekingMac',
        'sellerFiber.installerSurvey',
        'sellerFiber.doSurvey',
        'sellerFiber.sendMailQr',
        'sellerFiber.verifyQr',
        'sellerFiber.setQrForce',
        'sellerFiber.getPlan',
        'sellerFiber.getPaymentSubscrip',
        'sellerFiber.changer_incash',
        'sellerFiber.cancelQrPayment',
        'sellerFiber.reloadQrPayment',
        'sellerFiber.getMailPayment',
        'sellerFiber.setMailPayment',
        'sellerFiber.setChangerPack',
        'sellerFiber.sendMailQrPayment',
        'sellerFiber.verifyPayment',
        'sellerFiber.getInstallerCharges',
        'sellerFiber.verifyInfoPort',
        'sellerFiber.findInventoryAsigned',
        'sellerFiber.viewProcessFail',
        'sellerFiber.processFail',
        'sellerFiber.refresFail',
        'sellerFiber.refresComponent',
        'sellerFiber.changeMac',
        'sellerFiber.checkingActiveFiber',
      ],

      //Asignar cita a instaladores
      'FIB-AIC' => [
        'sellerFiber.getListInstaller',
      ],

      //cancelar cita de instalacion
      'FIB-SMC' => [
        'sellerFiber.getTypification',
        'sellerFiber.cancelInstalation'],

      //Listado de Guias pendientes
      'LST-GIP' => [
        'inventory.pendingFolios',
        'inventory.boxDetail',
        'inventory.acceptBoxDetail'],

      //Aceptar o rechazar inventario
      'SEL-ARI' => [
        'inventory.preassigned',
        'inventory.rejectPreassignedInv',
        'inventory.acceptPreassignedInv'],

      //Bajas de vendedores
      'SEL-LOW' => [
        'low.new-request',
        'coordination.findInveSeller',
        'low.getSalesUser',
        'low.getDeudaUser',
        'low.regLowUser',
        'low.viewRequestsList',
        'low.getRequestsList'],

      //Permiso Ver estados de Deudas
      'EDC-DSE' => [
        'coordination.reportDebtStatus',
        'coordination.reportgetDebtStatus',
        'coordination.reportgetDebtStatusUps',
        'coordination.reportgetDebtStatusRec',
        'coordination.reportgetDebtStatusDel',
        'coordination.reportgetDebtStatusDep'],

      //Venta de telefonia por telmovPay
      'SEL-TLP' => [
        'seller.chekingIdentiTelmov',
        'seller.listModelSmartPhone',
        //'telmovpay.asociateFinanceTelmov',
        'telmovpay.initTelmov',
        // 'telmovpay.verifyInitTelmov',
        'telmovpay.updateConctactClient',
        'telmovpay.step1InitFinace',
        'telmovpay.chekingMail',
        'telmovpay.cancelTelmov',
        'telmovpay.requestQr',
        'telmovpay.requestQrVerifyLast',
        //'telmovpay.associateCashTelmov',
        'telmovpay.buildPlan',
        'telmovpay.getModels',
        'telmovpay.initContract',
        'telmovpay.endContract',
        'telmovpay.endEnrole',
        'telmovpay.sincronizeApp'],

    ];
  }

  /**
   * Une dos colecciones y recibe una tercera coleccion en caso de que se quiera enviar
   * una coleccion previemente procesada y unir las coleccines enviadas sobre esa
   *
   *  @param Illuminate\Support\Collection addColection1
   *  @param Illuminate\Support\Collection addColection2
   *  @param Illuminate\Support\Collection colection
   *
   *  @return Illuminate\Support\Collection addColection
   */
  public static function joinColection($addColection1, $addColection2, $colection = false)
  {
    if (!$colection) {
      $colection = collect();
    }

    foreach ($addColection1 as $item) {
      $colection->push($item);
    }

    foreach ($addColection2 as $item) {
      $colection->push($item);
    }

    return $colection;
  }

  public static function completedLeftString(int $lenght, $char, $end)
  {
    $lng = strlen((string) $end);

    $ch = '';
    for ($i = 1; $i <= ($lenght - $lng); $i++) {
      $ch .= $char;
    }

    return $ch . $end;
  }

  public static function decodeJWT($token)
  {
    $decode = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));

    return $decode;
  }

  public static function getDiscount($method = null)
  {

    if ($method != null) {

      $result = DB::connection('netwey-r')->table('islim_financing_methods')->where('method', $method)->where('status', 'A')->first();

      if ($result != null) {
        return $result->discount;
      }

      return 0;
    }

    return 0;
  }

/**
 * [getOptionColumn description]
 * @param  [type] $table [nombre de la tabla de BD que deseo usar]
 * @param  [type] $field [Nombre de la columna de tipo enum a listar]
 * @return [type]        [description]
 */
  public static function getOptionColumn($table, $field)
  {
    //$table = 'islim_portability';
    //$field = 'status';

    $test = DB::select(DB::raw("show columns from {$table} where field = '{$field}'"));

    preg_match('/^enum\((.*)\)$/', $test[0]->Type, $matches);
    foreach (explode(',', $matches[1]) as $value) {
      $enum[] = trim($value, "'");
    }
    asort($enum);
    return $enum;
  }

/**
 * [transforTime Retorna la cadena en formato HH:MM:SS de los segundos ingresados]
 * @param  [type] $segundos [tiempo en segundos]
 * @return [type]           [String en formato HH:MM:SS]
 */
  public static function transforTime($segundos)
  {
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos - ($horas * 3600)) / 60);
    $segundos = $segundos - ($horas * 3600) - ($minutos * 60);

    $horas = ($horas < 10) ? '0' . $horas : $horas;
    $minutos = ($minutos < 10) ? '0' . $minutos : $minutos;
    $segundos = ($segundos < 10) ? '0' . $segundos : $segundos;

    return $horas . ':' . $minutos . ":" . $segundos;
  }

  public static function isBase64($cadena)
  {
    if (base64_decode($cadena, true) !== false && !is_numeric($cadena)) {
      return true;
    }
    return false;
  }

  public static function decodificarBase64($cadena)
  {
    if (self::isBase64($cadena)) {
      return base64_decode($cadena);
    }
    return $cadena;
  }

  public static function codificarBase64($cadena)
  {
    if (!self::isBase64($cadena)) {
      return base64_encode($cadena);
    }
    return $cadena;
  }

  /*
  Retorna n digitos aleatorios
  @param Integer $n número de digitos a generar
  @param Integer $b rango inferior para la generacion de números aleatorios
  @param Integer $e rango superior para la generacion de números aleatorios
  @return Integer
   */
  public static function getRandDig($n = 0, $b = 1, $e = 9)
  {
    if ($n > 0) {
      mt_srand(time());
      $digits = '';
      for ($i = 0; $i < $n; $i++) {
        $digits .= mt_rand($b, $e);
      }
      return $digits;
    }
    return 0;
  }

/**
 * [getRandText Genera una cadena de texto aleatoria]
 * @param  integer $limit [tamano de la cadena a generar]
 * @param  string  $type  [tipo de letras a usar]
 * @return [type]         [description]
 */
  public static function getRandText($limit = 1, $type = "upper")
  {
    if ($type == "upper") {
      $caracteres_permitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    } elseif ($type == "lower") {
      $caracteres_permitidos = "abcdefghijklmnopqrstuvwxyz";
    } else {
      //mixto
      $caracteres_permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    $longitud = $limit;
    $prefix = substr(str_shuffle($caracteres_permitidos), 0, $longitud);

    return $prefix;
  }

/**
 * [getTokenConctact retorna una cadena con el formato: ABC123]
 * @param  integer $tam_L  [cantidad de letras]
 * @param  integer $tam_N  [cantidad de numeros]
 * @param  string  $type_L [Tipo de letras]
 * @return [type]          [description]
 */
  public static function getTokenVerify($tam_L = 3, $tam_N = 3, $type_L = 'upper')
  {
    $num = self::getRandDig($tam_N);
    $let = self::getRandText($tam_L, $type_L);
    return $let . $num;
  }

  /*
  function diffSeg($ini,$fin){
  $dateIni =  new DateTime($ini);
  $dateEnd =  new DateTime($fin);
  $diff = $dateIni->diff($dateEnd);
  $hourToSeg = (($diff->h*60)*60);
  $minToSeg = $diff->i*60;
  $seg = $diff->s;
  return $hourToSeg+$minToSeg+$seg;
  }
   */
  /*DEPRECATED*///"accept: */*",
  /*public static function veifyCaptchaGoogle($captcha = false, $ip = false){
if($captcha && $ip){
$data = 'secret=' . env('GOOGLE_CAPTCHA_BACK') . '&response=' . urlencode($captcha) . '&remoteip=' . urlencode($ip);

$res = self::executeCurl(
env('URL_VERIFY_CAPTCHA'),
'POST',
[

"Content-Type: application/x-www-form-urlencoded",
"cache-control: no-cache"
],
$data
);

return ['success' => $res['success'], 'data' => $res['data']];
}

return ['success' => false, 'data' => 'Faltan datos.'];
}*/
}
