<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common;
/*
	Clase que contiene diversos metodos para para conectarse con la api de clientes.
*/
class APIClient{
	/**
   	 * Consulta el estatus de la linea
   	 * @param String $msisdn
   	 * @return Array 
   	*/
	public static function getClient($msisdn = false){
		if($msisdn){
			$response = Common::executeCurl(
							env('URL_CLIENT').'get-info-client',
							'POST',
							[
								'Content-Type: application/json',
								'cache-control: no-cache',
								'Authorization: Bearer '.env('TOKE_CLIENT')
							],
							[
								'msisdn' => $msisdn
							]
						);
			
			if($response['success'] && is_object($response['data']) && $response['data']->success){
				$sort = [];
				
				if(!empty($response['data']->data->offers_detail)){
	                foreach($response['data']->data->offers_detail as $item){
	                    $sort[$item->expired] []= [
	                        'name' => $item->name,
	                        'total' => $item->total,
	                        'remaing' => $item->remaing
	                    ];
	                }
	            }

                $response['data']->data->sort = $sort;

				return ['success' => true, 'data' => $response['data']->data];
			}

			//Log::error('Falló consulta API client msisdn: '.$msisdn, $response);

			return ['success' => false, 'msg' => !empty($response['data']->data) ? $response['data']->data->msg : $response['data']];
		}
		
		return ['success' => false, 'msg' => 'No se pudo consultar info. del cliente.'];
	}
}