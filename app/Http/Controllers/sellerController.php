<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Storage;
use App\Models\ArticlePack;
use App\Models\AssignedSales;
use App\Models\AssignedSalesDetail;
use App\Models\Broadband;
use App\Models\Client;
use App\Models\ClientNetwey;
use App\Models\ConfigIstallments;
use App\Models\Coppel;
use App\Models\IdentityVerification;
use App\Models\Inventory;
use App\Models\Pack;
use App\Models\PackEsquema;
use App\Models\PackPrices;
use App\Models\PayInstallment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentDetail;
use App\Models\SellerInventory;
use App\Models\Service;
use App\Models\TelephoneCompany;
use App\Models\TelmovPay;
use App\Models\TokensInstallments;
use App\Models\User;
use App\Utilities\Altan;
use App\Utilities\APIClient;
use App\Utilities\Common;
use App\Utilities\CoppelPay;
use App\Utilities\Google;
use App\Utilities\ProcessRegAlt;
use App\Utilities\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class sellerController extends Controller
{
  public function index(Request $request)
  {
    $lock = User::getOnliyUser(session('user'));

    return view('seller.index', compact('lock'));
  }

  public function comparative()
  {
    return view('seller.comparative');
  }

  public function validQtyDns(Request $request, $type = false)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->ine)) {
        $types = [
          'mov' => 'T',
          'home' => 'H',
          'mifi' => 'M',
          'mifi-h' => 'MH',
        ];

        $qty = ClientNetwey::getTypeDns(
          $request->ine,
          !empty($types[$type]) ? $types[$type] : false
        );

        if (
          $qty !== false &&
          (($type == 'mov' && count($qty) < env('LIMIT_DEVICE_M', 5)) ||
            ($type == 'mifi' && count($qty) < env('LIMIT_DEVICE_MI', 2)) ||
            ($type == 'home' && count($qty) < env('LIMIT_DEVICE', 2)) ||
            ($type == 'mifi-h' && count($qty) < env('LIMIT_DEVICE_MH', 2)))
        ) {
          return response()->json(['error' => false]);
        }
      }

      return response()->json(['error' => true]);
    }

    return redirect()->route('dashboard');
  }

  public function validImei(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      session(['device' => null]);

      if (!empty($request->imei)) {
        $res = Altan::validIMEI($request->imei);

        if ($res['success']) {
          session(['device' => $res['data']]);
          return response()->json(['error' => false, 'data' => $res['data']]);
        }
      }

      return response()->json(['error' => true]);
    }

    return redirect()->route('dashboard');
  }

  public function showClientN(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dni)) {
        $client = Client::getClientByDNI($request->dni);

        if (!empty($client)) {
          $client->status = true;
          $companys = TelephoneCompany::getCompanys();
          $html = view('seller.find', compact('client', 'companys'))->render();
          return response()->json(['error' => false, 'html' => $html, 'dni' => $client->dni]);
        }
      }

      return response()->json(['error' => true]);
    }

    return redirect()->route('dashboard');
  }

  /*Busca un cliente dado un dn (contacto, netwey o nombre) y retorna un json con los datos*/
  public function findClient(Request $request, $search = false)
  {
    if (!empty($request->search)) {
      return response()->json(Client::searchClients($request->search, 20));
    }

    return response()->json([]);
  }

  /*
  Devuelve cantidad de modems en abono vendidos por el usuario loguado con pagos vencidos
   */
  /*private function getExpiredPayInstallments($user, $type, $config)
  {
  $sales = SaleInstallment::getOnlyPendingSales($user, $type);

  if ($sales->count()) {
  $contExp = 0;

  foreach ($sales as $sale) {
  if (empty($config) || $config->id != $sale->config_id) {
  $config = ConfigIstallments::getConfigById($sale->config_id);
  }

  $today = time();

  //Verificando si la fecha limite para pago de la proxima cuota expiro
  $dateSale = strtotime(
  '+ ' . ($config->days_quote * $sale->quotes) . ' days',
  strtotime($sale->date_reg_alt)
  );

  if ($today > $dateSale) {
  $contExp++;
  }
  }

  return $contExp;
  }

  return 0;
  }*/

  /*
  Verifica si el usuario autenticado puede vender en abono tomando en cuenta las cuotas vencidas
   */
  /*private function canSaleInstallment($conf = false)
  {
  $canSaleIns = false;
  if ($conf) {
  $limitS = $conf->m_permit_s;
  $limitC = $conf->m_permit_c;

  $canSaleIns = true;
  if (session('user_type') == 'vendor') {
  $salesExpInst = Seller::getExpiredPayInstallments(
  session('user'),
  'vendor',
  $conf
  );

  if ($salesExpInst >= $limitS) {
  $canSaleIns = false;
  }

  if ($canSaleIns) {
  $parent = User::getParentUser(session('user'));

  if (!empty($parent)) {
  $salesExpInst = Seller::getExpiredPayInstallments(
  $parent->parent_email,
  'coordinador',
  $conf
  );

  if ($salesExpInst >= $limitC) {
  $canSaleIns = false;
  }
  }
  }
  } else {
  $salesExpInst = Seller::getExpiredPayInstallments(
  session('user'),
  'coordinador',
  $conf
  );

  if ($salesExpInst >= $limitC) {
  $canSaleIns = false;
  }
  }
  }

  return $canSaleIns;
  }*/

  public function showPackMov(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $packs = new \stdClass;
      $typeSell = (!empty($request->type) && $request->type == 'mifi') ? 'M' : 'T';
      $typeArtic = !empty($request->type) ? $request->type : 'mov';
      $isBandTE = !empty($request->isBandTE) ? $request->isBandTE : null;
      $typePayment = !empty($request->typePayment) ? $request->typePayment : null;

      $brand = isset($request->brand) ? $request->brand : false;
      $isOptionTelmov = isset($request->isOptionTelmov) ? $request->isOptionTelmov : false;

      if ($isOptionTelmov) {
        //Se debera buscar el pack que se le ofrecio en el proceso del contrato de telmovPay y lanzarlo a la vista de pack
        //
        $ineClient = $request->ine;
        $resTelmov = TelmovPay::inProcess(session('user'), ['CF'], $request->ine);
        if (!empty($resTelmov)) {
          //tengo msisdn, saco el articulo
          //tengo el pack
          //tengo el servicio
          //
          $infoPack = Pack::getActivePackById($resTelmov->pack_id);
          if (!empty($infoPack)) {
            $pack[0] = new \stdClass;
            $pack[0]->id = $resTelmov->pack_id;
            $pack[0]->sale_type = $infoPack->sale_type;
            $pack[0]->config = null;
            $pack[0]->is_visible_coppel = 'N';
            $pack[0]->valid_identity = 'N';
            $pack[0]->title = $infoPack->title;
            $pack[0]->description = $infoPack->description;
            $pack[0]->enganche = $resTelmov->initial_amount;

            $infoServ = PackPrices::getServiceByPackAndType($resTelmov->pack_id, 'T');
            if (!empty($infoServ)) {
              $pack[0]->servicio = new \stdClass;
              $pack[0]->servicio->id = $infoServ->id;
              $pack[0]->servicio->type = $infoServ->type;
              $pack[0]->servicio->title = $infoServ->title;
              $pack[0]->servicio->description = $infoServ->description;
              $pack[0]->servicio->price_pack = $infoServ->price_pack;
              $pack[0]->servicio->price_serv = $infoServ->price_serv;

              $inv = new \stdClass;
              $inv->msisdn = $resTelmov->msisdn;
              $pack[0]->servicio->articles[0] = $inv;

              $packs->status = true;
              $packs->packs = $pack;
            } else {
              $packs->status = false;
              $packs->message = "Se presento un error en la obtener la informacion del servicio";
            }
          } else {
            $packs->status = false;
            $packs->message = "Se presento un error en la obtener la informacion del paquete";
          }
        } else {
          $packs->status = false;
          $packs->message = "No hay un registro de emparejamiento de equipos con TelmovPay";
        }

        $html = view('seller.packs', compact('packs', 'isOptionTelmov'))->render();
        return response()->json(['success' => true, 'html' => $html]);
      }

      $isport = false;
      if (!empty($inputs['isPort']) && $inputs['isPort'] == 'true') {
        $isport = true;

        /*$isClient = ClientNetwey::isClient($inputs['dnport']);

      if($isClient){
      $packs->status = false;
      $packs->message = "El número a portar ya se encuentra registrado en Netwey.";

      $html = view('seller.packs', compact('packs', 'typeArtic'))->render();

      return response()->json(['success' => true, 'html' => $html]);
      }*/
      }

      $packs = Seller::SearchPack($typeSell, $typeArtic, $isBandTE, $typePayment, $brand, $isport);

//////////
      /*
      $invAssig = SellerInventory::getArticsAssign(session('user'), $typeSell);

      if (count($invAssig) > 0 || session('org_type') == 'R') {
      if (session('org_type') == 'R') {
      $articlesAssig = Inventory::getArticsByWh(session('wh'), $typeSell);

      $idInvAssig = $articlesAssig->pluck('id')->toArray();
      } else {
      $idInvAssig = $invAssig->pluck('inv_arti_details_id')->toArray();

      $articlesAssig = Inventory::getArticsByIds($idInvAssig, $typeSell);
      }

      if (count($articlesAssig) > 0) {
      $idDetailArti = $articlesAssig->pluck('inv_article_id')->toArray();

      $articles = Product::getProductsById(
      $idDetailArti,
      $typeSell,
      $typeArtic == 'mov-ph' ? env('SMARTCATID') : ($typeArtic == 'mifi' ? false : env('SIMCATID')),
      $brand
      );

      if (count($articles) > 0) {
      $idArticles = $articles->pluck('id')->toArray();

      $packsRel = ArticlePack::getRelationPack(
      $idArticles,
      session('org_type')
      );

      if (count($packsRel) > 0) {
      if (session('user_type') == 'vendor') {
      $parent = User::getParentUser(session('user'));
      }

      $tokens = TokensInstallments::getTokensByUser(
      !empty($parent) ? $parent->parent_email : session('user')
      );

      $infoUser = User::getInfoUser(
      !empty($parent) ? $parent->parent_email : session('user')
      );

      //Obteniendo configuracion activa para venta en abonos
      $conf = ConfigIstallments::getActiveConf();

      //Verificando condiciones de pagos vencidos para poder vender en abono
      $canSaleIns = self::canSaleInstallment($conf);

      //Quita los packs tipo abono si el coordinador no tiene tokens
      $whtInst = false;
      if (empty($tokens) || !$canSaleIns) {
      $whtInst = true;
      }

      $idPacks = $packsRel->pluck('pack_id')->toArray();

      $packs = Pack::getPacksById(
      $idPacks,
      $whtInst,
      $isport,
      $isBandTE,
      false,
      'N',
      (!empty($infoUser) && !empty($infoUser->esquema_comercial_id)) ? $infoUser->esquema_comercial_id : false,
      $typePayment
      );

      if (count($packs) > 0) {
      $idP = $packs->pluck('id')->toArray();

      $services = PackPrices::getServicesByPacks($idP);

      if (count($services) > 0) {
      $packs->status = true;
      $plans = [];
      foreach ($packs as $pack) {
      $pack->servicio = PackPrices::getServiceByPackAndType(
      $pack->id,
      $typeSell
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

      if (!empty($pack->servicio) && $dateOK) {
      $pack->servicio->articles = ArticlePack::getArticsByAssigAndPack(
      $pack->id,
      $idInvAssig
      );

      if (count($pack->servicio->articles) > 0) {
      $artarr = [];
      foreach ($pack->servicio->articles as $article) {
      $title = Product::getProductById($article->inv_article_id);

      $article->title = $title->title;
      $artarr[] = $article;
      }
      $pack->servicio->articles = $artarr;
      }

      //Guardando configuracion, si es un pack a cuotas
      if ($pack->sale_type == 'Q') {
      if (!empty($conf)) {
      $pack->config = $conf;
      }
      }

      $plans[] = $pack;
      }
      }

      if (count($plans)) {
      $packs->packs = $plans;
      } else {
      $packs->status = false;
      $packs->message = "Sin pack activos.";
      }
      } else {
      $packs->status = false;
      $packs->message = "Pack sin servicios y precios asignados.";
      }
      } else {
      $packs->status = false;
      $packs->message = "Sin pack activos.";
      }
      } else {
      $packs->status = false;
      $packs->message = "Inventario no asignado a un pack.";
      }
      } else {
      $packs->status = false;
      $packs->message = "No se consiguiron los articulos del inventario.";
      }
      } else {
      $packs->status = false;
      $packs->message = "No se consiguiron los articulos del inventario.";
      }
      } else {
      $packs->status = false;
      $packs->message = "Vendedor sin inventario.";
      }
       */
/////////////////

      $html = view('seller.packs', compact('packs', 'isOptionTelmov'))->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function showPacks(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();
      $packs = new \stdClass;
      $typeSell = $request->type == 'mifi-h' ? 'MH' : 'H';

      if (!empty($inputs['lat']) && !empty($inputs['lon'])) {
        /*if (env('APP_ENV', 'local') == 'local') {
        $inputs['lon'] = '-99.1774201';
        $inputs['lat'] = '19.3952801';
        }*/

        $response = Altan::serviceability(
          $inputs['lat'],
          $inputs['lon'],
          empty($inputs['address']) ? '' : $inputs['address'],
          $typeSell == 'MH'
        );

        if ($response['success']/*|| ($typeSell == 'MH' && !empty($response['service']) && ($response['service'] == 'E-BLK' || $response['service'] == 'E-RES'))*/) {
          if ($typeSell == 'H') {
            $broadband = Common::getWide(!empty($response['data']) ? $response['data'] : false);
          } else {
            $broadband = 0;
          }

          $broadbands = Broadband::getBroadBand($broadband);

          if (count($broadbands) > 0 || $typeSell == 'MH') {
            $broadstrings = count($broadbands) ? $broadbands->pluck('broadband')->toArray() : [];

            $invAssig = SellerInventory::getArticsAssign(
              session('user'),
              $typeSell == 'MH' ? 'M' : $typeSell
            );

            if (count($invAssig) > 0 || session('org_type') == 'R') {
              if (session('org_type') == 'R') {
                $articlesAssig = Inventory::getArticsByWh(
                  session('wh'),
                  $typeSell == 'MH' ? 'M' : $typeSell
                );

                $idInvAssig = $articlesAssig->pluck('id')->toArray();
              } else {
                $idInvAssig = $invAssig->pluck('inv_arti_details_id')->toArray();

                $articlesAssig = Inventory::getArticsByIds(
                  $idInvAssig,
                  $typeSell == 'MH' ? 'M' : $typeSell
                );
              }

              if (count($articlesAssig) > 0) {
                $idDetailArti = $articlesAssig->pluck('inv_article_id')->toArray();

                $articles = Product::getProductsById(
                  $idDetailArti,
                  $typeSell == 'MH' ? 'M' : $typeSell
                );

                if (count($articles) > 0) {
                  $idArticles = $articles->pluck('id')->toArray();

                  $packsRel = ArticlePack::getRelationPack(
                    $idArticles,
                    session('org_type')
                  );

                  if (count($packsRel) > 0) {
                    if (session('user_type') == 'vendor') {
                      $parent = User::getParentUser(session('user'));
                    }

                    $tokens = TokensInstallments::getTokensByUser(
                      !empty($parent) ? $parent->parent_email : session('user')
                    );

                    $infoUser = User::getInfoUser(
                      !empty($parent) ? $parent->parent_email : session('user')
                    );

                    //Obteniendo configuracion activa para venta en abonos
                    $conf = ConfigIstallments::getActiveConf();

                    //Verificando condiciones de pagos vencidos para poder vender en abono
                    $canSaleIns = Seller::canSaleInstallment($conf);

                    //Quita los packs tipo abono si el coordinador no tiene tokens
                    $whtInst = false;
                    if (empty($tokens) || !$canSaleIns || !showMenu(['DSI-DSE'])) {
                      $whtInst = true;
                    }

                    $idPacks = $packsRel->pluck('pack_id')->toArray();

                    $packs = Pack::getPacksById(
                      $idPacks,
                      $whtInst,
                      false,
                      false,
                      $typeSell,
                      'N',
                      (!empty($infoUser) && !empty($infoUser->esquema_comercial_id)) ? $infoUser->esquema_comercial_id : false
                    );

                    if (count($packs) > 0) {
                      $idP = $packs->pluck('id')->toArray();

                      $services = PackPrices::getServicesByPacks($idP);

                      if (count($services) > 0) {
                        $packs->status = true;
                        $plans = [];
                        foreach ($packs as $pack) {
                          $pack->servicio = PackPrices::getServiceByPackAndBroad(
                            $pack->id,
                            $broadstrings,
                            $typeSell == 'MH' ? 'MH' : false
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

                          if (!empty($pack->servicio) && $dateOK) {
                            $pack->servicio->articles = ArticlePack::getArticsByAssigAndPack(
                              $pack->id,
                              $idInvAssig
                            );

                            if (count($pack->servicio->articles) > 0) {
                              $artarr = [];
                              foreach ($pack->servicio->articles as $article) {
                                $title = DB::table('islim_inv_articles')
                                  ->select('title')
                                  ->where('id', $article->inv_article_id)
                                  ->first();

                                $article->title = $title->title;
                                $artarr[] = $article;
                              }
                              $pack->servicio->articles = $artarr;
                            }

                            //Guardando configuracion, si es un pack a cuotas
                            if ($pack->sale_type == 'Q') {
                              if (!empty($conf)) {
                                $pack->config = $conf;
                              }
                            }

                            $plans[] = $pack;
                          }
                        }

                        if (count($plans)) {
                          $packs->packs = $plans;
                        } else {
                          $packs->status = false;
                          $packs->message = "sin pack activos(1).";
                        }
                      } else {
                        $packs->status = false;
                        $packs->message = "pack sin servicios y precios asignados.";
                      }
                    } else {
                      $packs->status = false;
                      $packs->message = "sin pack activos(2).";
                    }
                  } else {
                    $packs->status = false;
                    $packs->message = "Inventario no asignado a ningun pack.";
                  }
                } else {
                  $packs->status = false;
                  $packs->message = "No se consiguiron los articulos del inventario.";
                }
              } else {
                $packs->status = false;
                $packs->message = "No se consiguiron los articulos del inventario.";
              }
            } else {
              $packs->status = false;
              $packs->message = "Vendedor sin inventario.";
            }
          } else {
            $packs->status = false;
            $packs->message = "No se puede mostrar los packs.";
          }
        } else {
          $packs->status = false;
          $packs->message = $response['msg'];
        }
      } else {
        $packs->status = false;
        $packs->message = "No se puede mostrar los packs.";
      }

      $isOptionTelmov = false;
      $html = view('seller.packs', compact('packs', 'isOptionTelmov'))->render();

      return response()->json(['success' => true, 'html' => $html]);
    }
  }

  public function validNumberSale(Request $request)
  {
    if ($request->isMethod('post')) {
      $saleP = new \stdClass;
      $saleP->error = true;
      $msisdn = $request->input('msisdn');
      $pack = base64_decode($request->input('pack'));

      if (!empty($msisdn)) {
        $isAssig = SellerInventory::isAssignedDn($msisdn, session('user'));

        if (empty($isAssig)) {
          $saleP->msg = "El msisdn no existe.";
          return response()->json($saleP);
        }

        $artiDetail = Inventory::getArticByIdAndDN($pack, $msisdn);

        $pack = Pack::getActivePackById($pack);

        $is_wait = SaleInstallment::isAvailable($msisdn);

        if (!empty($artiDetail) && !empty($pack)) {
          $saleP->error = false;
        }

        if (!empty($is_wait)) {
          $saleP->error = true;
          $saleP->msg = "El artículo se encuentra en espera de aprobación para venta en abono.";
        }

        //Verificando que el coordinador tenga tokens
        if ($pack->sale_type == 'Q' && !$saleP->error) {
          if (session('user_type') == 'vendor') {
            $parent = User::getParentUser(session('user'));
          }

          $tokens = TokensInstallments::getTokensByUser(
            !empty($parent) ? $parent->parent_email : session('user')
          );

          //Obteniendo configuracion activa para venta en abonos
          $conf = ConfigIstallments::getActiveConf();

          //Verificando condiciones de pagos vencidos para poder vender en abono
          $canSaleIns = Seller::canSaleInstallment($conf);

          if (empty($tokens) || !$canSaleIns) {
            $saleP->error = true;

            if (!$canSaleIns) {
              $saleP->msg = "No puedes realizar la venta por cuotas vencidas.";
            } elseif (session('user_type') == 'vendor') {
              $saleP->msg = "El Coordinador se quedo sin cupos para ventas en abono.";
            } else {
              $saleP->msg = "Te quedaste sin cupos para ventas en abono.";
            }
          }
        }
      }

      return response()->json($saleP);
    }
  }

  public function processSale(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $saleP = new \stdClass;
      $saleP->error = true;
      $saleP->message = 'Faltan datos para procesar la venta.';

      //$environment = env('APP_ENV', 'local');

      if (
        !empty($inputs['type_sell']) &&
        (
          ($inputs['type_sell'] == 'home' && !empty($inputs['lat']) && !empty($inputs['lon']))
          ||
          ($inputs['type_sell'] == 'mov' && !empty($inputs['imei']) && !empty(session('device')))
          ||
          $inputs['type_sell'] == 'mov-ph'
          ||
          $inputs['type_sell'] == 'mifi'
          ||
          ($inputs['type_sell'] == 'mifi-h' && !empty($inputs['lat']) && !empty($inputs['lon'])))
        &&
        !empty($inputs['num']) && !empty($inputs['type']) && !empty($inputs['service']) &&
        !empty($inputs['client']) && !empty($inputs['plan']) && !empty($inputs['isPort']) &&
        (
          ($inputs['isPort'] == 'true' && !empty($inputs['nip']) && !empty($inputs['dnPort']) && !empty($inputs['companyPort']) && strlen($inputs['dnPort']) == 10/*&& $request->hasFile('dnf') && $request->file('dnf')->isValid() && $request->hasFile('dnb') && $request->file('dnb')->isValid()*/)
          ||
          $inputs['isPort'] == 'false')
      ) {
        $pack = base64_decode($inputs['plan']);

        $artiDetail = Inventory::getArticByIdAndDN($pack, $inputs['num']);

        //Si es portabilidad se guarda la foto de la identificación en s3
        /*if($inputs['isPort'] == 'true'){
        //Subiendo archivos a s3 en caso de ser portabilidad
        $path = 'portability/dni/'.$inputs['client'].'/';

        $dnf = $request->file('dnf');
        Storage::disk('s3')->put(
        $path.$inputs['client'].'-f.'.$dnf->getClientOriginalExtension(),
        file_get_contents($dnf->getPathname()),
        'public'
        );
        $urlDniF = (String)Storage::disk('s3')
        ->url($path.$inputs['client'].'-f.'.$dnf->getClientOriginalExtension());

        $dnb = $request->file('dnb');
        Storage::disk('s3')->put(
        $path.$inputs['client'].'-b.'.$dnb->getClientOriginalExtension(),
        file_get_contents($dnb->getPathname()),
        'public'
        );
        $urlDniB = (String)Storage::disk('s3')
        ->url($path.$inputs['client'].'-b.'.$dnb->getClientOriginalExtension());
        }*/

        //Validando si el vendedor tiene permisos para realizar el alta que intenta hacer
        if (
          (($inputs['type_sell'] == 'mifi' || $inputs['type_sell'] == 'mifi-h') && hasPermit('SEL-MIF')) ||
          (($inputs['type_sell'] == 'mov' || $inputs['type_sell'] == 'mov-ph') && hasPermit('SEL-MOV')) ||
          ($inputs['type_sell'] == 'home' /*Agregar politica para hbb aqui*/)
        ) {

          $isAssig = SellerInventory::isAssignedDn($inputs['num'], session('user'));

          if (
            !empty($artiDetail) && !empty($isAssig) &&
            (
              ($inputs['isPort'] == 'false') ||
              ($inputs['isPort'] == 'true' /*&& !empty($urlDniB) && !empty($urlDniF)*/))
          ) {
            $service = Service::getService(base64_decode($inputs['service']));

            if (!empty($service)) {
              $client = Client::getClientINEorDN($inputs['client'], false);

              $types = [
                'mov' => 'T',
                'mov-ph' => 'T',
                'home' => 'H',
                'mifi' => 'M',
                'mifi-h' => 'MH',
              ];

              $qty = ClientNetwey::getTypeDns(
                $client->dni,
                !empty($types[$inputs['type_sell']]) ? $types[$inputs['type_sell']] : false
              );

              if (
                !empty($client) &&
                (($inputs['type_sell'] == 'mov' || $inputs['type_sell'] == 'mov-ph') && count($qty) < env('LIMIT_DEVICE_M', 5)) ||
                ($inputs['type_sell'] == 'home' && count($qty) < env('LIMIT_DEVICE', 2)) ||
                ($inputs['type_sell'] == 'mifi' && count($qty) < env('LIMIT_DEVICE_MI', 2)) ||
                ($inputs['type_sell'] == 'mifi-h' && count($qty) < env('LIMIT_DEVICE_MH', 2))
              ) {

                if (session('user_type') == 'vendor') {
                  $parent = User::getParentUser(session('user'));
                }

                $infoUser = User::getInfoUser(
                  !empty($parent) ? $parent->parent_email : session('user')
                );

                $plan = Pack::getInfoPack($pack, $inputs['type']);

                $coordBan = false;
                $esquemas = PackEsquema::getEsquemasByPack($pack);

                if ($esquemas->count()) {
                  if (!empty($infoUser->esquema_comercial_id) && in_array($infoUser->esquema_comercial_id, $esquemas->pluck('id_esquema')->toArray())) {
                    $coordBan = true;
                  }
                } else {
                  $coordBan = true;
                }

                if (!empty($plan) && $coordBan) {
                  //Verificando que se haya validado la identidad si el pack lo requiere
                  if ($plan->valid_identity == 'Y') {
                    $isVeri = IdentityVerification::getSuccesVerification($inputs['num'], $inputs['client']);

                    if (empty($isVeri)) {
                      $saleP->message = 'Debes verificar la identidad del cliente para poder darlo de alta.';
                      return response()->json($saleP);
                    }
                  }

                  //Monto a pagar
                  $amount = $plan->price_pack + $plan->price_serv;

                  if ($plan->sale_type == 'Q' && (
                    !filter_var($inputs['isCoppel'], FILTER_VALIDATE_BOOLEAN)
                    ||
                    !filter_var($inputs['isTelmovPay'], FILTER_VALIDATE_BOOLEAN)
                  )) {
                    //Buscando configuracion activa
                    $conf = ConfigIstallments::getActiveConf();

                    if (!empty($conf)) {
                      //Validando si el monto que abonara el cliente es válido
                      $min_p = $amount * ($conf->firts_pay / 100);

                      if (
                        !empty($inputs['min_inst']) && is_numeric($inputs['min_inst']) &&
                        $min_p <= $inputs['min_inst']
                      ) {

                        $tokens = TokensInstallments::getTokensByUser(
                          !empty($parent) ? $parent->parent_email : session('user')
                        );

                        //Verificando condiciones de pagos vencidos para poder vender en abono
                        $canSaleIns = Seller::canSaleInstallment($conf);

                        //Estatus de la venta en abono, inicializado en un estatus no válido
                        $status_r = 'F';

                        if (!empty($tokens) && $canSaleIns) {
                          $is_wait = SaleInstallment::isAvailable($inputs['num']);

                          if (empty($is_wait)) {
                            $status_r = 'R';
                            $unique = uniqid('ABO-') . time();

                            //Dando de alta el dn si es un coordinador
                            if (session('user_type') != 'vendor') {
                              $result = ProcessRegAlt::doProcessRegAlt(
                                $inputs['type_sell'], /*1*/
                                $inputs['num'], /*2*/
                                !empty($inputs['address']) ? $inputs['address'] : false, /*3*/
                                !empty($inputs['lat']) ? str_replace(',', '.', $inputs['lat']) : false, /*4*/
                                !empty($inputs['lon']) ? str_replace(',', '.', $inputs['lon']) : false, /*5*/
                                $service, /*6*/
                                $artiDetail, /*7*/
                                $inputs['type'], /*8*/
                                $unique, /*9*/
                                $client, /*10*/
                                $plan, /*11*/
                                $inputs['isPort'], /*12*/
                                !empty($inputs['nip']) ? $inputs['nip'] : false, /*13*/
                                !empty($inputs['dnPort']) ? $inputs['dnPort'] : false, /*14*/
                                !empty($inputs['companyPort']) ? $inputs['companyPort'] : false, /*15*/
                                !empty($urlDniB) ? $urlDniB : false, /*16*/
                                !empty($urlDniF) ? $urlDniF : false, /*17*/
                                !empty($inputs['imei']) ? $inputs['imei'] : false, /*18*/
                                !empty($inputs['saleTo']) ? $inputs['saleTo'] : false, /*19*/
                                !empty($inputs['isBandTE']) ? $inputs['isBandTE'] : false, /*20*/
                                false, /*21*/
                                false, /*22*/
                                false, /*23*/
                                ($inputs['type_sell'] == 'mov-ph') ? (!empty($inputs['dnReferred']) ? $inputs['dnReferred'] : false) : false/*24*/
                              );

                              if ($result['success']) {
                                //Actualizando tokens disponibles
                                TokensInstallments::updateToken(
                                  ($tokens->tokens_available - 1),
                                  $tokens->id
                                );

                                $status_r = 'P';
                              } else {
                                $status_r = 'F';
                                if (!empty($result['message'])) {
                                  $saleP->message = $result['message'];
                                }
                              }
                            } else {
                              $coord = User::getParentUser(session('user'));

                              if (!empty($coord)) {
                                $fm = $coord->parent_email;
                              }
                            }

                            //Guardando solicitud si es un vendedor o un coordinador y si el alta se ejecuto de forma correcta
                            if ($status_r == 'R' || $status_r == 'P') {
                              $date = date("Y-m-d H:i:s");

                              SaleInstallment::getConnect('W')
                                ->insert([
                                  'seller' => session('user'),
                                  'coordinador' => !empty($fm) ? $fm : session('user'),
                                  'quotes' => $status_r == 'P' ? 1 : 0,
                                  'config_id' => $conf->id,
                                  'unique_transaction' => $unique,
                                  'lat' => $inputs['lat'],
                                  'lng' => $inputs['lon'],
                                  'pack_id' => $plan->id,
                                  'type_pack' => $inputs['type'],
                                  'service_id' => $service->id,
                                  'client_dni' => $inputs['client'],
                                  'msisdn' => $inputs['num'],
                                  'amount' => $amount,
                                  'first_pay' => $inputs['min_inst'],
                                  'date_reg_alt' => $status_r == 'P' ? $date : null,
                                  'date_reg' => $date,
                                  'date_update' => $date,
                                  'alert_exp' => 'P',
                                  'status' => $status_r,
                                ]);

                              //Insertando primera cuota de la venta
                              if ($status_r == 'P') {
                                SaleInstallmentDetail::getConnect('W')
                                  ->insert([
                                    'unique_transaction' => $unique,
                                    'amount' => $inputs['min_inst'],
                                    'n_quote' => 1,
                                    'conciliation_status' => 'C',
                                    'date_reg' => $date,
                                    'date_update' => $date,
                                    'status' => 'A',
                                  ]);
                              }

                              if ($status_r != 'F') {
                                $saleP->error = false;
                              }
                            }
                          } else {
                            $saleP->message = 'El msisdn se encuentra en espera de aprobacion de otra venta en abono.';
                          }
                        }
                      } else {
                        $saleP->message = 'Faltan datos para procesar la venta en abono.';
                      }
                    } else {
                      $saleP->message = 'No se consiguio configuracion de pago en abono.';
                    }
                  } else {
                    if ($inputs['isCoppel'] && $inputs['isCoppel'] == 'true') {
                      if (!empty($inputs['blackbox']) && !empty($inputs['token'])) {
                        $lastRegCoppel = Coppel::getLast($inputs['num']);

                        if (!empty($lastRegCoppel)) {
                          if ($lastRegCoppel->amount == ($plan->price_pack + $plan->price_serv) && $lastRegCoppel->pack_id == $plan->id) {
                            $resCoppel = CoppelPay::processPayment(
                              $inputs['blackbox'],
                              $inputs['token'],
                              $lastRegCoppel->request,
                              $lastRegCoppel->transaction_code,
                              $inputs['num'],
                              $request->ip()
                            );

                            if ($resCoppel['success']) {
                              $lastRegCoppel->auth_code = $resCoppel['data']['cpl_auth_code'];
                              $lastRegCoppel->auth = $resCoppel['data']['cpl_auth'];
                              $lastRegCoppel->token = $inputs['token'];
                              $lastRegCoppel->save();

                              $result = ProcessRegAlt::doProcessRegAlt(
                                $inputs['type_sell'], /*1*/
                                $inputs['num'], /*2*/
                                !empty($inputs['address']) ? $inputs['address'] : false, /*3*/
                                !empty($inputs['lat']) ? $inputs['lat'] : false, /*4*/
                                !empty($inputs['lon']) ? $inputs['lon'] : false, /*5*/
                                $service, /*6*/
                                $artiDetail, /*7*/
                                $inputs['type'], /*8*/
                                uniqid('CMB') . time(), /*9*/
                                $client, /*10*/
                                $plan, /*11*/
                                $inputs['isPort'] == 'true', /*12*/
                                !empty($inputs['nip']) ? $inputs['nip'] : false, /*13*/
                                !empty($inputs['dnPort']) ? $inputs['dnPort'] : false, /*14*/
                                !empty($inputs['companyPort']) ? $inputs['companyPort'] : false, /*15*/
                                !empty($urlDniB) ? $urlDniB : false, /*16*/
                                !empty($urlDniF) ? $urlDniF : false, /*17*/
                                !empty($inputs['imei']) ? $inputs['imei'] : false, /*18*/
                                !empty($inputs['saleTo']) ? $inputs['saleTo'] : false, /*19*/
                                !empty($inputs['isBandTE']) ? $inputs['isBandTE'] : false, /*20*/
                                false, /*21*/
                                true, /*22*/
                                false, /*23*/
                                ($inputs['type_sell'] == 'mov-ph') ? (!empty($inputs['dnReferred']) ? $inputs['dnReferred'] : false) : false/*24*/
                              );

                              $saleP->error = !$result['success'];

                              if ($saleP->error) {
                                $saleP->errorAltan = true;
                                if (!empty($result['messageAltan'])) {
                                  $saleP->message = 'El pago fue procesado exitosamente pero al intentar dar de alta el msisdn ocurrio el siguiente error: ' . (!empty($result['message']) ? $result['message'] : 'Falló conexión con altan');
                                  $lastRegCoppel->error = $result['messageAltan'];
                                } else {
                                  $saleP->message = 'El pago fue procesado exitosamente pero ocurrio un error al dar de alta el msisdn';
                                  $lastRegCoppel->error = $saleP->message;
                                }
                                $lastRegCoppel->status = 'EA';
                              } else {
                                $lastRegCoppel->status = 'S';
                              }

                              $lastRegCoppel->save();
                            } else {
                              $lastRegCoppel->status = 'EC';
                              $lastRegCoppel->error = $resCoppel['message'];
                              $lastRegCoppel->save();

                              $saleP->message = 'Mensaje de Coppel: ' . $resCoppel['message'];
                            }
                          } else {
                            $lastRegCoppel->status = 'EC';
                            $lastRegCoppel->error = 'Datos no válidos para procesar el pago en Coppel.';
                            $lastRegCoppel->save();

                            $saleP->message = 'Datos no válidos para procesar el pago en Coppel.';
                          }
                        } else {
                          $saleP->message = 'Datos no válidos para procesar el pago en Coppel.';
                        }
                      } else {
                        $countCop = (int) (Coppel::getTotalReg() + 1);
                        $trans = 'TRANSA' . Common::completedLeftString(15, '0', $countCop);

                        $amountC = $plan->price_pack + $plan->price_serv;
                        $signature = CoppelPay::getSignature($trans, $amountC);

                        //Creando registro en tabla de coppel
                        $idRegCoppel = Coppel::firstInsert([
                          'transaction_code' => $trans,
                          'ip' => $request->ip(),
                          'signature' => $signature,
                          'msisdn' => $inputs['num'],
                          'amount' => $amountC,
                          'clients_dni' => $client->dni,
                          'service_id' => $service->id,
                          'pack_id' => $plan->id,
                          'articles_id' => $artiDetail->inv_article_id,
                          'user_email' => session('user'),
                          'status' => 'I',
                          'date_reg' => date('Y-m-d H:i:s'),
                        ]);

                        $resCoppel = CoppelPay::buyRequest($trans, $amountC, $plan->description, $signature, $inputs['num']);

                        if ($resCoppel['success']) {
                          Coppel::setRequest($idRegCoppel, $resCoppel['request']);
                          $saleP->error = false;
                          $saleP->request = $resCoppel['request'];
                        } else {
                          Coppel::setStatus($idRegCoppel, 'EC', $resCoppel['message']);
                          $saleP->message = $resCoppel['message'];
                        }
                      }
                    } else {
                      if (filter_var($inputs['isTelmovPay'], FILTER_VALIDATE_BOOLEAN)) {
                        $inputs['art_inv_model'] = base64_decode($inputs['art_inv_model']);
                      } else {
                        $inputs['art_inv_model'] = false;
                      }

                      $result = ProcessRegAlt::doProcessRegAlt(
                        $inputs['type_sell'], /*1*/
                        $inputs['num'], /*2*/
                        !empty($inputs['address']) ? $inputs['address'] : false, /*3*/
                        !empty($inputs['lat']) ? $inputs['lat'] : false, /*4*/
                        !empty($inputs['lon']) ? $inputs['lon'] : false, /*5*/
                        $service, /*6*/
                        $artiDetail, /*7*/
                        $inputs['type'], /*8*/
                        uniqid('CMB') . time(), /*9*/
                        $client, /*10*/
                        $plan, /*11*/
                        $inputs['isPort'] == 'true', /*12*/
                        !empty($inputs['nip']) ? $inputs['nip'] : false, /*13*/
                        !empty($inputs['dnPort']) ? $inputs['dnPort'] : false, /*14*/
                        !empty($inputs['companyPort']) ? $inputs['companyPort'] : false, /*15*/
                        !empty($urlDniB) ? $urlDniB : false, /*16*/
                        !empty($urlDniF) ? $urlDniF : false, /*17*/
                        !empty($inputs['imei']) ? $inputs['imei'] : false, /*18*/
                        !empty($inputs['saleTo']) ? $inputs['saleTo'] : false, /*19*/
                        !empty($inputs['isBandTE']) ? $inputs['isBandTE'] : false, /*20*/
                        false, /*21*/
                        false, /*22*/
                        !empty($inputs['typePaymentF']) ? $inputs['typePaymentF'] : false, /*23*/
                        ($inputs['type_sell'] == 'mov-ph') ? (!empty($inputs['dnReferred']) ? $inputs['dnReferred'] : false) : false, /*24*/
                        // $inputs['isTelmovPay'] == 'true',
                        //$inputs['art_inv_model']
                      );

                      $saleP->error = !$result['success'];

                      if ($saleP->error && !empty($result['message'])) {
                        $saleP->message = $result['message'];
                      }
                    }
                  }
                } else {
                  $saleP->message = 'No se consiguio el plan.';
                }
              } else {
                $saleP->message = 'No se consiguio el cliente ó ya alcanzo el maximo de productos.';
              }
            } else {
              $saleP->message = 'No se consiguio el servicio.';
            }
          } else {
            $saleP->message = 'No se consiguio el msisdn ó no esta asociado con el pack seleccionado.';
          }
        } else {
          $saleP->message = 'No tienes permiso para realizar el alta.';
        }
      }

      if ($saleP->error) {
        Log::error('Ocurrio un error al intentar dar de alta un DN ' . (string) json_encode($saleP));
      }
      return response()->json($saleP);
    }
  }

  public function getStatusNumber(Request $request)
  {
    if ($request->isMethod('post')) {
      $inputs = $request->all();

      $this->validate($request, [
        'statusNumber' => 'required|min:10|max:10',
      ]);

      $client = APIClient::getClient($inputs['statusNumber']);

      return view('seller.status', compact('client'));
    }

    return view('seller.status');
  }

  //Muesta la vista para vender un articulo y envia los dns asignados al vendedor
  public function saleProduct(Request $request)
  {
    //consultando articulos asociados al vendedor
    //Si es un vendedor de una organizacion tipo retail
    $artics = [];
    if (session('org_type') == 'R') {
      $artics = Inventory::getDnsByWareHouse(session('wh'));
    } else {
      //Si es un vendedor de una organizacion normal
      $artics = SellerInventory::getAllArticsAssign(session('user'));
    }

    return view('seller.unBarring', compact('artics'));
  }

  //Devuelve el html con el detalle del dn seleccionado, funciona para usuarios retail y normales, para los usuarios normales les va a mostrar el primer pack que consiga la consulta asignado al dn seleccionado.
  public function getPackProduct(Request $request)
  {
    if ($request->ajax() && $request->isMethod('post')) {
      session()->forget('productDN');
      if (!empty($request->dn)) {
        $data = $this->getPacksProduct($request->dn);

        if (!$data['error']) {
          $address = true;
          $html = view('seller.packsProducts', compact('data', 'address'))->render();

          session(['productDN' => $request->dn]);

          return response()->json(['error' => false, 'html' => $html]);
        }

        return response()->json($data);
      }
    }
  }

  //Devuelve los packs asociados al dn dado, valida que el dn pueda ser vendido por el usuario que esta consultando los packs, este metodo usa variables de session.
  private function getPacksProduct($msisdn = false)
  {
    if ($msisdn) {
      //Buscando si el dn seleccionado esta asignado al vendedor tipo retail
      if (session('org_type') == 'R') {
        $artic = Inventory::getArticByDnAndWh($msisdn, session('wh'));
      } else {
        //Buscando si el dn seleccionado esta asignado al vendedor no retail
        $artic = SellerInventory::getArticByDnAndUser($msisdn, session('user'));
      }

      if (!empty($artic)) {
        //Consultando los packs asociados al articulo
        $packs = ArticlePack::getInfoPackByArticle(
          $artic->inv_article_id,
          session('org_type') == 'R' ? 'Y' : 'N'
        );

        if ($packs->count()) {
          //Consultando los servicios asociados a
          $services = PackPrices::getDataPacksByIds($packs->pluck('pack_id'));

          if ($services->count()) {
            return ['error' => false, 'artic' => $artic, 'packs' => $packs, 'services' => $services];
          } else {
            return ['error' => true, 'message' => 'No se consiguieron servicios asociados al pack.'];
          }
        } else {
          return ['error' => true, 'message' => 'No se consiguieron packs asociados al dn.'];
        }
      } else {
        return ['error' => true, 'message' => 'No se consiguio el DN.'];
      }
    }
    return ['error' => true, 'message' => 'Ocurrio un error, por favor intenta mas tarde.'];
  }

  public function confirmSaleProduct(Request $request)
  {
    if ($request->isMethod('post')) {
      $msisdn = $request->msisdn_select;
      if (!empty($msisdn)) {
        $data = $this->getPacksProduct($msisdn);

        if (!$data['error']) {
          $address = !empty($request->address) ? $request->address : false;
          $lat = !empty($request->lat) ? $request->lat : '';
          $lng = !empty($request->lon) ? $request->lon : '';

          $html = view('seller.packsProducts', compact('data', 'address', 'lat', 'lng'))->render();

          return view('seller.confirmSaleProduct', compact('html', 'msisdn'));
        }
      }
    }

    session()->flash('message_class', 'alert-danger');
    session()->flash('message_error', 'Ocurrio un error con el proceso de la compra.');
    return redirect()->route('seller.onlyProduct');
  }

  public function doSaleProduct(Request $request)
  {
    if ($request->isMethod('post')) {
      if (!empty($request->msisdn)) {
        $orgDN = session('productDN');

        if ($request->msisdn == $orgDN) {
          $data = $this->getPacksProduct($orgDN);

          if (!$data['error']) {
            $dni = uniqid();
            $date = date('Y-m-d H:i:s');
            $unique = uniqid('RET') . time();

            //Creando cliente con datos temporales
            $client = [
              'dni' => $dni,
              'name' => 'TEMPORAL',
              'last_name' => 'TEMPORAL',
              'phone_home' => '0000000000',
              'reg_email' => session('user'),
              'date_reg' => $date,
            ];

            if (!empty($request->address)) {
              $client['address'] = $request->address;
            }

            Client::insert($client);

            //Asignado dn al cliente creado
            $clientNetwey = array(
              'msisdn' => $orgDN,
              'clients_dni' => $dni,
              'service_id' => $data['services'][0]->service_id,
              'type_buy' => 'CO',
              'periodicity' => $data['services'][0]->periodicity,
              'num_dues' => 0,
              'paid_fees' => 0,
              'unique_transaction' => $unique,
              'date_buy' => $date,
              'date_reg' => $date,
              'dn_type' => $data['artic']->artic_type,
              'status' => 'A',
            );

            if ($data['artic']->artic_type == 'H') {
              $clientNetwey['serviceability'] = $data['services'][0]->broadband;
            }

            if ($data['services'][0]->type == 'CR') {
              $clientNetwey['type_buy'] = 'CR';
              $clientNetwey['price_remaining'] = $data['services'][0]->total_amount;
              $clientNetwey['total_debt'] = $data['services'][0]->total_amount;
              $clientNetwey['credit'] = 'A';
            }

            if (!empty($request->address)) {
              $clientNetwey['address'] = $request->address;

              if (!empty($request->lat) && !empty($request->lon)) {
                $clientNetwey['lat'] = $request->lat;
                $clientNetwey['lng'] = $request->lon;
                $clientNetwey['point'] = DB::raw("(GeomFromText('POINT(" . $clientNetwey['lat'] . " " . $clientNetwey['lng'] . ")'))");
              } else {
                $response = Google::getDataFromAddress($request->address);

                if ($response['success']) {
                  $clientNetwey['lat'] = $response['data']['lat'];
                  $clientNetwey['lng'] = $response['data']['lng'];
                  $clientNetwey['address'] = !empty($response['data']['address']) ? $response['data']['address'] : $request->address;
                  $clientNetwey['point'] = DB::raw("(GeomFromText('POINT(" . $clientNetwey['lat'] . " " . $clientNetwey['lng'] . ")'))");
                }
              }
            }

            ClientNetwey::insert($clientNetwey);

            //Marcando el articulo como vendido
            Inventory::markArticleSale($data['artic']->id);

            //Pasando a trash todas las asignaciones del articulo
            SellerInventory::cleanAssign($data['artic']->id, session('user'));

            SellerInventory::markSale($data['artic']->id, session('user'), session('org_type'));

            //Creando venta
            $amount = ($data['services'][0]->price_pack + $data['services'][0]->price_serv);

            $dataSale = array(
              'services_id' => $data['services'][0]->service_id,
              'inv_arti_details_id' => $data['artic']->id,
              'concentrators_id' => 1,
              'api_key' => env('API_KEY_ALTAM'),
              'users_email' => session('user'),
              'packs_id' => $data['packs'][0]->pack_id,
              'unique_transaction' => $unique,
              'codeAltan' => $data['services'][0]->codeAltan,
              'type' => 'V',
              'id_point' => 'VENDOR',
              'description' => 'ARTICULO',
              'amount' => $amount,
              'amount_net' => ($amount / env('TAX')),
              'com_amount' => 0,
              'msisdn' => $orgDN,
              'date_reg' => $date,
              'status' => 'A',
              'sale_type' => $data['artic']->artic_type,
            );

            $sup = session('user');

            if (session('user_type') == 'vendor') {
              $sup = User::getParentUser(session('user'));

              if (!empty($sup)) {
                $sup = $sup->parent_email;
              }
            }

            if (session('org_type') == 'R' || session('user_type') != 'vendor') {
              $idAssig = AssignedSales::getConnect('W')
                ->insertGetId([
                  'parent_email' => $sup,
                  'users_email' => session('user'),
                  'amount' => $amount,
                  'amount_text' => $amount,
                  'date_reg' => $date,
                  'date_accepted' => $date,
                  'status' => 'P',
                ]);

              AssignedSalesDetail::getConnect('W')
                ->insert([
                  'asigned_sale_id' => $idAssig,
                  'amount' => $amount,
                  'amount_text' => $amount,
                  'unique_transaction' => $unique,
                ]);
            }

            Sale::getConnect('W')
              ->insert($dataSale);

            return redirect()->route('seller.onlyProduct')->with('sale', $unique);
          }
        }
      }
    }

    session()->flash('message_class', 'alert-danger');
    session()->flash('message_error', 'Ocurrio un error con el proceso de la compra.');
    return redirect()->route('seller.onlyProduct');
  }

  public function cashDelivery(Request $request)
  {
    if ($request->isMethod('post')) {
      $msgError = 'No se procesaron ventas.';

      if (!empty($request->item) && is_array($request->item) && count($request->item)) {
        $user = User::getParentUser2(session('user'));

        if (!empty($user) && !empty($user->parent_email)) {
          $sales = Sale::getNotConciliationSalesByUser([
            'user' => session('user'),
            'sales' => $request->item,
          ]);

          $totalAmount = $sales->sum('amount');

          $sales = $sales->get();

          if ($sales->count()) {
            $idAssig = AssignedSales::getConnect('W')
              ->insertGetId([
                'parent_email' => $user->parent_email,
                'users_email' => session('user'),
                'amount' => $totalAmount,
                'amount_text' => $totalAmount,
                'date_reg' => date("Y-m-d H:i:s"),
                'status' => 'V',
              ]);

            foreach ($sales as $sale) {
              $idUpdate = AssignedSalesDetail::getAssigneSaleByuniq(
                $sale->unique_transaction,
                $idAssig
              );

              if ($idUpdate->count()) {
                AssignedSales::deleteAssigns($idUpdate->pluck('id'));
              }

              AssignedSalesDetail::getConnect('W')
                ->insert([
                  'asigned_sale_id' => $idAssig,
                  'amount' => $sale->amount,
                  'amount_text' => $sale->amount,
                  'unique_transaction' => $sale->unique_transaction,
                ]);
            }

            session()->flash('message_class', 'alert-success');
            session()->flash('message_error', 'Efectivo entregado exitosamente.');

            return redirect()->route('dashboard');
          }
        } else {
          $msgError = 'Usuario sin supervisor asignado.';
        }
      }

      session()->flash('message_class', 'alert-danger');
      session()->flash('message_error', $msgError);
    }

    $salesRep = AssignedSales::getAssigneReportByUser(session('user'));

    //Busca toda la info relacionada a la venta en abono para que le vendedor la notifique
    $saleIns = SaleInstallment::getPendingReportSales(session('user'));

    //Buscando si la transaccion fue notificada y rechazada
    foreach ($saleIns as $saleInsd) {
      $not = PayInstallment::getDenyNoti($saleInsd->id);

      if (!empty($not)) {
        $saleInsd->reason_den = $not->reason;
      }
    }

    //Busca las transacciones hechas en abono para sacarlas de las transacciones normales
    $saleInsU = SaleInstallment::getTransactionSales(session('user'));

    $sales = Sale::getSalePendingReport([
      'status' => 'E',
      'user' => session('user'),
      'notTransactions' => array_merge(
        $saleInsU->pluck('unique_transaction')->toArray(),
        $salesRep->pluck('unique_transaction')->toArray()
      ),
    ]);

    foreach ($sales as $sale) {
      $not = AssignedSalesDetail::getDenyNoti($sale->unique_transaction);

      if (!empty($not)) {
        $sale->reason_den = $not->reason;
      }
    }

    if ($sales->count() || $saleIns->count()) {
      return view('seller.cash_delivery', compact('sales', 'saleIns'));
    }

    session()->flash('message_class', 'alert-danger');
    session()->flash('message_error', 'No tienes ventas pendientes por entrega de efectivo.');
    return redirect()->route('dashboard');
  }

  public static function cashDeliveryDeny(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $sn = AssignedSales::getDenyNotiByUser(session('user'))->count();

      $si = PayInstallment::getDenyNotiByUser(session('user'));

      if ($sn || $si->count()) {
        if ($sn) {
          AssignedSales::setView(session('user'));
        }

        if ($si->count()) {
          PayInstallment::setView($si->pluck('id'));
        }

        return response()->json(['success' => true, 'show' => true]);
      }

      return response()->json(['success' => true, 'show' => false]);
    }
  }
}
