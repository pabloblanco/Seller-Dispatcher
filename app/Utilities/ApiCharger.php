<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common;

class ApiCharger {
	public static function auth(){
		$res = Common::executeCurl(
			env('URL_API_RECHARGE').'/auth',
			'POST',
			[
				'accept: */*',
				'Content-Type: application/json',
				'cache-control: no-cache',
				'accept-language: en-US,en;q=0.8',
				'Authorization: basic '.base64_encode(env('API_KEY_ALTAM'))
			]
		);

		if($res['success'] && $res['data']->status == 'OK'){
			return ['success' => true, 'token' => $res['data']->response->token];
		}else{
			Log::error('Follo al intentar autenticarse en api recargas', $res);
		}

		return [
			'success' => false, 
			'msg' => !empty($res['data']->msg) ? $res['data']->msg : 'Fallo conexion con API de recarga'
		];
	}

	public static function step1($token, $data = []){
		$res = Common::executeCurl(
			env('URL_API_RECHARGE').'/step1',
			'POST',
			[
				'accept: */*',
				'Content-Type: application/json',
				'cache-control: no-cache',
				'accept-language: en-US,en;q=0.8',
				'Authorization: bearer '.$token
			],
			$data
		);

		if($res['success'] && $res['data']->status == 'OK'){
			return [
				'success' => true,
				'transaction' => $res['data']->response->transaction,
				'services' => $res['data']->response->services
			];
		}else{
			Log::error('Fallo paso 1 en API recargas data enviada: '.(String)json_encode($data), $res);
		}

		return [
			'success' => false, 
			'msg' => !empty($res['data']->msg) ? $res['data']->msg : 'Fallo conexion con API de recarga'
		];
	}

	public static function step2Seller($token, $data = []){
		$res = Common::executeCurl(
			env('URL_API_RECHARGE').'/step2',
			'POST',
			[
				'accept: */*',
				'Content-Type: application/json',
				'cache-control: no-cache',
				'accept-language: en-US,en;q=0.8',
				'Authorization: bearer '.$token
			],
			$data
		);

		if($res['success'] && $res['data']->status == 'OK'){
			return [
				'success' => true,
				'transaction' => $res['data']->response->transaction,
				'createdAt' => $res['data']->response->createdAt
			];
		}else{
			Log::error('Follo paso 2 en API recargas data enviada: '.(String)json_encode($data), $res);
		}

		return [
			'success' => false, 
			'msg' => !empty($res['data']->msg) ? $res['data']->msg : 'Fallo conexion con API de recarga'
		];
	}
}