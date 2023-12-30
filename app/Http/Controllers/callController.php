<?php
/*DEPRECATED*/
namespace App\Http\Controllers;

use \Curl;
use DateTime;
//use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class callController extends Controller
{
  public function client(Request $request, $search = false)
  {
    return view('call.client');
  }

  public function clientAjax($search = false)
  {
    if ($search) {
      $clients = DB::table('islim_client_netweys')
        ->select(
          DB::raw("CONCAT(islim_clients.name,' ', islim_clients.last_name, ' - ', islim_client_netweys.msisdn) AS namePhone"),
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_client_netweys.msisdn'
        )
        ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni')
        ->where(function ($query) use ($search) {
          $query->orWhere('islim_clients.last_name', 'like', '%' . $search . '%')
            ->orWhere('islim_clients.name', 'like', '%' . $search . '%')
            ->orWhere('islim_client_netweys.msisdn', 'like', '%' . $search . '%');
        })
        ->get();

      echo json_encode($clients);
    }
  }

  public function getDataClient(Request $request)
  {
    $number = $request->has('number') ? $request->input('number') : false;

    if (!empty($number)) {
      $data = new \stdClass;
      $data->error = false;

      $client = DB::table('islim_client_netweys')
        ->select('islim_client_netweys.msisdn',
          'islim_client_netweys.serviceability',
          'islim_clients.name',
          'islim_clients.last_name',
          'islim_clients.phone_home',
          'islim_clients.email',
          'islim_clients.address'
        )
        ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni')
        ->where('msisdn', $number)
        ->first();

      if (empty($client)) {
        $data->error = true;
        $data->message = 'Cliente no registrado';
        echo json_encode($data);
        exit();
      }

      $data->client = $client;

      $lastRecharge = DB::table('islim_sales')
        ->select('islim_sales.amount', 'islim_services.title')
        ->join('islim_services', 'islim_services.id', '=', 'islim_sales.services_id')
        ->where('islim_sales.msisdn', $number)
        ->where(function ($query) {
          $query->orWhere('islim_services.type', 'R')
            ->orWhere('islim_services.type', 'P');
        })
        ->orderBy('islim_sales.date_reg', 'DESC')
        ->first();

      $url = env('URL_API_ALTAM') . 'profile/' . $number;
      $dataReq = array(
        'apiKey' => env('API_KEY_ALTAM'),
      );

      if (env('APP_ENV', 'local') != 'local') {
        $data = Curl::to($url)
          ->withData($dataReq)
          ->asJson()
          ->returnResponseObject()
          ->post();
      } else {
        $data->status = 200;
        $data->content = new \stdClass;
        $data->content->status = 'success';
        $data->content->msisdn = new \stdClass;
        $data->content->msisdn->status = 'active';
        $data->content->msisdn->{'total-mb'} = '300';
        $data->content->msisdn->{'remaining-mb'} = '200';
        $data->content->msisdn->expireDate = '2018-05-15 05:00:00';
      }

      if (!empty($lastRecharge)) {
        $data->showLastRecharge = true;
        $data->amount = $lastRecharge->amount;
        $data->title = $lastRecharge->title;
      } else {
        $data->showLastRecharge = false;
      }

      if ($data->status != 200) {
        $data->showStatusLine = false;
        $data->messageStatusLine = 'Error interno de la paltaforma ALTAN.';
      } else {
        if (strtolower($data->content->status) == 'error') {
          $data->showStatusLine = false;
          $data->messageStatusLine = $data->content->message;
        } else {
          if (strtolower($data->content->msisdn->status) == 'active') {
            $data->content->msisdn->status = 'Activa';
          }

          if (strtolower($data->content->msisdn->status) == 'inactive') {
            $data->content->msisdn->status = 'Inactiva';
          }

          if (strtolower($data->content->msisdn->status) == 'Suspend (B2W)') {
            $data->content->msisdn->status = 'Suspendida';
          }

          if (strtolower($data->content->msisdn->status) == 'PreActive') {
            $data->content->msisdn->status = 'Plan vencido';
          }

          if (strtolower($data->content->msisdn->status) == 'Activa' && (!empty($data->content->msisdn->supplementaryOffers) && count($data->content->msisdn->supplementaryOffers) > 0)) {
            $res = $this->sortSupOffers($data->content->msisdn->supplementaryOffers);
            $data->content->msisdn->expireDate = $res ? $res->expireDate : 'S/F';
          }
        }
      }

      echo json_encode($data);
    }
  }

  private function sortSupOffers($offers = false)
  {
    if ($offers) {
      $lastDate = 0;
      $element = false;
      $c = 0;
      foreach ($offers as $offer) {
        $date = new DateTime($offer->expireDate);
        $date = $date->getTimestamp();
        if ($c == 0) {
          $unusedAmt = 12345;
        } else {
          $unusedAmt = $offer->unusedAmt;
        }

        if ($lastDate < $date && $unusedAmt > 0) {
          $lastDate = $date;
          $element = $offer;
        }
      }
      if ($element) {
        return $element;
      }

    }
    return false;
  }
}
