<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common; 
use App\Models\CoppelLogs;
/*
	Clase que contiene diversos métodos para para conectarse con coppel.
*/
class CoppelPay{
	/**
   	* Ejecuta el primer paso para procesar un pago con coppel
		* @param String $transaction
		* @param Integer $amount
		* @param String $description
   	* @return Array 
   	*/
	public static function buyRequest($transaction, $amount, $description, $signature, $msisdn){
		$data_s = [
			'cpl_business' => env('CPL_BUSINESS'),
			'cpl_transaction' => $transaction,
			'cpl_signature' => $signature,
			'cpl_amount' => $amount,
			'cpl_currency' => 'MXN',
			'cpl_description' => $description,
			'cpl_url_redirect' => env('CPL_REDIRECT')
		];

		$log = new CoppelLogs;
		$log->transaction_code = $transaction;
		$log->msisdn = $msisdn;
		$log->data_out = json_encode($data_s);
		$log->end_point = 'solicitud-compra';
		$log->date_reg = date('Y-m-d H:i:s');
		$log->save();

		$response = Common::executeCurl(
						env('CPL_URL').'solicitud-compra',
						'POST',
						[
							'Content-Type: application/json',
							'User-Agent: Curl/1.0'
						],
						$data_s
					);

		$log->data_in = json_encode($response);
		$log->save();
					
		if($response['success']){
			if(strtolower($response['data']->code) == 'success'){
				$data = json_decode($response['data']->data);

				if(!empty($data->solicitud)){
					return ['success' => true, 'request' => $data->solicitud];
				}
			}

			return [
				'success' => false, 
				'message' => !empty($response['data']->message) ? $response['data']->message : 'No se pudo iniciar el proceso de pago con Coppel'
			];
		}
		   
		return ['success' => false, 'message' => 'No se pudo iniciar el proceso de pago con Coppel'];
	}

	public static function processPayment($blackbox, $token, $request, $transaction, $msisdn, $ip){
		$data_s = [
			'cpl_solicitud' => $request,
			'cpl_iovation' => $blackbox,
			'cpl_ip' => $ip,
			'cpl_token' => $token
		];

		$log = new CoppelLogs;
		$log->transaction_code = $transaction;
		$log->msisdn = $msisdn;
		$log->data_out = json_encode($data_s);
		$log->end_point = 'confirmacion-pago';
		$log->date_reg = date('Y-m-d H:i:s');
		$log->save();

		$response = Common::executeCurl(
			env('CPL_URL').'confirmacion-pago',
			'POST',
			[
				'Content-Type: application/json',
				'User-Agent: Curl/1.0'
			],
			$data_s
		);

		$log->data_in = json_encode($response);
		$log->save();

		if($response['success']){
			if(strtolower($response['data']->code) == 'success'){
				$data = json_decode($response['data']->data);

				return [
					'success' => true, 
					'data' => [
						'cpl_auth_code' => $data->cpl_auth_code,
						'cpl_auth' => $data->cpl_auth
					]
				];
			}
			
			return [
				'success' => false, 
				'message' => !empty($response['data']->message) ? $response['data']->message : 'No se pudo iniciar el pago con Coppel'
			];
		}
		   
		return ['success' => false, 'message' => 'No se pudo procesar el pago en Coppel'];
	}

	public static function getSignature($transaction, $amount){
		return md5(env('CPL_KEY').'#'.env('CPL_BUSINESS').'#'.$transaction.'#'.$amount);
	}
}