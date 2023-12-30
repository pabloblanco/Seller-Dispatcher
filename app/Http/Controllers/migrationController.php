<?php

namespace App\Http\Controllers;

use App\Models\ArticlePack;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Pack;
use App\Models\PackPrices;
use App\Models\Product;
use App\Models\SellerInventory;
use App\Models\Service;
use App\Utilities\ProcessRegAlt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class migrationController extends Controller
{
  public function migrations()
  {
    $hasinv = false;
    $packs = false;
    //Consultando inventario para migracion
    $inv = SellerInventory::getArticsAssign(
      session('user'),
      'M'
    );

    if (count($inv)) {
      $idInvAssig = $inv->pluck('inv_arti_details_id')->toArray();

      $articlesAssig = Inventory::getArticsByIds(
        $idInvAssig,
        'M'
      );

      if (count($articlesAssig)) {
        $idDetailArti = $articlesAssig->pluck('inv_article_id')->toArray();

        $articles = Product::getProductsById(
          $idDetailArti,
          'M'
        );

        if ($articles) {
          $idArticles = $articles->pluck('id')->toArray();

          $packsRel = ArticlePack::getRelationPack(
            $idArticles,
            'N'
          );

          if (count($packsRel)) {
            $idPacks = $packsRel->pluck('pack_id')->toArray();

            $packs = Pack::getPacksById(
              $idPacks,
              true,
              false,
              false,
              'MH',
              'Y'
            );

            if (count($packs)) {
              foreach ($packs as $pack) {
                $pack->service = PackPrices::getServiceByPackAndBroad(
                  $pack->id,
                  [],
                  'MH'
                );

                //Validando si el pack tiene fecha inicio y/o fecha fin
                $dateOK = true;
                if (!empty($pack->date_ini) && !empty($pack->date_end)) {
                  if (time() < strtotime($pack->date_ini) || time() > strtotime($pack->date_end)) {
                    $dateOK = false;
                  }
                } elseif (!empty($pack->date_ini)) {
                  if (time() < strtotime($pack->date_ini)) {
                    $dateOK = false;
                  }
                } elseif (!empty($pack->date_end)) {
                  if (time() > strtotime($pack->date_end)) {
                    $dateOK = false;
                  }
                }

                if (!empty($pack->service) && $dateOK) {
                  $articlesPack = ArticlePack::getArticsByAssigAndPack(
                    $pack->id,
                    $idInvAssig
                  );

                  if (count($articlesPack)) {
                    foreach ($articlesPack as $artic) {
                      $pd = Product::getProductById($artic->inv_article_id);
                      if (!empty($pd)) {
                        $artic->titleP = $pd->title;
                        $artic->descriptionP = $pd->description;
                        $hasinv = true;
                      }
                    }
                    $pack->articles = $articlesPack;
                  }
                }
              }
            }
          }
        }
      }
    }

    if (!$hasinv) {
      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', 'No tienes inventario disponible para migraciones.');
    }

    return view('migrations.index', compact('hasinv', 'packs'));
  }

  public function findClientForMigration(Request $request)
  {
    if (!empty($request->dn)) {
      $client = Client::getClientByMSISDN($request->dn, 'H');

      if (!empty($client) && ($client->status != 'T' && $client->status != 'I')) {
        $html = view('migrations.client', compact('client'))->render();

        return response()->json(['error' => false, 'html' => $html]);
      }
    }

    return response()->json(['error' => true]);
  }

  public function updateClient(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $input = $request->all();
      $this->validate($request, [
        'name' => 'required',
        'last_name' => 'required',
        'phone' => 'required',
        'dni' => 'required']);

      $client = Client::getClientINEorDN($input['dni']);

      if (!empty($client)) {
        $errm = '';
        if ($input['phone'] != $client->phone_home) {
          $existPhone = Client::getClientINEorDN(false, $input['phone']);

          if (!empty($existPhone)) {
            $errm .= 'El número teléfono ya se encuentra registrado en el sistema.';
          }
        }

        if (!empty($input['email']) && $input['email'] != $client->email) {
          $existEmail = Client::getClientINEorDN(false, false, $input['email']);

          if (!empty($existEmail)) {
            $errm .= !empty($errm) ? ', el email ya se encuentra registrado en el sistema.' : 'El email ya se encuentra registrado en el sistema.';
          }
        }

        if (!empty($errm)) {
          return response()->json(['error' => true, 'msg' => $errm, 'icon' => 'alert-danger']);
        }

        $data = [
          'name' => $input['name'],
          'last_name' => $input['last_name'],
          'phone_home' => $input['phone'],
        ];

        if (!empty($input['phone2'])) {
          $data['phone'] = $input['phone2'];
        }

        if (!empty($input['email'])) {
          $data['email'] = $input['email'];
        }

        if (!empty($input['address'])) {
          $data['address'] = $input['address'];
        }

        Client::getConnect('W')
          ->where('dni', $input['dni'])
          ->update($data);

        return response()->json(['error' => false, 'msg' => "Datos del cliente actualizados!", 'icon' => 'alert-success']);
      }

      return response()->json(['error' => true, 'msg' => 'No se pudo editar el usuario', 'icon' => 'alert-danger']);
    }
  }

  public function doMigration(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $errormsg = 'No se pudo realizar la migración.';

      if (!empty($request->msisdn) && !empty($request->pack) && !empty($request->service) && !empty($request->msisdn_old)) {
        //Validando si el dn nuevo esta en el inventario del vendedor
        $isAssig = SellerInventory::isAssignedDn($request->msisdn, session('user'));

        $artiDetail = Inventory::getArticByIdAndDN($request->pack, $request->msisdn);

        //Validando que el dn que se queire migrar exista
        $client = Client::getClientByMSISDN($request->msisdn_old, 'H');

        if (!empty($isAssig) && !empty($client) && ($client->status != 'T' && $client->status != 'I') && !empty($artiDetail)) {
          //Validando que el pack sea de migración
          $packs = Pack::getPacksById(
            [$request->pack],
            true,
            false,
            false,
            'MH',
            'Y'
          );

          //Validando que el pack este relacionado con el servicio enviado
          $packP = PackPrices::getServiceByPack($request->pack, $request->service);
          $service = Service::getService($request->service);
          $plan = Pack::getInfoPack($request->pack, 'CO');

          if (count($packs) && !empty($packP) && !empty($plan)) {
            $result = ProcessRegAlt::doProcessRegAlt(
              'mifi-h', /*1*/
              $request->msisdn, /*2*/
              !empty($client->address) ? $client->address : false, /*3*/
              !empty($client->lat) ? $client->lat : false, /*4*/
              !empty($client->lng) ? $client->lng : false, /*5*/
              $service, /*6*/
              $artiDetail, /*7*/
              'CO', /*8*/
              uniqid('CMB') . time(), /*9*/
              $client, /*10*/
              $plan, /*11*/
              false, /*12*/
              false, /*13*/
              false, /*14*/
              false, /*15*/
              false, /*16*/
              false, /*17*/
              false, /*18*/
              'C', /*19*/
              false, /*20*/
              $request->msisdn_old/*21*/
            );

            if (!$result['success'] && !empty($result['message'])) {
              $errormsg = $result['message'];
            } else {
              return response()->json(['error' => false]);
            }
          } else {
            $errormsg = 'Pack no válido.';
          }
        } else {
          $errormsg = 'No se consiguio el msisdn.';
        }
      }

      Log::error('Ocurrio un error al intentar migrar el DN: ' . $errormsg, $request->all());

      return response()->json(['error' => true, 'msg' => $errormsg]);
    }
  }
}
