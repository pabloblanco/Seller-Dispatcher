<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\DB;
use App\Utilities\ApiCharger;
use Illuminate\Http\Request;
use App\Models\Concentrator;
use App\Models\Service;
use App\Models\Sale;
use App\Models\User;
//use DateTime;

class chargerController extends Controller
{
    public function index(Request $request) {
		$service = new \stdClass;
		if($request->isMethod('post')){
			$userDB = User::getOnliyUser(session('user'));
            $balanceUser = $userDB->charger_balance;

			@$inputs = $request->all();


			if($inputs['sltService']<100000){
				$serviceDB = Service::getService($inputs['sltService']);
            	$priceService = $serviceDB->price_pay;
            }
            else{
            	$priceService = $inputs['priceserv'];
            }

            if($balanceUser < $priceService){
            	$service->status = false;
		    	$service->charger = false;
	           	$service->message = "Monto supera el saldo del Vendedor";
           	}else{
				if(@$inputs['hidmsisdn'] != 0 OR !empty(@$inputs['hidmsisdn']) OR is_numeric(@$inputs['hidmsisdn'])){
					if(!empty($inputs['hitransaction']) /*AND !empty($inputs['hilat']) AND !empty($inputs['hilng'])*/ AND !empty($inputs['hitok']) AND !empty($inputs['sltService'])){

						$token = $inputs['hitok'];

						$data = [
				            'msisdn' => $inputs['hidmsisdn'],
				            'seller' => session('user'),
				            'service' => $inputs['sltService'],
				            'transaction' => $inputs['hitransaction']
				        ];

				        if(!empty($inputs['hilat']) && !empty($inputs['hilng'])){
				        	$data['lat'] = $inputs['hilat'];
				        	$data['lng'] = $inputs['hilng'];
				        }

				        $responseStep2 = ApiCharger::step2Seller($token, $data);

				    	if($responseStep2['success']){
				    		#Actualiza la venta
	                        Sale::getConnect('W')
            					->where('unique_transaction', $inputs['hitransaction'])
            					->update(['users_email' => session('user')])
            					;
							#Actualiza nuevamente el saldo del concentrador de NetWey    
							Concentrator::setBalance(1, 9999999);

	                        #Actualiza la usuario
	                        $balanceFinal = (float)$balanceUser - (float)$priceService;
	                        User::setBalance(session('user'), $balanceFinal);

	                        $service->charger = true;
	                        $service->status = true;
				    		$service->msisdn = $inputs['hidmsisdn'];
		    	        	$service->transaction = $responseStep2['transaction'];
		    	        	$service->date = $responseStep2['createdAt'];
				    	}else{
				    		$service->status = false;
				    		$service->charger = false;
		            		$service->message = "Transacción fallida, intente más tarde.";
				    	}
				    }else{
				    	$service->status = false;
				    	$service->charger = false;
		            	$service->message = "Falta información, intente nuevamente.";
				    }
			    }else{
			    	$service->status = false;
			    	$service->charger = false;
		            $service->message = "Falta información, intente nuevamente.";
			    }
			}
		    return view('charger.index', compact('service'));
		}else{
			$service->status = true;
			$service->charger = false;
			$service->message = "";
			return view('charger.index', compact('service'));
		}
	}

	 public function find(Request $request){
	 	if($request->isMethod('post')){
	 		$userDB = User::getOnliyUser(session('user'));

            $service = new \stdClass;
            if($userDB->charger_balance == 0){
            	$service->status = false;
		    	$service->charger = false;
	            $service->message = "Saldo Insuficiente para hacer recargas.";
            }else{
	            $inputs = $request->all();
		        if($inputs['msisdn'] != 0 OR !empty($inputs['msisdn']) OR is_numeric($inputs['msisdn'])){
		            $responseAuth = ApiCharger::auth();

					if($responseAuth['success']){
						$data = [
			                'msisdn' => $inputs['msisdn'],
			                'seller' => session('user')
		            	];

		            	if(!empty($inputs['lat']) && !empty($inputs['lon'])){
				        	$data['lat'] = $inputs['lat'];
				        	$data['lng'] = $inputs['lon'];
				        }

		    	        $responseStep1 = ApiCharger::step1($responseAuth['token'], $data);

		    	        $service = new \stdClass;
		    	        if($responseStep1['success']){
		    	        	$service->status = true;
		    	        	$service->token = $responseAuth['token'];
		    	        	$service->msisdn = $inputs['msisdn'];
		    	        	$service->transaction = $responseStep1['transaction'];
		    	        	$service->elements = $responseStep1['services'];

		    	        	if(!empty($inputs['lat']) && !empty($inputs['lon'])){
			    	        	$service->lat = $inputs['lat'];
			    	        	$service->lng = $inputs['lon'];
			    	        }
		    	    	}else{
		    	    		$service->status = false;
	            			$service->message = $responseStep1['msg'];
		    	    	}
					}else{
						$service->status = false;
	            		$service->message = $responseAuth['msg'];
					}
		        }else{
		            $service->status = false;
		            $service->message = "MSISDN invalido.";
		        }
		    }

		    return response()->view('charger.find', compact('service'));
    	}
    }
}
