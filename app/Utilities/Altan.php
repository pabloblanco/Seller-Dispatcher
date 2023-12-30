<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common;
/*
	Clase que contiene diversos metodos para para conectarse con la api altan.
*/
class Altan{
	/**
   	 * Consulta la servicialidad de sitio dado
   	 * @param String $lat
   	 * @param String $lng
   	 * @param String $address
   	 * @return Array
   	*/
	public static function serviceability($lat = false, $lng = false, $address = '', $isMobile = false){
		if($lng && $lat){
			$send = [
				'apiKey' => env('API_KEY_ALTAM'),
				'lat' => $lat,
				'lng' => $lng
			];

			if($address != ''){
				$send['address'] = $address;
			}

			if($isMobile){
				$send['mobility'] = 'Y';
			}

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'serviceability/',
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$send
						);
						
			if($response['success']){
				if($response['data']->status == 'success'){
					return ['success' => true, 'data' => $response['data']->service];
				}

				return [
					'success' => false, 
					'msg' => 'Fuera de cobertura.', 
					'service' => !empty($response['data']->service) ? $response['data']->service : 'S/R',
					'description' => !empty($response['data']->description) ? $response['data']->description : 'S/R'
				];
			}

			return ['success' => false, 'msg' => 'No se pudo consultar cobertura con Altan.'];
		}

		return ['success' => false, 'msg' => 'No se pudo consultar cobertura.'];
	}

	/**
   	 * Realiza la activación de un dn de internet hogar (DEPRECATED)
   	 * @param Array $data
   	 * @return Array
   	*/
	public static function activation($data = [], $dn = false){
		if(count($data) && $dn){
			$data['apiKey'] = env('API_KEY_ALTAM');

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'activation/'.$dn,
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success'] && $response['data']->status == 'success'){
				return $response['data']->transactionId;
			}else{
				Log::error('Ocurrio un error al intentar dar de alta el DN HBB: '.$dn, $response);
			}
		}

		return false;
	}

	/**
		* Realiza la activación de un dn de internet hogar
		* Este metodo reemplaza al activation, retorna de una mejor forma el error devuelto por altan
		* @param Array $data
		* @return Array
	*/
		public static function activation2($data = [], $dn = false){
		if(count($data) && $dn){
			$data['apiKey'] = env('API_KEY_ALTAM');

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'activation/'.$dn,
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success'] && $response['data']->status == 'success'){
				return ['success' => true, 'order_id' => $response['data']->transactionId];
			}else{
				Log::error('Ocurrio un error al intentar dar de alta el DN HBB: '.$dn, $response);
			}

			$message = 'Falló conexión con Altan.';
			if($response['success'] && !empty($response['data']->description_altan)) {
				$message = $response['data']->description_altan;
			}

			return [
				'success' => false, 
				'message' => $message
			];
		}

		return ['success' => false, 'message' => 'No se pudo ejecutar el request en altan'];
	}

	/**
   	 * Realiza la activación de un dn de Telefonía
   	 * @param Array $data
   	 * @return Array
   	*/
	public static function activationMov($data = [], $dn = false){
		if(count($data) && $dn){
			$data['apiKey'] = env('API_KEY_ALTAM');

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'activation/'.$dn,
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success'] && $response['data']->status == 'success'){
				return $response['data']->transactionId;
			}else{
				Log::error('Ocurrio un error al intentar dar de alta el DN MBB o MIFI: '.$dn, $response);
			}
		}

		return false;
	}

	public static function sendSms($data = []){
		if(is_array($data) && count($data)){
			$response = Common::executeCurl(
							env('URL_SMS'),
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success']) return $response['data'];
		}

		return false;
	}

	/**
   	 * Realiza la activación de un dn de Telefonía
   	 * @param Array $data
   	 * @return Array
   	*/
	public static function validIMEI($imei = false){
		if($imei){
			$data['apiKey'] = env('API_KEY_ALTAM');

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'/imei-status/'.$imei,
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success'] && $response['data']->status == 'success'){
				if(strtoupper($response['data']->deviceFeatures->band28) == 'SI' || strtoupper($response['data']->deviceFeatures->band28) == 'NO'){
					return [
						'success' => true,
						'data' => [
							'homologated' => !empty($response['data']->imei->homologated)?$response['data']->imei->homologated:'S/I',
							'blocked' => !empty($response['data']->imei->blocked)?$response['data']->imei->blocked:'S/I',
							'volteCapable' => !empty($response['data']->deviceFeatures->volteCapable)? strtolower($response['data']->deviceFeatures->volteCapable):'S/I',
							'model' => !empty($response['data']->deviceFeatures->model)?$response['data']->deviceFeatures->model:'S/I',
							'brand' => !empty($response['data']->deviceFeatures->brand)?$response['data']->deviceFeatures->brand:'S/I',
							'band28' => strtoupper($response['data']->deviceFeatures->band28)
						]
					];
				}
			}else{
				Log::error('Ocurrio un error al intentar Validar IMEI: '.$imei, $response);
			}
		}

		return ['success' => false];
	}

	public static function deactive($dn){
		if($dn){
			$data['apiKey'] = env('API_KEY_ALTAM');

			$response = Common::executeCurl(
							env('URL_API_ALTAM').'deactivate/'.$dn,
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache'
							],
							$data
						);

			if($response['success'] && $response['data']->status == 'success'){
				return $response['data']->orderId;
			}else{
				Log::error('Ocurrio un error al intentar dar de baja el DN: '.$dn, $response);
			}
		}

		return false;
	}
}