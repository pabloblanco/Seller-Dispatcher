<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\Schedules;
use App\Utilities\Common;
use Illuminate\Http\Request;

class clientController extends Controller
{
  function list($page = 1, $search = '') {
    $sellers = [];

    if(session('hierarchy') >= env('HIERARCHY')){
      $sellers = User::getParents(session('user'));
    }

    $filters = explode(',', $search);

    foreach ($filters as $filter) {
      $filter = explode('=', $filter);
      if (count($filter) == 2) {
        if ($filter[0] == 'np') {
          $np = $filter[1];
        }
        //Verificando que el usuario tenga los permisos para realizar el filtro
        if (session('user_type') != 'vendor') {
          if ($filter[0] == 'date') {
            $date = explode(' - ', $filter[1]);
            if (count($date) == 2) {
              $dateb = date('Y-m-d H:i:s', strtotime($date[0]));
              $datee = date('Y-m-d H:i:s', strtotime($date[1]) + (3600 * 23) + 3599);
            }
          }
          if ($filter[0] == 'seller') {
            $seller = $filter[1];
          }
        }
      }
    }

    $clients = Client::getClientByfilter([
      'parents' => count($sellers) ? $sellers->pluck('email') : [],
      'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
      'name' => !empty($np) ? $np : null,
      'dateB' => !empty($dateb) ? $dateb : null,
      'dateE' => !empty($datee) ? $datee : null,
      'seller' => !empty($seller) ? $seller : null
    ]);

    $data = $this->_getPaginateData(
              $page, 
              trim($search), 
              $clients,
              'client.listAjax',
              'getArrayForClient'
            );

    return view('client.list', compact('data', 'sellers'));
  }

  public function listAjax($page = 1, $search = '')
  {
    $sellers = [];

    if(session('hierarchy') >= env('HIERARCHY')){
      $sellers = User::getParents(session('user'));
    }

    $filters = explode(',', $search);

    foreach ($filters as $filter) {
      $filter = explode('=', $filter);
      if (count($filter) == 2) {
        if ($filter[0] == 'np') {
          $np = $filter[1];
        }
        //Verificando que el usuario tenga los permisos para realizar el filtro
        if (session('user_type') != 'vendor') {
          if ($filter[0] == 'date') {
            $date = explode(' - ', $filter[1]);
            if (count($date) == 2) {
              $dateb = date('Y-m-d H:i:s', strtotime($date[0]));
              $datee = date('Y-m-d H:i:s', strtotime($date[1]) + (3600 * 23) + 3599);
            }
          }
          if ($filter[0] == 'seller') {
            $seller = $filter[1];
          }
        }
      }
    }

    $clients = Client::getClientByfilter([
      'parents' => count($sellers) ? $sellers->pluck('email') : [],
      'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
      'name' => !empty($np) ? $np : null,
      'dateB' => !empty($dateb) ? $dateb : null,
      'dateE' => !empty($datee) ? $datee : null,
      'seller' => !empty($seller) ? $seller : null
    ]);

    $data = $this->_getPaginateData(
              $page, 
              trim($search), 
              $clients,
              'client.listAjax',
              'getArrayForClient'
            );

    return response()->json($data);
  }

  private static function getArrayForClient(&$clients){
    foreach ($clients as $client) {
      if (hasPermit('EPD-DSE')) {
        $client->urledit = route('client.edit', ['id' => base64_encode($client->dni)]);
      } else {
        $client->urledit = '#';
      }

      if (hasPermit('CDV-DSE')) {
        $client->schedule = route('call.newschedule', ['dni' => base64_encode($client->dni)]);
      } else {
        $client->schedule = '#';
      }

      $client->date_reg = getFormatDate($client->date_reg, 'd-m-Y H:i:s');

      if (!empty($client->contact_date)) {
        $client->contact_date = getFormatDate($client->contact_date, 'd-m-Y');
      }


      if (session('user_type') == 'vendor') {
        $client->reg_email = '';
      }

      if (empty($client->vname)) {
        $client->seller = 'Store';
      } else {
        $client->seller = $client->vname . ' ' . $client->vlast_name;
      }
    }
  }

  public function listClient($page = 1, $search = '')
  {
    $sellers = [];

    if(session('hierarchy') >= env('HIERARCHY')){
      $sellers = User::getParents(session('user'));
    }

    $clients = Client::getClientNetweyByfilter([
      'parents' => count($sellers) ? $sellers->pluck('email') : [],
      'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
      'name' => !empty($search) ? $search : null,
      //'dateB' => !empty($dateb) ? $dateb : null,
      //'dateE' => !empty($datee) ? $datee : null,
      //'seller' => !empty($seller) ? $seller : null
    ]);

    $data = $this->_getPaginateData(
              $page, 
              trim($search), 
              $clients,
              'client.listClientAjax',
              'getArrayForClientNetwey'
            );

    return view('client.clientList', compact('data'));
  }

  public function listClientAjax($page = 1, $search = '')
  {
    $sellers = [];

    if(session('hierarchy') >= env('HIERARCHY')){
      $sellers = User::getParents(session('user'));
    }

    $clients = Client::getClientNetweyByfilter([
      'parents' => count($sellers) ? $sellers->pluck('email') : [],
      'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
      'name' => !empty($search) ? $search : null,
      //'dateB' => !empty($dateb) ? $dateb : null,
      //'dateE' => !empty($datee) ? $datee : null,
      //'seller' => !empty($seller) ? $seller : null
    ]);

    $data = $this->_getPaginateData(
              $page, 
              trim($search), 
              $clients,
              'client.listClientAjax',
              'getArrayForClientNetwey'
            );

    return response()->json($data);
  }

  private static function getArrayForClientNetwey(&$clients){
    foreach ($clients as $client) {
      if (hasPermit('ECD-DSE')) {
        $client->urledit = route('clientNP.edit', ['id' => base64_encode($client->dni)]);
      } else {
        $client->urledit = '#';
      }

      $client->schedule = '#';
    }
  }

  private function _getPaginateData($page = 1, $search = '', $collection, $routeName, $function)
  {
    //$search = trim($search);
    $firstpg = '#';
    $lastpg = '#';

    $limitPgNumber = 4; //limite del paginador, (1,2..4)
    $take = 3; //Limite de la data
    $skip = $page == 1 ? 0 : ($take * ($page - 1)); //Datos a saltar

    

    $totalClients = $collection->count();

    $clients = $collection->skip($skip)
                       ->take($take)
                       ->get();

    //Calculando paginas totales
    $pages = ceil($totalClients / $take);

    $dataPages = []; //array que almacena informacion del paginado

    if ($pages > 1) {
      //numero de vueltas que debe dar el ciclo
      $loop = $pages > $limitPgNumber ? $limitPgNumber : $pages;
      //Calculando el numero en el que debe comenzar el ciclo

      if ($pages <= 4) {
        $start = 0;
      } else {
        if (($page - 1) <= ($pages - $limitPgNumber)) {
          $start = $page - 1;
        } else {
          $start = $pages - $limitPgNumber;
        }

      }

      //cliclo que arma el array de paginado
      for ($i = $start; $i < ($loop + $start); $i++) {
        //parametro para armar url del paginado
        $params = array('page' => ($i + 1));
        if (!empty($search)) {
          $params['search'] = $search;
        }

        $dataPages[] = array(
          'active' => $i == ($page - 1) ? true : false, //pagina activa
          'url' => route($routeName, $params), //URL de la pagina
          'number' => ($i + 1), //Numero a mostrar en el paginado
        );
      }

      //Calculando pagina a la que debe apuntar la flecha "inicio" de retroceso en el paginado
      $params = array('page' => 1);
      if (($loop + $start) > $limitPgNumber) {
        $params = array('page' => ($loop + $start) - $limitPgNumber);
      }

      //agregando parametro de busqueda en caso de que exista
      if (!empty($search)) {
        $params['search'] = $search;
      }

      //url a la que debe apuntar la flecha "ultimo"
      $paramslast = array('page' => $pages);
      if (!empty($search)) {
        $paramslast['search'] = $search;
      }

      $firstpg = route($routeName, $params);
      $lastpg = route($routeName, $paramslast);

    }

    call_user_func_array(self::class.'::'.$function, array(&$clients));

    $data = array(
      "recordsTotal" => $totalClients,
      "totalPAges" => $pages,
      "actualPage" => $page,
      "clients" => $clients,
      "limit" => $take,
      "pages" => $dataPages,
      "first" => $firstpg,
      "last" => $lastpg,
      "canSchedule" => false,
      "canEdit" => hasPermit('ECD-DSE'),
    );

    return $data;
  }

  public function _registerLeaflet($inputs = false)
  {
    $obj = new \stdClass;
    $obj->error = true;
    if ($inputs && hasPermit('RCL-DSE')) {
      $dateInput = null;
      if (!empty($inputs['birthday'])) {
        if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/", $inputs['birthday'])) {
          $newDate = new DateTime($inputs['birthday']);
          $dateInput = $newDate->format('Y-m-d');
        } else {
          $obj->message = 'Formato de fecha erroneo.';
          return $obj;
        }
      }

      if(!empty($inputs['email']) && !filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)){
        $obj->message = 'Formato de email erroneo.';
        return $obj;
      }

      if (empty($inputs['dni'])) {
        $inputs['dni'] = uniqid();
      }

      $data = [
        'dni' => $inputs['dni'],
        'name' => Common::normaliza($inputs['name']),
        'last_name' => Common::normaliza($inputs['last_name']),
        'address' => $inputs['direction'],
        'birthday' => $dateInput,
        'email' => $inputs['email'],
        'phone_home' => $inputs['phone'],
        'phone' => (($inputs['phone2']) ? $inputs['phone2'] : null),
        'social' => $inputs['social'],
        'date_reg' => date("Y-m-d H:i:s"),
        'reg_email' => session('user'),
      ];

      if($inputs['social'] == 'S' && !empty($inputs['campaign'])){
        $data['campaign'] = $inputs['campaign'];
      }

      if (!empty($inputs['lat']) && !empty($inputs['lon'])) {
        $data['lat'] = $inputs['lat'];
        $data['lng'] = $inputs['lon'];
      }

      if (!empty($inputs['note'])) {
        $data['note'] = $inputs['note'];
      }

      if (!empty($inputs['nextC'])) {
        $newDate = new DateTime($inputs['nextC']);
        $data['contact_date'] = $newDate->format('Y-m-d H:i:s');
      }

      //validando si es vendedor para asignarle el cliente
      if (session('user_type') == 'vendor') {
        $data['user_mail'] = session('user');
      }

      $client = Client::getClientINEorDN($inputs['dni'], $inputs['phone'], $inputs['email']);

      if (empty($client)) {
        Client::getConnect('W')->insert($data);

        $obj->dni = $inputs['dni'];
        $obj->phone = $inputs['phone'];
        $obj->name = Common::normaliza($inputs['name']);
        $obj->last_name = Common::normaliza($inputs['last_name']);
        $obj->error = false;
        $obj->message = 'Prospecto registrado exitosamente.';
      } else {
        $msg = 'El o los siguiente(s) dato(s),';
        if(!empty($client->email) && $client->email == $inputs['email']){
          $msg .= ' email,';
        }

        if($client->phone_home == $inputs['phone']){
          $msg .= ' teléfono 1,';
        }

        if($client->dni == $inputs['dni']){
          $msg .= ' INE,';
        }

        $msg .= ' ya se encuentra(n) registrado(s).';

        $obj->message = $msg;
      }
    }

    return $obj;
  }

  public function registerAjax(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();
      $obj = new \stdClass;

      if (!empty($inputs['name']) && !empty($inputs['last_name']) && !empty($inputs['phone'])) {
        $obj = $this->_registerLeaflet($inputs);
      } else {
        $obj->error = true;
        $obj->message = 'El nombre, apellido y teléfono son obligatorios.';
      }

      return response()->json($obj);
    }
  }

  //Registrar clientes
  public function register(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $this->validate($request, [
        'name' => 'required',
        'last_name' => 'required',
        'phone' => 'required',
      ]);

      $res = $this->_registerLeaflet($inputs);

      if ($res->error) {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', $res->message);

        return redirect()->back()->withInput();
      } else {
        session()->flash('message_class', 'alert-success');
        session()->flash('message_error', $res->message);
      }
    }
    return view('client.register');
  }

  //Editar cliente
  public function editClient(Request $request, $dni = null)
  {
    $dni = base64_decode($dni);

    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $this->validate($request, [
        'name' => 'required',
        'last_name' => 'required',
        'phone' => 'required',
        'dni' => 'required'
      ]);

      if ($dni != $inputs['dni']) {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'Cliente no registrado.');
        return redirect()->route('client.listClient');
      }

      $bt = null;
      if (!empty($inputs['birthday'])) {
        $tmpDate = new DateTime($inputs['birthday']);
        $bt = $tmpDate->format('Y-m-d');
      }

      $dataUpdate = array(
        'name' => $inputs['name'],
        'last_name' => $inputs['last_name'],
        'address' => $inputs['direction'],
        'birthday' => $bt,
        'email' => $inputs['email'],
        'phone_home' => $inputs['phone']
      );

      if(!empty($inputs['phone2'])){
        $dataUpdate['phone'] = $inputs['phone2'];
      }

      if(!empty($inputs['social'])){
        $dataUpdate['social'] = $inputs['social'];
      }

      if (!empty($inputs['nextC'])) {
        $newDate = new DateTime($inputs['nextC']);
        $data['contact_date'] = $newDate->format('Y-m-d H:i:s');
      }

      if(!empty($inputs['note'])){
        $dataUpdate['note'] = $inputs['note'];
      }

      $client = Client::getProspectByPhone($inputs['phone'], $inputs['dni'], $inputs['email']);

      if (empty($client)) {
        $res = Client::getConnect('W')
                      ->where('dni', $inputs['dni'])
                      ->update($dataUpdate);

        if ($res) {
          session()->flash('message_class', 'alert-success');
          session()->flash('message_error', 'Se actualizo el cliente.');
        }
      } else {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'No se actualizaron los datos, el número de teléfono se encuentra asociado a otro cliente.');
      }
    }

    $data = Client::getClientINEorDN($dni, false);

    if (empty($data)) {
      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'Cliente no registrado.');
      return redirect()->route('client.listClient');
    }

    return view('client.editClient', compact('data'));
  }

  //Editar prospecto
  public function edit(Request $request, $dni = null)
  {
    if (!hasPermit('EPD-DSE')) {
      return redirect()->route('dashboard');
    }

    $dni = base64_decode($dni);

    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $this->validate($request, [
        'name' => 'required',
        'last_name' => 'required',
        'phone' => 'required',
      ]);

      if ($dni != $inputs['dni']) {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'Cliente no registrado.');
        return redirect()->route('client.listClient');
      }

      $bt = null;
      if (!empty($inputs['birthday'])) {
        $tmpDate = new DateTime($inputs['birthday']);
        $bt = $tmpDate->format('Y-m-d');
      }

      $dataUpdate = array(
        'name' => $inputs['name'],
        'last_name' => $inputs['last_name'],
        'address' => $inputs['direction'],
        'birthday' => $bt,
        'email' => $inputs['email'],
        'phone_home' => $inputs['phone'],
        'phone' => $inputs['phone2']
        //'social' => $inputs['social'],
      );

      if(!empty($inputs['phone2'])){
        $dataUpdate['phone'] = $inputs['phone2'];
      }

      if(!empty($inputs['social'])){
        $dataUpdate['social'] = $inputs['social'];
      }

      if (!empty($inputs['nextC'])) {
        $newDate = new DateTime($inputs['nextC']);
        $data['contact_date'] = $newDate->format('Y-m-d H:i:s');
      }

      if (!empty($inputs["note"])) {
        $dataUpdate["note"] = $inputs["note"];
      }

      $client = Client::getProspectByPhone($inputs['phone'], $inputs['dni'], $inputs['email']);

      if (empty($client)) {
        $res = Client::getConnect('W')
                      ->where('dni', $inputs['dni'])
                      ->update($dataUpdate);

        if ($res) {
          session()->flash('message_class', 'alert-success');
          session()->flash('message_error', 'Se actualizo el prospecto.');
        }
      } else {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'No se actualizaron los datos, el número de teléfono se encuentra asociado a otro prospecto.');
      }
    }

    $data = Client::getClientINEorDN($dni, false);

    if (empty($data)) {
      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'Prospecto no registrado.');
      return redirect()->route('client.list');
    }

    return view('client.edit', compact('data'));
  }

  //Devuelve un json con la lista de citas de un usuario dado y una fecha dada
  public function listDate(Request $request)
  {
    $inputs = $request->all();

    if (!empty($inputs['date']) && !empty($inputs['email'])) {
      $dates = Schedules::getSchedules([
        'user' => $inputs['email'],
        'dateB' => $inputs['date'].' 00:00:00',
        'dateE' => $inputs['date'].' 23:59:59'
      ]);

      return response()->json([
              'error' => false, 
              'date' => $inputs['date'],
              'dates' => $dates->count() ? $dates : false
             ]);
    }

    return response()->json(['error' => true]);
  }

  private function _getDataNEDate($client, $sellers)
  {
    if(count($sellers) || session('hierarchy') < env('HIERARCHY')){
      $listSellers = User::getOnlySellers([
        'parents' => $sellers->pluck('email')
      ]);
    }

    $data = [
      'client' => $client,
      'hasSeller' => false,
      'showSellers' => (!empty($listSellers) && $listSellers->count() > 0),
      'sellers' => !empty($listSellers) ? $listSellers : [],
      'dates' => [],
      'editschedule' => false
    ];

    if (!empty($client->user_mail)) {
      $seller = User::getUser($client->user_mail, 'R');

      if(!empty($seller) && $seller->platform == 'vendor'){
        $data['hasSeller'] = true;
        $data['sellerName'] = $seller->name . ' ' . $seller->last_name;
        $data['SellerEmail'] = $client->user_mail;

        $data['dates'] = Schedules::getSchedules([
          'user' => $client->user_mail,
          'dateB' => date("Y-m-d"). ' ' . '00:00:00',
          'dateE' => date("Y-m-d"). ' ' . '23:59:59'
        ]);

        $data['date'] = date("Y-m-d");
      }
    }

    return $data;
  }

  public function editschedule(Request $request, $idSche = null)
  {
    if (!empty($idSche) && hasPermit('ECV-DSE')) {
      $idSche = base64_decode($idSche);
      $schedule = Schedules::getScheduleById($idSche);

      if (!empty($schedule)) {
        if(session('hierarchy') >= env('HIERARCHY')){
          $sellers = User::getParents(session('user'));
        }

        $client = Client::getClientByuser([
          'parents' => $sellers->pluck('email'),
          'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
          'dni' => !empty($schedule->client_dni) ? $schedule->client_dni : null
        ]);

        if ($request->isMethod('post')) {
          $inputs = $request->all();
          $dni = $schedule->client_dni;

          if (!empty($inputs['dateCalendar']) && 
              !empty($inputs['hour']) && 
              !empty($inputs['sellerEmail']) && 
              !empty($dni)) {

            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $inputs['dateCalendar'])) {
              if (empty($client->user_mail) || $client->user_mail != $inputs['sellerEmail']) {
                Client::getConnect('W')
                        ->where('dni', $dni)
                        ->update(['user_mail' => $inputs['sellerEmail']]);
              }

              $newDate = new DateTime($inputs['dateCalendar'] . ' ' . $inputs['hour']);
              $dateInput = $newDate->format('Y-m-d H:i:s');

              Schedules::getConnect('W')->where('id', $idSche)->update([
                'users_email' => $inputs['sellerEmail'],
                'client_dni' => $dni,
                'date_schedules' => $dateInput,
                'reg_email' => session('user'),
                'status' => 'A'
              ]);

              session()->flash('message_class', 'alert-success');
              session()->flash('message_error', 'Cita editada.');

              return redirect()->route('client.scheduleList');
            } else {
              session()->flash('message_class', 'alert-danger');
              session()->flash('message_error', 'Fecha no válida.');
            }
          } else {
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'Faltan datos para agendar la cita.');
          }
        }

        if (!empty($client) || $client) {
          $data = $this->_getDataNEDate($client, $sellers);
          $data['editschedule'] = true;
          
          return view('client.newSchedule', compact('data'));
        } else {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'Prospecto no registrado.');
          return redirect()->route('client.scheduleList');
        }
      } else {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'No se consiguio la cita.');
        return redirect()->route('client.scheduleList');
      }
    }

    return redirect()->route('dashboard');
  }

  //guarda citas nuevas
  public function newSchedule(Request $request, $dni = null)
  {
    if (!empty($dni)) {
      $dni = base64_decode($dni);

      if(session('hierarchy') >=  env('HIERARCHY')){
        $sellers = User::getParents(session('user'));
      }

      $client = Client::getClientByuser([
        'parents' => $sellers->pluck('email'),
        'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
        'dni' => !empty($dni) ? $dni : null
      ]);

      if ($request->isMethod('post')) {
        $inputs = $request->all();

        if (!empty($inputs['dateCalendar']) && 
            !empty($inputs['hour']) && 
            !empty($inputs['sellerEmail']) && !empty($dni)) {

          if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $inputs['dateCalendar'])) {
            if (empty($client->user_mail) || $client->user_mail != $inputs['sellerEmail']) {
              Client::getConnect('W')
                      ->where('dni', $dni)
                      ->update(['user_mail' => $inputs['sellerEmail']]);
            }

            $newDate = new DateTime($inputs['dateCalendar'] . ' ' . $inputs['hour']);
            $dateInput = $newDate->format('Y-m-d H:i:s');

            Schedules::getConnect('W')->insert([
              'users_email' => $inputs['sellerEmail'],
              'client_dni' => $dni,
              'date_schedules' => $dateInput,
              'reg_email' => session('user'),
              'status' => 'A'
            ]);

            session()->flash('message_class', 'alert-success');
            session()->flash('message_error', 'Cita agendada.');

            if (hasPermit('CDV-DSE')) {
              return redirect()->route('date.new');
            } else {
              return redirect()->route('dashboard');
            }

          } else {
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'Fecha no válida.');
          }
        } else {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'Faltan datos para agendar la cita.');
        }
      }

      $date = Schedules::getActiveScheduleByClient($dni);

      if (!empty($client) && empty($date)) {
        $data = $this->_getDataNEDate($client, $sellers);

        return view('client.newSchedule', compact('data'));
      } else {
        if (empty($client)) {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'Prospecto no registrado.');
          if (hasPermit('CDV-DSE')) {
            return redirect()->route('date.new');
          } else {
            return redirect()->route('dashboard');
          }

        }
        if (!empty($date)) {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'El prospecto ya tiene agendada una cita.');
          if (hasPermit('CDV-DSE')) {
            return redirect()->route('date.new');
          } else {
            return redirect()->route('dashboard');
          }

        }
      }
    }
    return redirect()->route('dashboard');
  }

  private function _getListSchedule($date = null)
  {
    if (empty($date) || !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
      $now = date("Y-m-d");
      $dateB = $now . ' 00:00:00';
      $dateE = $now . ' 23:59:59';
    } else {
      $dateB = $date . ' 00:00:00';
      $dateE = $date . ' 23:59:59';
    }    

    if(session('hierarchy') >= env('HIERARCHY')){
      $sellers = User::getParents(session('user'));

      if($sellers->count()){
        $listSellers = $sellers->filter(function ($value, $key) {
                          return $value->platform == 'vendor';
                       });
      }
    }

    $list = Schedules::getListScedule([
      'dateB' => $dateB,
      'dateE' => $dateE,
      'me' => (session('hierarchy') >= env('HIERARCHY')) ? session('user') : null,
      'sellers' => (!empty($sellers) && $sellers->count()) ? $listSellers->pluck('email') : []
    ]);

    $dates = [];
    foreach ($list as $date) {
      $date->name = $date->cname.' '.$date->clast_name;
      $date->date_schedules = getFormatDate($date->date_schedules, 'd-m-Y H:i');

      if (hasPermit('ECV-DSE')){
        $date->delay = route('client.editschedule', ['idSche' => base64_encode($date->id)]);
      } else {
        $date->delay = '#';
      }

      $dates[] = $date;
    }

    return $dates;
  }

  public function getSchedule($date = null)
  {
    $dates = $this->_getListSchedule($date);
    $obj = new \stdClass;

    if ($dates && count($dates) > 0 && $dates != 'NOT_PERMIT') {
      $obj->error = false;
      $obj->code = 'OK';
      $obj->data = $dates;
      $obj->hold = hasPermit('ECV-DSE'); //posponer
      $obj->notSeller = (hasPermit('LCV-DSE') && session('user_type') != 'vendor');
    } else {
      $obj->error = false;
      $obj->code = 'NOT_DATA';
    }

    return response()->json($obj);
  }

  public function listSchedule()
  {
    $dates = $this->_getListSchedule();

    if (!hasPermit('LCV-DSE')) {
      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'No tiene citas agendadas.');

      return redirect()->route('dashboard');
    }

    return view('client.scheduleList', compact('dates'));
  }

  //consultar clientes por DN
  public function clientGetByDN(Request $request)
  {
    if ($request->isMethod('post')) {
      if(!empty($request->msisdn)){
        $msisdn = $request->msisdn;
        $client=ClientNetwey::getClientByDN($msisdn);
        if($client){
          return response()->json([
              'error' => false,
              'client' => $client,
          ]);
        }
      }
    }
    return response()->json(['error' => true]);
  }

}
