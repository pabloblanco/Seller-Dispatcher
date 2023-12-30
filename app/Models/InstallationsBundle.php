<?php

namespace App\Models;

use App\Models\Installations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallationsBundle extends Model
{
  use HasFactory;
  protected $table = 'islim_installations_bundle';

  protected $fillable = [
    'id',
    'installations_id',
    'dn_type',
    'msisdn_parent',
    'pack_id',
    'service_id',
    'date_reg',
    'status',
    'inv_article_id',
    'isBandTE',
    'obs',
    'conf_port',
    'info_imei',
    'service_pay',
    'unique_transaction',
    'inv_detail_id'];

  public $timestamps = false;
  protected $casts = [
    'info_imei' => 'array',
    'conf_port' => 'array'];

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
  public static function registerChildrenBundler($data)
  {
    try {
      return self::getConnect('W')->insert($data);
    } catch (Exception $e) {
      $Txmsg = 'Error al insertar el hijo del bundle de instalación (M54). ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return response()->json(['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg]);
    }
  }

/**
 * [getImeiPhoneBundle Se obtiene los hijos del agendamiento de cita]
 * @param  [type] $bundle       [description]
 * @param  [type] $installer_id [description]
 * @return [type]               [description]
 */
  public static function getChildrenBundle($installer_id, $type = false, $filter = "all", $statusCita = ['A'])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_installations_bundle.id AS children_id',
        'islim_installations_bundle.info_imei',
        'islim_installations_bundle.pack_id',
        'islim_installations_bundle.inv_article_id',
        'islim_installations_bundle.service_id',
        'islim_installations_bundle.dn_type')
      ->join('islim_installations',
        'islim_installations.id',
        'islim_installations_bundle.installations_id')
      ->where([
        ['islim_installations_bundle.status', 'A'],
        ['islim_installations_bundle.installations_id', $installer_id]])
      ->whereIn('islim_installations.status', $statusCita);

    if ($type) {
      $data = $data->where('islim_installations_bundle.dn_type', $type);
    }

    if ($filter == "all") {
      return $data->get();
    } else {
      return $data->first();
    }
  }

/**
 * [getChildrenActive Devuelve la lista de que componente el bundle para conocer el status de la activacion]
 * @param  [type] $installer_id [Id de instalacion de fibra]
 * @return [type]               [description]
 */
  public static function getChildrenActive($installer_id)
  {
    $dataChildren = self::getConnect('R')
      ->select(
        'islim_installations_bundle.id',
        'islim_installations_bundle.status',
        'islim_inv_arti_details.msisdn',
        'islim_installations_bundle.dn_type',
        DB::raw('CONCAT("children") AS config'),
        DB::raw('CONCAT(NULL) AS obs_activate')
      )
      ->join('islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_installations_bundle.inv_detail_id')
      ->where([
        ['islim_installations_bundle.status', '!=', 'T'],
        ['islim_installations_bundle.installations_id', $installer_id]])
      ->whereNotNull('islim_installations_bundle.inv_detail_id');

    /*$query = vsprintf(str_replace('?', '%s', $dataChildren->toSql()), collect($dataChildren->getBindings())->map(function ($binding) {
    return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
    Log::alert($query);*/

    $dataMaster = Installations::getConnect('R')
      ->select(
        'islim_installations.id',
        'islim_installations.status',
        DB::raw('IFNULL(islim_installations.msisdn,islim_inv_arti_details.msisdn) as msisdn'),
        DB::raw('CONCAT("F") AS dn_type'),
        DB::raw('CONCAT("master") AS config'),
        'islim_installations.obs_activate'
      )
      ->leftJoin('islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_installations.inv_detail_fiber_id'
      )
      ->where([
        ['islim_installations.id', $installer_id],
      ])
      ->whereIn('islim_installations.status', ['P', 'E', 'EC', 'PA']);

    $data = $dataMaster->union($dataChildren);

    return $data->get();
  }

  public static function CompletedDataBundle($children_id, $msisdn_parent, $inv_detail_id, $dataPort = false, $unique, $objImei = false, $service_pay, $Newstatus, $Obs = false)
  {
    try {
      $upData = array(
        'msisdn_parent' => $msisdn_parent,
        'inv_detail_id' => $inv_detail_id,
        'unique_transaction' => $unique,
        'service_pay' => $service_pay,
        'status' => $Newstatus);

      if ($dataPort) {
        $upData['conf_port'] = (String) json_encode($dataPort);
      }
      if ($objImei) {
        $upData['info_imei'] = (String) json_encode($objImei);
      }
      if ($Obs) {
        $upData['obs'] = $Obs;
      }

      $info = self::getConnect('W')
        ->where('id', $children_id)
        ->update($upData);

      return ['success' => true, 'code' => 'OK', 'msg' => ''];
    } catch (Exception $e) {
      $Txmsg = 'Error al actualizar instalación para cron de activacion bundle (M173). ' . (String) json_encode($e->getMessage());
      Log::error($Txmsg);
      return ['success' => false, 'code' => 'ERR_DB', 'msg' => $Txmsg];
    }
  }

/**
 * [getChildren Obtencion del registro hijo de un bundle]
 * @param  [type] $children_id [description]
 * @return [type]              [description]
 */
  public static function getChildren($children_id)
  {
    return self::getConnect('R')
      ->where('id', $children_id)
      ->first();
  }

/**
 * [updateChildrenForFail En caso de que se cambie el articulo del hijo se pone en espera del cron para altan]
 * @param  [type] $children_id [description]
 * @param  [type] $arti_new    [description]
 * @return [type]              [description]
 */
  public static function updateChildrenForFail($children_id, $arti_new)
  {
    try {
      self::getConnect('W')
        ->where('id', $children_id)
        ->update([
          'inv_detail_id' => $arti_new,
          'status' => 'EC']);
      return array('success' => true, 'msg' => 'OK');

    } catch (Exception $e) {
      $txMsg = 'No se pudo actualizar el articulo del bundle. (M208) ' . (String) json_encode($e->getMessage());
      Log::error($txMsg);
      return array('success' => false, 'msg' => $txMsg);
    }
  }

  /**
   * [getComponent Devuelve un componente el bundle para conocer el status de la activacion]
   * @param  [type] $installer_id [Id de installation bundle]
   * @return [type]               [description]
   */
  public static function getComponent($id)
  {
    $component = self::getConnect('R')
      ->select(
        'islim_installations_bundle.id',
        'islim_installations_bundle.status',
        'islim_inv_arti_details.msisdn',
        'islim_installations_bundle.dn_type',
        DB::raw('CONCAT("children") AS config'),
        DB::raw('CONCAT(NULL) AS obs_activate'),
        'islim_installations_bundle.installations_id as installations_id'
      )
      ->join('islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_installations_bundle.inv_detail_id')
      ->where([
        ['islim_installations_bundle.status', '!=', 'T'],
        ['islim_installations_bundle.id', $id]])
      ->whereNotNull('islim_installations_bundle.inv_detail_id');

    return $component->first();
  }

/**
 * [updateChildrenForFail Cuando un plan es pagado se actualizan los registros de paquetes y servicios]
 * @param  [type] $children_id [description]
 * @param  [type] $arti_new    [description]
 * @return [type]              [description]
 */
  public static function updateChildrenChangerBundle(
    $install_id,
    $type,
    $pack,
    $service
  ) {

    if (!empty($install_id) && !empty($type) &&
      !empty($pack) && !empty($service)) {
      try {
        self::getConnect('W')
          ->where([
            ['installations_id', $install_id],
            ['dn_type', $type],
            ['status', 'A']])
          ->update([
            'pack_id' => $pack,
            'service_id' => $service]);
        return array('success' => true, 'msg' => 'OK');

      } catch (Exception $e) {
        $txMsg = "No se pudo actualizar los datos del servicio y paquete del hijo '" . $type . "' de la instalación bundle '" . $install_id . "'. (M269) " . (String) json_encode($e->getMessage());
        Log::error($txMsg);
        return array('success' => false, 'msg' => $txMsg);
      }
    }
    return array('success' => false, 'msg' => "Faltan datos para actualizar el registro '" . $type . "' del bundle (M274)");
  }

}
