<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentityVerification;
use App\Utilities\Truora;


class identityController extends Controller
{
  private function createdNewToken($client, $msisd){
    //obteniendo nuevo token
    $res = Truora::getUrlToRedirect(uniqid($client.$msisd));

    if($res['success']){
      $identity = IdentityVerification::getConnect('W');
      $identity->clients_dni = $client;
      $identity->msisdn = $msisd;
      $identity->user = session('user');
      $identity->process_id = $res['process_id'];
      $identity->account_id = $res['account_id'];
      $identity->date_reg = date('Y-m-d H:i:s');
      $identity->date_update = date('Y-m-d H:i:s');
      $identity->status = 'I';
      $identity->url_redirect = $res['url'];
      $identity->save();

      return response()->json([
        'error' => false, 
        'data' => [
          'status' => 'redirect',
          'url' => $res['url'],
          'id' => $identity->id
        ]
      ]);
    }

    return response()->json([
      'error' => true, 
      'msg' => 'No se pudo iniciar proceso para validar identidad, por favor intente mas tarde.'
    ]);
  }

  public function validIdentity(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if(!empty($request->msisdn) && !empty($request->client)){
        //Consulta si hay una verificación iniciada o exitosa asociada al dn y cliente
        $prevValid = IdentityVerification::getPrevValidationActive($request->msisdn, $request->client);
        
        //Condición para generar un nuevo token
        if(empty($prevValid) || $prevValid->user != session('user')){
          //obteniendo nuevo token
          return $this->createdNewToken($request->client, $request->msisdn);
        }else{
          if($prevValid->status == 'I'){
            //Verificando si el proceso esta activo en truora
            $res = Truora::processVerification($prevValid->process_id);

            //Si el estatus es pendiente retorna la url para que finalicen le proceso
            //if($res['success'] && $res['status'] == 'pending'){
              return response()->json([
                'error' => false, 
                'data' => [
                  'status' => 'redirect',
                  'url' => $prevValid->url_redirect,
                  'id' => $prevValid->id
                ]
              ]);
            //}

            //Actualiza el estatus dependiendo del estatus en truroa
            if($res['success'] && $res['status'] == 'success'){
              $prevValid->status = 'S';
              $prevValid->status_code = $res['status_code'];
            }else{
              $prevValid->status = 'F';
              if(!empty($res['status_code'])){
                $prevValid->status_code = $res['status_code'];
              }
            }
            if(!empty($res['response'])){
              $prevValid->resp_process = $res['response'];
            }
            $prevValid->save();
            
            //Si truora dice que el proceso fallo genera un nuevo token
            if($prevValid->status == 'F'){
              return $this->createdNewToken($request->client, $request->msisdn);
            }
          }

          //Si el proceso ya fue validado
          if($prevValid->status == 'S'){
            return response()->json([
              'error' => false, 
              'data' => [
                'status' => 'verified',
                'id' => $prevValid->id
              ]
            ]);
          }
        }
      }

      return response()->json([
        'error' => true, 
        'msg' => 'No se pudo iniciar proceso para validar identidad, por favor intente mas tarde.'
      ]);
    }

    return redirect()->route('dashboard');
  }

  public function checkValidIdentity(Request $request){
    if ($request->isMethod('post') && $request->ajax()) {
      if(!empty($request->id)){
        $status = [
          'F' => 'failure',
          'S' => 'success',
          'I' => 'pending'
        ];

        $veri = IdentityVerification::getVerificationInProcess($request->id);

        if(!empty($veri)){
          //Verificando si el proceso esta activo en truora
          $res = Truora::processVerification($veri->process_id);

          if($res['success']){
            if($res['status'] == 'success'){
              $veri->status = 'S';
              $veri->status_code = $res['status_code'];
            }

            if($res['status'] == 'failure'){
              $veri->status = 'F';
              $veri->status_code = $res['status_code'];
            }

            if(!empty($res['response'])){
              $veri->resp_process = $res['response'];
            }
            $veri->save();
          }

          return response()->json([
            'error' => false, 
            'status' => $status[$veri->status],
            'status_detail' => !empty($res['status_detail']) ? $res['status_detail'] : ''
          ]);
        }
      }

      return response()->json([
        'error' => true, 
        'msg' => 'No se pudo validar el proceso de verificación de identidad, por favor intente mas tarde.'
      ]);
    }

    return redirect()->route('dashboard');
  }

  public function redirectTruora(){
    return view('seller.redirect_truora');
  }
}
