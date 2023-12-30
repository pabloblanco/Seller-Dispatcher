<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FiberPaymentForce extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_payment_force';

  protected $fillable = [
    'id',
    'code_url',
    'status',
    'type',
    'date_reg',
    'date_acept',
    'dni_client',
    'pack_id',
    'date_instalation',
    'schedule',
    'url_contract',
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Models\Client_document
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [newUrlQr Genera un nuevo QR para un cliente especifico o retorna el ultimo pendiente por aceptar]
 * @param  [type] $dni              [description]
 * @param  [type] $pack             [description]
 * @param  [type] $date_instalation [description]
 * @param  [type] $schedule         [description]
 * @return [type]                   [description]
 */
  public static function newUrlQr($dni, $pack, $date_instalation, $schedule, $type = "START", $contract_url = '')
  {
    $QrData = self::getConnect('W')
      ->where([
        ['dni_client', $dni],
        ['type', $type],
        ['status', 'C']])
      ->whereNull('date_acept')
      ->orderBy('date_reg', 'DESC')
      ->first();

    if (!empty($QrData)) {
      if ($QrData->pack_id == $pack) {
        //Existe un Qr pendiente por aceptar
        return array('success' => true, 'url' => $QrData->code_url, 'id' => $QrData->id);
      } else {
        $QrData->status = 'T';
        $QrData->save();
      }
    }

    $url = uniqid() . time();
    $data = [
      'code_url' => $url,
      'type' => $type,
      'date_reg' => date('Y-m-d H:i:s'),
      'dni_client' => $dni,
      'pack_id' => $pack,
      'date_instalation' => $date_instalation,
      'schedule' => $schedule,
      'url_contract' => $contract_url,
    ];
    try {
      $id = self::getConnect('W')->insertGetId($data);
      return array('success' => true, 'url' => $url, 'id' => $id);

    } catch (Exception $e) {
      $Txmsg = 'Error al insertar el url del contrato. ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return array('success' => false, 'msg' => $Txmsg);
    }
  }

  /**
   * [getUrlQr Obtiene el url para ver y aceptar los terminos y condiciones del servicio de fibra]
   * @param  [type] $dni  [description]
   * @param  string $type [description]
   * @return [type]       [description]
   */
  public static function getUrlQr($dni, $type = false, $status = false, $idUrl = false)
  {
    $qr = self::getConnect('R')
      ->where([
        ['dni_client', $dni]]);

    if ($type) {
      $qr = $qr->where('type', $type);
    }
    if ($status) {
      $qr = $qr->where('status', $status);
    }
    if ($idUrl) {
      $qr = $qr->where('id', $idUrl);
    }

    return $qr->first();
  }

  public static function generateContract($request)
  {
    $client = Client::getClientByDNI($request->dni);

    $pack = Pack::getActivePackById($request->pack);

    $packprice = PackPrices::getPackPriceByPackId($request->pack);

    $dateIns = Carbon::createFromFormat('Y-m-d', $request->date_instalation)->startOfDay();

    $addressFormated = $request->state . ', ' . $request->city . ', ' . $request->muni . ', ' . $request->colony . ', ' . $request->route . ', ' . $request->numberhouse . '. Referencia: ' . $request->reference;
    $addressFormated = str_replace(' ,', '', $addressFormated);

    $current_date = Carbon::now()->locale('es');

    $city = FiberCity::getCityNameById($request->city_id);

    if (!empty($client) && !empty($pack) && !empty($packprice) && !empty($city)) {

      $data = [
        'client_name' => ucwords($client->name),
        'client_lname' => ucwords($client->last_name),
        'client_phonehome' => $client->phone_home,
        'client_phone' => $client->phone,
        'address' => $addressFormated,
        'date' => $dateIns->format('d-m-Y'),
        'schedule' => $request->schedule,
        'price' => $packprice->total_price,
        'pack' => $pack->title,
        'city' => $city->location,
        'now' => $current_date,
        'doc_type' => $request->typeIdentity,
        'doc_id' => $request->identity,
      ];

      try {

        $pdf = \PDF::loadView('docs.fiber_force_contract', compact('data'));

        $doc_path = 'contracts/' . $request->dni . date('YmdHis') . '.pdf';
        Storage::disk('s3-fiber-contract')->put(
          $doc_path,
          $pdf->download()->getOriginalContent(),
          'public'
        );
        $ruta = (String) Storage::disk('s3-fiber-contract')->url($doc_path);

        return array('success' => true, 'url' => $ruta);
      } catch (Exception $e) {
        $txmsg = 'Error al generar el contrato de adhesión del cliente. ' . (String) json_encode($e->getMessage());
        Log::error($txmsg);
        return array('success' => false, 'msg' => $txmsg);
      }

    } else {
      return array('success' => false, 'msg' => "Fallo la obtención de los datos de la creación del contrato de adhesión");
    }
  }
}
