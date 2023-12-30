<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Paysheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NominaController extends Controller
{
  public function index(Request $request)
  {

    if (!empty(session('user_dni'))) {
      $recs = Paysheet::getPaySheets(session('user_dni'), 12);

      foreach ($recs as $rec) {
        $par = Paysheet::getPaySheetByRelTypeAndUser(
          $rec->rel_type,
          session('user_dni'),
          $rec->type
        );

        $rec->rel = $par;
      }

      $user_c = User::getUserByDni(session('user_dni'));

      return view('nomina.index', compact('recs', 'user_c'));
    }

    return redirect()->route('dashboard');
  }

  public function getFile(Request $request, $type)
  {
    if ($request->isMethod('post') && !empty($type)) {
      if (!empty(session('user_dni')) && !empty($request->name)) {
        $name = $request->name;

        $rec = Paysheet::getUrlByTypeAndUser($name, session('user_dni'), $type);

        if (!empty($rec)) {
          $disk = Storage::disk('s3');

          $command = $disk->getDriver()
            ->getAdapter()
            ->getClient()
            ->getCommand(
              'GetObject',
              [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $rec->url_download,
              ]
            );

          $url = $disk->getDriver()
            ->getAdapter()
            ->getClient()
            ->createPresignedRequest($command, '+5 minutes');

          if($rec->status != 'R'){
            $rec->status = 'R';
            $rec->save();
          }

          return response()->json(['error' => false, 'url' => (String) $url->getUri()]);
        }
      }

      return response()->json(['error' => true, 'message' => 'No se pudo descargar el recibo']);
    }

    return redirect()->route('dashboard');
  }

  public function getFileContract(Request $request)
  {
    if ($request->isMethod('post')) {
      if (!empty(session('user_dni')) && !empty($request->name)) {
        $disk = Storage::disk('s3');

        $command = $disk->getDriver()
          ->getAdapter()
          ->getClient()
          ->getCommand(
            'GetObject',
            [
              'Bucket' => env('AWS_BUCKET'),
              'Key' => 'contracts/' . $request->name,
            ]
          );

        $url = $disk->getDriver()
          ->getAdapter()
          ->getClient()
          ->createPresignedRequest($command, '+5 minutes');

        return response()->json(['error' => false, 'url' => (String) $url->getUri()]);
      }

      return response()->json(['error' => true, 'message' => 'No se pudo descargar el recibo']);
    }

    return redirect()->route('dashboard');
  }
}
