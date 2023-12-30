<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common; 
/*
	Clase que contiene diversos métodos para para conectarse con coppel.
*/
class CoppelPay{
	/**
   	* Ejecuta el primer paso para procesar un pago con coppel
		* @param String $transaction
		* @param INTEGER $amount
		* @param String $description
   	* @return Array 
   	*/
	public static function buyRequest($transaction, $amount, $description){
		$response = Common::executeCurl(
						env('CPL_URL'),
						'POST',
						[],
						[
							'cpl_business' => env('CPL_BUSINESS'),
							'cpl_transaction' => $transaction,
							'cpl_signature' => self::getSignature($transaction, $amount),
							'cpl_amount' => $amount,
							'cpl_currency' => 'MXN',
							'cpl_description' => $description,
							'cpl_url_redirect' => env('CPL_REDIRECT')
						]
					);
		
		Log::debug($response);
		/*if($response['success'] && !empty($response['data']->results) && !empty($response['data']->results[0]->geometry)){
			return [
				'success' => true,
				'data' => [
					'lat' => $response['data']->results[0]->geometry->location->lat,
					'lng' => $response['data']->results[0]->geometry->location->lng,
					'address' => !empty($response['data']->results[0]->formatted_address) ? $response['data']->results[0]->formatted_address : null
				]
			];
		}*/
		
		//Log::error('Falló consulta en api google (getDataFromAddress) dirección: '.$address, $response);

		return ['success' => false, 'msg' => 'algo falló'];
	}

	public static function getSignature($transaction, $amount){
		return md5(env('CPL_KEY').'#'.env('CPL_BUSINESS').'#'.$transaction.'#'.$amount);
	}
}