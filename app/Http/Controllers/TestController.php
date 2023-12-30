<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Mail\Mail_tycfQr;
use App\Mail\mailContract;
use App\Mail\mailInstall;
use App\Mail\mailMifi;
use App\Mail\mailMifiHuella;
use App\Mail\mailMigracion;
use App\Mail\mailSuperSim;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller
{
  public function viewEmail()
  {
    $dataBody = [
      'name' => 'Freddy',
      'lastname' => 'Colmenares',
      'seller'  => 'Pepito vendedor Perez',
      'sellerPhone' => '1234556770',
      'installer' => 'Pepito instalador Perez',
      'installerPhone' => '1234556770',
      'address' => 'Dirección de instalación',
      'date' => '18-12-2021 09:00'
    ];

    return response()->view('mails.mail_install', compact('dataBody'));
  }

  public function testEmail(Request $request){


    // $DataBody = [
    //   'asunto' => "Contrato de adhesión para agendamiento de cita de fibra Netwey",
    //   'client_name' => 'Nombre_Cliente',
    //   'urlqr' => 'https://www.google.com',
    //   'process' => "agendamiento de cita",
    //   'bodytext' => "Entendemos que puede haber ciertos términos y condiciones que usted querrá revisar antes de proceder con este agendamiento del servicio de Fibra Netwey. Tómese unos minutos para leerlas antes de aceptarlas para continuar con el proceso.",
    //   'nota' => "Una vez revisado y aceptado el contrato de adhesión puedes informar a tu asesor de ventas para proseguir con el proceso de agendamiento"
    // ];

    // // Mail::to($request->email)->send(new Mail_tycfQr($DataBody));
    // return view('mails.mailTycFQr', compact('DataBody'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $data = [
    //   'url_contract' => 'https://www.google.com',
    //   'full_date' => '01-01-2023' . ' / ' . '10:00',
    //   'pack_title' => 'paquete de fibra',
    //   'pack_price' => '999',
    //   'phone' => '5212345678',
    //   'email' => 'correo@correo.com'
    // ];
    // // Mail::to($request->email)->send(new mailContract($data));
    // return view('mails.mail_send_contract', compact('data'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'name' => 'Nombre_Cliente',
    //   'lastname' => 'Apellido_Cliente',
    //   'seller' => 'Nombre_vendedor' . ' ' . 'Apellido_Vendedor',
    //   'sellerPhone' => '5287654321',
    //   //'installer' => $installer->name . ' ' . $installer->last_name,
    //   'installer' => "Por establecer...",
    //   //'installerPhone' => $installer->phone,
    //   'installerPhone' => "Por establecer...",
    //   'address' => 'Av. Siempre Viva 123' . ' Referencia: ' . 'casa azul',
    //   'date' => '01-01-2023' . ' / ' . '10:00',
    // ];

    // // Mail::to($request->email)->send(new mailInstall($dataBody));
    // return view('mails.mail_install', compact('dataBody'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'dn' => '10109999',
    //   'phone1' => '5212345678',
    //   'email' => 'correo@correo.com'
    // ];
    // // Mail::to($request->email)->send(new mailMifi($dataBody));
    // return view('mails.mail_mifi', compact('dataBody'));


    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'dn' => '10109999',
    //   'phone1' => '5212345678',
    //   'email' => 'correo@correo.com'
    // ];
    // // Mail::to($request->email)->send(new mailMifiHuella($dataBody));
    // return view('mails.mail_mifi_huella', compact('dataBody'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'dn' => '10109999',
    //   'phone1' => '5212345678',
    //   'email' => 'correo@correo.com'
    // ];
    // // Mail::to($request->email)->send(new mailMigracion($dataBody));
    // return view('mails.mail_migration', compact('dataBody'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'dn' => '10109999',
    //   'phone1' => '5212345678',
    //   'email' => 'correo@correo.com'
    // ];
    //  // Mail::to($request->email)->send(new mailSuperSim($dataBody));
    // return view('mails.mail_supersim', compact('dataBody'));

    //*****************************************************************************//
    //*****************************************************************************//

    // $dataBody = [
    //   'name'        => 'luis',
    //   'lastname'    => 'Perez',
    //   'dn'          => '1234567890',
    //   'phone1'      => '414123456789',
    //   'email'       => 'correo@dominio.com',
    //   'address'     => 'Av 1 sector rivera',
    //   'Labeladdress' => 'Dirección: '
    //   ];

    //  // Mail::to($request->email)->send(new mailWelcome($dataBody));
    // return view('mails.mailWelcome', compact('dataBody'));


    //*****************************************************************************//
    //*****************************************************************************//


    return "ok";
  }
}
