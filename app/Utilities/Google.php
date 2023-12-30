<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use App\Utilities\Common;
/*
	Clase que contiene diversos metodos para para conectarse con la api de clientes.
*/

class Google
{
	/**
	 * Consulta detalle de una dirección por medio de la API place de google
	 * @param String $address
	 * @return Array 
	 */
	public static function getDataFromAddress($address = false)
	{
		if ($address) {
			$response = Common::executeCurl(
				'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . urlencode($address) . "&key=" . env('GOOGLE_KEY'),
				'GET'
			);

			if ($response['success'] && !empty($response['data']->results) && !empty($response['data']->results[0]->geometry)) {
				return [
					'success' => true,
					'data' => [
						'lat' => $response['data']->results[0]->geometry->location->lat,
						'lng' => $response['data']->results[0]->geometry->location->lng,
						'address' => !empty($response['data']->results[0]->formatted_address) ? $response['data']->results[0]->formatted_address : null
					]
				];
			}

			Log::error('Falló consulta en api google (getDataFromAddress) dirección: ' . $address, $response);

			return ['success' => false, 'msg' => 'Falló consulta en api google (getDataFromAddress) dirección: ' . $address];
		}

		return ['success' => false, 'msg' => 'No se pudo consultar datos de la dirección.'];
	}

	/**
	 * Consulta detalle de una dirección dada su lat y lng por medio de la API place de google
	 * @param String $lat
	 * @param String $lng
	 * @return Array 
	 */
	public static function getAddressGoogle($lat = false, $lng = false)
	{
		if ($lat && $lng) {
			$url = 'https://maps.google.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . env('GOOGLE_KEY') . '&sensor=false&language=es';

			$res = Common::executeCurl($url, 'GET');

			if ($res['success'] && !empty($res['data']->results) && !empty($res['data']->results[0]->formatted_address)) {
				return [
					'success' => true,
					'data' => [
						'address' => $res['data']->results[0]->formatted_address
					]
				];
			}

			Log::error('Falló consulta en api google (getDataFromAddress) lat: ' . $lat . ' / ' . ' lng: ' . $lng, $res);

			return ['success' => false, 'msg' => 'Falló consulta en api google (getAddressGoogle) lat: ' . $lat . ' / ' . ' lng: ' . $lng];
		}

		return ['success' => false];
	}

	/**
	 * Valida captcha de google
	 * @param String $captcha
	 * @param String $ip
	 * @return Array 
	 */
	public static function veifyCaptchaGoogle($captcha = false, $ip = false, $request = false)
	{
		if ($captcha && $ip) {
			$data = 'secret=' . env('GOOGLE_CAPTCHA_BACK') . '&response=' . urlencode($captcha) . '&remoteip=' . urlencode($ip);

			$res = Common::executeCurl(
				env('URL_VERIFY_CAPTCHA'),
				'POST',
				[
					"accept: */*",
					"Content-Type: application/x-www-form-urlencoded",
					"cache-control: no-cache"
				],
				$data
			);

			if ($res['success'] && $res['data']->success && $res['data']->score >= env('GOOGLE_PT', 0.3)) {
				return ['success' => true];
			}

			$data = [];
			if ($request) {
				$data = [
					'user' => !empty($request->emailLogin) ? $request->emailLogin : 'S/I'
				];
			}

			//Log::error('Falló en captcha ip: '.$ip.' Data: '.json_encode($data), $res);

			return ['success' => false, 'data' => 'No paso captha.'];
		}

		return ['success' => false, 'data' => 'Faltan datos.'];
	}

	/**
	 * Consulta datos de una dirección dada su lat y lng por medio de la API place de google
	 * @param String $lat
	 * @param String $lng
	 * @return Array 
	 */
	public static function getDataFromPosGoogle($lat = false, $lng = false)
	{
		if ($lat && $lng) {
			$url = 'https://maps.google.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . env('GOOGLE_KEY') . '&sensor=false&language=es';

			$res = Common::executeCurl($url, 'GET');

			if ($res['success'] && !empty($res['data']->results) && !empty($res['data']->results[0]->address_components)) {
				return [
					'success' => true,
					'data' => [
						'components' => $res['data']->results[0]->address_components
					]
				];
			}

			Log::error('Falló consulta en api google (getDataFromAddress) lat: ' . $lat . ' / ' . ' lng: ' . $lng, $res);

			return ['success' => false, 'msg' => 'Falló consulta en api google (getDataFromPosGoogle) lat: ' . $lat . ' / ' . ' lng: ' . $lng];
		}

		return ['success' => false];
	}

	public static function getFormatedAddress($components = false)
	{
		$destination = [
			'route'         => false,
			'colony'        => false,
			'state'         => false,
			'postal_code'   => false,
			'street_number' => false,
			'municipality'  => false,
			'city'          => false,
			'country'       => 'MX',
		];

		if ($components && is_array($components)) {
			foreach ($components as $ele) {
				if (in_array('route', $ele->types)) {
					$destination['route'] = $ele->long_name;
				}

				if (in_array('sublocality_level_1', $ele->types) && in_array('sublocality', $ele->types)) {
					$destination['colony'] = $ele->long_name;
				}

				if (in_array('locality', $ele->types)) {
					$destination['city'] = $ele->long_name;
				}

				if (in_array('postal_code', $ele->types)) {
					$destination['postal_code'] = $ele->long_name;
				}

				if (in_array('street_number', $ele->types)) {
					$destination['street_number'] = $ele->long_name;
				}

				if (in_array('administrative_area_level_1', $ele->types)) {
					$destination['state'] = $ele->long_name;
				}
			}
		}

		return ['success' => true, 'data' => $destination];
	}
}
