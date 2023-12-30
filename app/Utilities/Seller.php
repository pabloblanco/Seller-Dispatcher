<?php
namespace App\Utilities;

use App\Utilities\Common;
use Illuminate\Support\Facades\Log;
use App\Models\SellerInventory;
use App\Models\SaleInstallment;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ArticlePack;
use App\Models\User;
use App\Models\TokensInstallments;
use App\Models\ConfigIstallments;
use App\Models\Pack;
use App\Models\PackPrices;

/*
Clase que contiene diversos metodos para para conectarse con la api de 815.
 */
class Seller
{

  /*
  Devuelve cantidad de modems en abono vendidos por el usuario loguado con pagos vencidos
   */
  public static function getExpiredPayInstallments($user, $type, $config)
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
  }


/*
  Verifica si el usuario autenticado puede vender en abono tomando en cuenta las cuotas vencidas
   */
  public static function canSaleInstallment($conf = false)
  {
    $canSaleIns = false;
    if ($conf) {
      $limitS = $conf->m_permit_s;
      $limitC = $conf->m_permit_c;

      $canSaleIns = true;
      if (session('user_type') == 'vendor') {
        $salesExpInst = self::getExpiredPayInstallments(
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
            $salesExpInst = self::getExpiredPayInstallments(
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
        $salesExpInst = self::getExpiredPayInstallments(
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
  }

/**
 * [SearchPack Metodo que busca los planes para el proceso de venta]
 * @param [type] $typeSell    [description]
 * @param [type] $typeArtic   [description]
 * @param [type] $isBandTE    [description]
 * @param [type] $typePayment [description]
 * @param [type] $brand       [description]
 * @param [type] $isport      [description]
 */
  public static function SearchPack($typeSell, $typeArtic, $isBandTE,$typePayment, $brand, $isport){

    $packs = new \stdClass;
    $invAssig = SellerInventory::getArticsAssign(session('user'), $typeSell);

    // Log::info('invAssig');
    // Log::info((String) json_encode($invAssig));
    // Log::info((String) count($invAssig));
    // Log::info((String) session('org_type'));

      if (count($invAssig) > 0 || session('org_type') == 'R') {
        if (session('org_type') == 'R') {
          $articlesAssig = Inventory::getArticsByWh(session('wh'), $typeSell);

          $idInvAssig = $articlesAssig->pluck('id')->toArray();
        } else {
          $idInvAssig = $invAssig->pluck('inv_arti_details_id')->toArray();

          $articlesAssig = Inventory::getArticsByIds($idInvAssig, $typeSell);
        }
        // Log::info((String) json_encode($articlesAssig));
        // Log::info((String) count($articlesAssig));

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
          $packs->message = "No se consiguiron articulos asignados.";
        }
      } else {
        $packs->status = false;
        $packs->message = "Vendedor sin inventario.";
      }
      return $packs;
  }
}