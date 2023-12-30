<?php

namespace App\Http\Controllers;

use App\Mail\NotifyChangeStatusInv;
use App\Mail\NotifyErrorInventary;
use App\Models\History_inv_status;
use App\Models\Inventory;
use App\Models\InvRecicle;
use App\Models\Product;
use App\Models\SellerInventory;
use App\Models\SellerInventoryTemp;
use App\Models\SellerInventoryTrack;
use App\Models\StockProvaDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InventaryController extends Controller
{
  public function pendingFolios()
  {
    $user = session('user');

    $pending = StockProvaDetail::getPendingFoliosByUser($user);

    $pending = $pending->groupBy('folio');

    return response()->view('inventory.listPendingFolios', compact('pending'));
  }

  public function boxDetail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->box)) {
        $detail = StockProvaDetail::getboxDetail($request->box, session('user'));

        $html = view('inventory.boxDetail', compact('detail'))->render();
        return response()->json(['success' => true, 'html' => $html]);
      }
    }

    return response()->json(['success' => false, 'msg' => 'No se pudo cargar el detalle de la caja.']);
  }

  public function acceptBoxDetail(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = $request->data;

      if (!empty($data) && count($data)) {
        $user = session('user');
        $name = session('name') . ' ' . session('last_name');
        $dataError = [];
        $errors = [];
        $dnsNA = [];
        $dnsRC = [];

        foreach ($data as $detail) {
          $dt = StockProvaDetail::getEDDetail($detail['detail']);

          if (!empty($dt) && $dt->users == $user && $dt->status == 'P') {
            //Usuario reporto un error
            if (!empty($detail['error'])) {
              $error = 'Error no definido';

              if ($detail['error'] == 'np') {
                $error = 'Artículo incorrecto';
              }

              if ($detail['error'] == 'nf') {
                $error = 'No viene el artículo';
              }

              if ($detail['error'] == 'ot') {
                $error = !empty($detail['errorD']) ? $detail['errorD'] : 'Error no definido por usuario';
              }

              if (!count($dataError)) {
                $dataError = [
                  'usuer' => $user,
                  'name' => $name,
                  'box' => $dt->box,
                  'folio' => $dt->folio];
              }

              $errors[] = [
                'msisdn' => $dt->msisdn,
                'sku' => $dt->sku,
                'error' => $error];

              $dnsNA[] = $dt->msisdn;

              $dt->status = 'E';
              $dt->last_user_action = $user;
              $dt->coo_date_action = date('Y-m-d H:i:s');
              $dt->comment = $error;
              $dt->save();
            } else {
              //Validando si el dn ya estaba dado de alta en inventario
              $msisdn = Inventory::getDataDn($dt->msisdn);

              if (empty($msisdn)) {
                //Todo OK con el dn, se debe asignar
                $product = Product::getProductBySKU($dt->sku);
                $today = date('Y-m-d H:i:s');

                //Creando detalle de inventario
                $newDetailInv = new Inventory;
                $newDetailInv->inv_article_id = $product->id;
                $newDetailInv->warehouses_id = env('WHDEFAULT', 5);
                $newDetailInv->msisdn = $dt->msisdn;
                $newDetailInv->iccid = $dt->iccid;
                if (!empty($dt->imei)) {
                  $newDetailInv->imei = $dt->imei;
                }
                $newDetailInv->price_pay = $product->price_ref;
                $newDetailInv->date_reg = $today;
                $newDetailInv->status = 'A';
                $newDetailInv->save();

                //Asignando dn al usuario
                $assig = new SellerInventory;
                $assig->users_email = $user;
                $assig->inv_arti_details_id = $newDetailInv->id;
                $assig->date_reg = $today;
                $assig->status = 'A';
                $assig->first_assignment = $today;
                $assig->last_assignment = $today;
                $assig->last_assigned_by = $user;
                $assig->save();

                //registrando mov. de inventario
                SellerInventoryTrack::setInventoryTrack(
                  $newDetailInv->id,
                  null,
                  env('WHDEFAULT', 5),
                  $user,
                  null,
                  session('user')
                );

                //Actualizando estatus a asignado en tabla islim_stock_prova_detail
                $dt->status = 'AS';
                $dt->last_user_action = $user;
                $dt->coo_date_action = date('Y-m-d H:i:s');
                $dt->save();
              } else {

                //reviso si el DN esta en espera de reciclaje
                $ReciclerArt = InvRecicle::get_recicler_in_process($dt->msisdn);
                if (!empty($ReciclerArt)) {

                  $dt->status = 'PR';
                  $dt->statusRecycling = 'P';
                  $dt->user_assignment = $user;
                  $dt->last_user_action = $user;
                  $dt->coo_date_action = date('Y-m-d H:i:s');
                  $dt->comment = 'El msisdn se encuentra en proceso de reciclaje';
                  $dt->save();
                  $dnsRC[] = $dt->msisdn;
                } else {
                  if (!count($dataError)) {
                    $dataError = [
                      'usuer' => $user,
                      'name' => $name,
                      'box' => $dt->box,
                      'folio' => $dt->folio];
                  }

                  $errors[] = [
                    'msisdn' => $dt->msisdn,
                    'sku' => $dt->sku,
                    'error' => 'El msisdn ya se encontraba iluminado'];

                  $dnsNA[] = $dt->msisdn;

                  $dt->status = 'E';
                  $dt->last_user_action = $user;
                  $dt->coo_date_action = date('Y-m-d H:i:s');
                  $dt->comment = 'El msisdn ya se encontraba iluminado';
                  $dt->save();
                }
              }
            }
          }
        }

        if (count($errors)) {
          try {
            Mail::to(explode(',', env('EMAILERRPRV')))->send(new NotifyErrorInventary($dataError, $errors));
          } catch (\Exception $e) {
            Log::error('No se pudo enviar correo notificación de error en inventario: Error: ' . $e->getMessage());
          }
        }

        return response()->json(['success' => true, 'dns_not_asigned' => $dnsNA, 'dns_reciclers' => $dnsRC]);
      }
    }

    return response()->json(['success' => false, 'msg' => 'No se pudo asignar el detalle de la caja.']);
  }

  public function listDNOOR(Request $request)
  {
    if (session('user_type') == 'vendor') {
      $userf = '';
      $dns = SellerInventory::getDNsWithAlertByUsers([session('user')]);
    } else {
      $userf = !empty($request->seller) ? $request->seller : false;

      if (!$userf) {
        $users = User::getParentsOneLevel(session('user'))->pluck('email')->toArray();
        $users[] = session('user');
      } else {
        $users[] = $userf;
      }
      $dns = SellerInventory::getDNsWithAlertByUsers($users);
    }

    return response()->view('inventory.listDnsOrangeRed', compact('dns', 'userf'));
  }

  public function viewListDN_OR(Request $request)
  {

    if (session('user_type') == 'vendor') {
      $userf = '';
      $dns = SellerInventory::getDNsWithAlertByUsers([session('user')], $request->typeColor);
    } else {
      $userf = !empty($request->seller) ? $request->seller : false;

      if (!$userf) {
        $users = User::getParentsOneLevel(session('user'))->pluck('email')->toArray();
        $users[] = session('user');
      } else {
        $users[] = $userf;
      }
      $dns = SellerInventory::getDNsWithAlertByUsers($users, $request->typeColor);
    }

    $html_list = view('inventory.tableDN_OR', compact('dns'))->render();

    return response()->json(['htmlCode' => $html_list, 'dns' => $dns, 'userf' => $userf]);
  }
/**
 * [chekingRequestStatus Muestra el detalle de una solicitud de cambio de status rojo a naranja dado un id de inventario y el usaurio logeado]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function chekingRequestStatus(Request $request)
  {
    $detail = false;

    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->id)) {
        $title = null;
        $msg = null;
        $responsable = null;
        $isCreate = History_inv_status::NotPresetRequest($request->id);
        if (!empty($isCreate)) {
          if ($isCreate->status == 'C') {
            $detail = true;
            $title = "En proceso";
            $msg = "El articulo aun se encuentra en espera de ser procesado por el administrador de netwey";
            $responsable = null;
          } elseif ($isCreate->status == 'R') {
            $detail = true;
            $title = "Rechazado";
            $msg = $isCreate->motivo_rechazo;
            $responsable = $isCreate->userAutorizador;
          } else {
            $detail = true;
            $title = "Procesado";
          }
        } else {
          $detail = true;
          $title = "No hay detalles de cambio de status de inventario";
        }

        $html_list = view('inventory.detailStatusinv', compact('detail', 'title', 'msg', 'responsable'))->render();

      } else {
        $html_list = view('inventory.detailStatusinv', compact('detail'))->render();
      }
    } else {
      $html_list = view('inventory.detailStatusinv', compact('detail'))->render();
    }
    return response()->json(['htmlCode' => $html_list]);
  }

  public static function downloadInvNoty(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $types = [
        'H' => 'InternetHogar',
        'T' => 'Telefonía',
        'M' => 'Mifi',
        'F' => 'Fibra'];

      if (empty($request->seller)) {
        $users = User::getParentsOneLevel(session('user'))->pluck('email')->toArray();
        $users[] = session('user');
      } else {
        $users[] = $request->seller;
      }

      $dns = SellerInventory::getDNsWithAlertByUsers($users, $request->typeColor);

      $fileName = 'inventario_notificado_' . date('Ymd');

      $headers = array(
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=" . $fileName . ".csv",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0",
      );

      $columns = array(
        'MSISDN',
        'Usuario',
        'Origen',
        'Notificación',
        'Tipo',
        'Artículo',
        'Fechadenotificación',
      );

      $callback = function () use ($dns, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($dns as $dn) {
          $data = [
            $dn->msisdn,
            $dn->name . ' ' . ($dn->last_name ?? ''),
            !empty($dn->date_red) ? $dn->origin : '-',
            !empty($dn->date_red) ? 'Roja' : 'Naranja',
            !empty($types[$dn->artic_type]) ? $types[$dn->artic_type] : 'Otro',
            $dn->title,
            !empty($dn->date_red) ? date('d-m-Y', strtotime($dn->date_red)) : date('d-m-Y', strtotime($dn->date_orange)),
          ];

          fputcsv($file, $data);
        }
        fclose($file);
      };

      return response()->stream($callback, 200, $headers);
    }

    return redirect()->route('dashboard');
  }

  public function preassignedInv()
  {
    $articles = Inventory::getConnect('R')
      ->select(
        'islim_inv_assignments_temp.id as id',
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_inv_arti_details.imei',
        'islim_inv_articles.artic_type as type',
        'islim_inv_assignments_temp.date_reg'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_inv_assignments_temp',
        function ($join) {
          $join->on(
            'islim_inv_assignments_temp.inv_arti_details_id',
            'islim_inv_arti_details.id'
          )
            ->where('islim_inv_assignments_temp.user_email', session('user'))
            ->where('islim_inv_assignments_temp.status', 'P');
        }
      )
      ->where([
        ['islim_inv_arti_details.status', 'A']])
      ->get();

    return view('inventory.preAssignedAR', compact('articles'));

  }

  public function rejectPreassignedInv(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->id)) {

        $sit = SellerInventoryTemp::getConnect('W')
          ->where('id', $request->id)
          ->where('user_email', session('user'))
          ->where('status', 'P')
          ->first();

        if ($sit) {

          $sit->status = 'R';
          $sit->date_status = date('Y-m-d H:i:s', time());
          if (!empty($request->reason)) {
            $sit->reason_reject = $request->reason;
            $sit->reject_notification_view = 'N';
          }
          $sit->save();

          return response()->json(['success' => true, 'id' => $request->id, 'msg' => 'Tu solicitud ha sido procesada con exito']);
        }
      }
    }

    return response()->json(['success' => false, 'msg' => 'No se pudo procesar tu solicitud...']);
  }

  public function acceptPreassignedInv(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->id)) {

        $user = User::find(session('user'));

        if ($user) {
          if (!empty($user->parent_email)) {
            $sit = SellerInventoryTemp::getConnect('W')
              ->where('id', $request->id)
              ->where('user_email', session('user'))
              ->where('status', 'P')
              ->first();

            if ($sit) {

              $invs = SellerInventory::getConnect('W')
                ->where('inv_arti_details_id', $sit->inv_arti_details_id)
                ->where('status', 'A')
                ->first();

              SellerInventory::getConnect('W')
                ->where('inv_arti_details_id', $sit->inv_arti_details_id)
                ->where('status', 'A')
                ->update([
                  'status' => "T"]);

              $ifExistsAssig = SellerInventory::getAsignmentUser($sit->inv_arti_details_id, session('user'));

              $date_reg = date("Y-m-d H:i:s");
              if (!empty($invs)) {
                if (!empty($invs->date_orange)) {
                  $date_reg = Carbon::createFromFormat('Y-m-d H:i:s', $invs->date_orange)->subDays(20)->format('Y-m-d H:i:s');
                }
              }

              if (!empty($ifExistsAssig)) {

                SellerInventory::getConnect('W')
                  ->where([
                    ['inv_arti_details_id', $sit->inv_arti_details_id],
                    ['users_email', session('user')],
                  ])
                  ->update([
                    'date_reg' => $date_reg,
                    'status' => 'A',
                    'obs' => 'El vendedor acepto la asignacion',
                    'date_orange' => !empty($invs) ? $invs->date_orange : null,
                    'date_red' => null,
                    'last_assignment' => date('Y-m-d H:i:s'),
                    'last_assigned_by' => $sit->assigned_by,
                  ]);
              } else {
                SellerInventory::getConnect('W')
                  ->insert([
                    'users_email' => session('user'),
                    'inv_arti_details_id' => $sit->inv_arti_details_id,
                    'date_reg' => $date_reg,
                    'status' => 'A',
                    'obs' => 'El vendedor acepto la asignacion',
                    'date_orange' => !empty($invs) ? $invs->date_orange : null,
                    'last_assignment' => date('Y-m-d H:i:s'),
                    'last_assigned_by' => $sit->assigned_by,
                  ]);
              }

              $sit->status = 'A';
              $sit->date_status = date('Y-m-d H:i:s', time());
              $sit->save();

              $band = true;
              if (!empty($invs)) {
                if ($invs->users_email == session('user')) {
                  $band = false;
                }
              }

              if ($band) {
                $inventory = Inventory::getConnect('R')->find($sit->inv_arti_details_id);

                SellerInventoryTrack::setInventoryTrack(
                  $sit->inv_arti_details_id,
                  !empty($invs) ? $invs->users_email : null,
                  !empty($invs) ? null : $inventory->warehouses_id,
                  session('user'),
                  null,
                  $sit->assigned_by,
                  'El vendedor acepto la asignacion'
                );
              }

              return response()->json(['success' => true, 'id' => $request->id, 'msg' => 'Tu solicitud ha sido procesada con exito']);
            }
          }
        }
      }
    }

    return response()->json(['success' => false, 'msg' => 'No se pudo procesar tu solicitud. Verifica que posees registrado supervisor (CI523)']);
  }

  public function preassignedStatus()
  {

    $users = User::getParentsOneLevel(session('user'))->pluck('email')->toArray();

    $desde = date("Y-m-d", strtotime("-30 days", time())) . " 00:00:00";
    $hasta = date("Y-m-d", time()) . " 23:59:59";

    $articles = Inventory::getConnect('R')
      ->select(
        'islim_inv_assignments_temp.id as id',
        DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) as vendor'),
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_inv_arti_details.imei',
        'islim_inv_articles.artic_type as type',
        'islim_inv_assignments_temp.date_reg',
        'islim_inv_assignments_temp.status',
        'islim_inv_assignments_temp.reason_reject'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_inv_assignments_temp',
        function ($join) use ($users, $desde, $hasta) {
          $join->on(
            'islim_inv_assignments_temp.inv_arti_details_id',
            'islim_inv_arti_details.id'
          )
            ->whereIn('islim_inv_assignments_temp.user_email', $users)
            ->where('islim_inv_assignments_temp.status', '<>', 'T')
            ->where('islim_inv_assignments_temp.date_reg', '>=', $desde)
            ->where('islim_inv_assignments_temp.date_reg', '<=', $hasta);
        }
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_inv_assignments_temp.user_email'
      )
      ->where([
        ['islim_inv_arti_details.status', 'A']])
      ->get();

    return view('inventory.preAssignedStatus', compact('articles'));
  }

  public function changeStatus(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->photo)) {
        $path = 'inventary/evidence-photo/';
        $photo = $request->file('photo');
        $photoPath = $path . uniqid() . time() . '.' . $photo->getClientOriginalExtension();

        Storage::disk('s3')->put(
          $photoPath,
          file_get_contents($photo->getPathname()),
          'public'
        );

        $urlPhoto = (String) Storage::disk('s3')->url($photoPath);

        /*Registro la peticion en el historial de cambio de status*/
        $saveHistory = History_inv_status::getConnect('W');
        $saveHistory->users_email = session('user');
        $saveHistory->inv_arti_details_id = session('id_InvRed');
        $saveHistory->date_reg = date('Y-m-d H:i:s', time());

        $sendMail = true;
        $msjTrack = '';
        if (session('typeScan') == 'A') {
          $saveHistory->status = 'P';
          $msjTrack = 'El usuario scaneo para cambio de status de inventario a naranja';

        } else {
          //Reviso si es SimCard ya que la simcard se dificulta la lectura del codigo de barras, la primera vez se cambia a naranja, si persiste el cambio a naranja se debe revisar.
          //
          $saveHistory->status = 'C';

          $isSimCard = Inventory::getTypeArticle(session('id_InvRed'));
          if (!empty($isSimCard)) {
            if ($isSimCard->Category_id == 2) {
              //Revisamos si ya hubo un status rojo previamente si no se notifica al correo su revision por netwey

              $cantRed = History_inv_status::LastRedStatus(session('id_InvRed'));
              if (empty($cantRed)) {
                //Log::info('PRIMERA VEZ');
                //if ($cantRed->total_red == 0) {
                //Es la primera vez de simcard en estar en rojo
                $sendMail = false;
                $saveHistory->status = 'P';
                $msjTrack = 'Es una tarjeta SimCard que se paso a naranja por primera vez sin poderse escanear';
                // }
              }
            }
          }
          // Envio un email de notificacion para ser revisado
        }

        if ($saveHistory->status == 'P') {
          //Los de scaneo automatico y simCard por primera vez
          SellerInventory::setValidMotive();
          //se le quita el inventario al coordinador y se le da al vendedor de vuelta
          if (session('user_type') == 'vendor') {
            //Se hace traking de inventario
            $coord = User::getParentUser2(session('user'));

            SellerInventoryTrack::setInventoryTrack(
              session('id_InvRed'),
              !empty($coord) ? $coord->parent_email : null,
              null,
              session('user'),
              null,
              null,
              $msjTrack
            );
          }
        }

        if (!empty($urlPhoto)) {
          $saveHistory->url_evidencia = $urlPhoto;
        }
        $saveHistory->color_destino = 'N';
        $saveHistory->save();

        if (session('typeScan') == 'M' && $sendMail) {

          $Infodn = Inventory::getDnsById(session('id_InvRed'));
          $InfoUser = User::getUserByEmail(session('user'));

          if (!empty($Infodn) && !empty($InfoUser)) {
            try {
              Mail::to(explode(',', env('LIST_MAIL_CHANGE_STATUS_INV')))->send(new NotifyChangeStatusInv($saveHistory, $Infodn, $InfoUser));
            } catch (\Exception $e) {
              Log::error('No se pudo enviar correo notificación en cambio de status de inventario: Error: ' . $e->getMessage());
            }
          }
        }
        return ['success' => true, 'msg' => 'Se registro la solicitud exitosamente'];
      }
    }
    return response()->json(['success' => false, 'msg' => 'No se pudo procesar tu solicitud..']);
  }

  public function verifyDnStatus(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $fail = false;

      //Obtendo el id del detalle del articulo y que este disponible a la venta
      $infInv = Inventory::getDataDn($request->dn);
      if (!empty($infInv)) {
        if ($infInv->status == 'A') {
          //Reviso si lo tengo asignado
          $isAsigne = SellerInventory::getAsignmentUserRED($infInv->id, session('user'));
          if (!empty($isAsigne)) {
            //Reviso si esta en rojo
            $dnChange = SellerInventory::isValidChangeStatus($request->dn);
            if (!empty($dnChange)) {

              //Verifico que no este aun como creada la solicitud de cambio de status de inventario
              $present = History_inv_status::NotPresetRequest($dnChange->inv_arti_details_id, 'C');
              $permit = false;
              if (empty($present)) {
                $permit = true;
              } elseif ($present->status != 'C') {
                $permit = true;
              }
              if ($permit) {
                // type_scan
                // true manual
                // false scaneado
                $bander = filter_var($request->type, FILTER_VALIDATE_BOOLEAN);

                session([
                  'id_InvRed' => $dnChange->inv_arti_details_id,
                  'typeScan' => (boolval($bander)) ? 'M' : 'A',
                ]);

                return response()->json(['success' => true, 'msg' => 'DN permitido']);
              } else {
                $fail = true;
                $msg = 'DN aun en espera de ser procesado por netwey para cambio de status. Solicitud realizada el ' . $present->date_reg;
              }
            } else {
              $fail = true;
              $msg = "El DN " . $request->dn . " no esta en status Rojo. Se recuerda que este proceso es para pasar de Status Rojo a Naranja ";
            }
          } else {
            $fail = true;
            $Asigne = SellerInventory::getAsignmentUserRED($infInv->id);
            $inv_hard = '';
            if (!empty($Asigne)) {
              $inv_hard = ". La solicitud de cambio de status para este DN lo debe realizar " . $Asigne->user_red;
            }
            $msg = "El DN " . $request->dn . " no pertenece a " . session('user') . $inv_hard;
          }
        } else {
          $fail = true;
          $msg = "El DN " . $request->dn . " no esta disponible para la venta";
        }
      } else {
        $fail = true;
        $msg = "El DN " . $request->dn . " no existe en inventario";
      }
    }
    if ($fail) {
      return response()->json(['success' => false, 'msg' => $msg]);
    }
    return response()->json(['success' => false, 'msg' => 'No se pudo procesar tu solicitud.']);
  }
}
