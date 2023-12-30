<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Installations;
use App\Models\InstallationsBundle;
use App\Models\Inventory;
use App\Models\Pack;
use App\Models\Service;
use App\Utilities\ProcessRegAlt;
use Illuminate\Console\Command;

class activationBundleAltan extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:activateBundleAltan';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Proceso de activacion de elementos del bundle que se deben activar con altan.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $dateInitialAll = microtime(true);

    $UpAltan = InstallationsBundle::getConnect('W')
      ->where('status', 'EC')
      ->orderBy('installations_id', 'ASC')
      ->get();

    $cant = count($UpAltan);
    if ($cant) {

      $typeProduct = [
        'T' => 'mov',
        'T' => 'mov-ph',
        'H' => 'home',
        'M' => 'mifi',
        'MH' => 'mifi-h'];

      $item = 1;
      // $InstallTemp = "";
      //
      foreach ($UpAltan as  &$register) {
        $this->info('<fg=black;bg=white> > Item (' . $item . '/' . $cant . ') Id ' . $register->id);
        $dateInitialOne = microtime(true);
        /*if ($register->unique_transaction != $InstallTemp) {
        //en caso de bundle los unique transation se le coloca un sufijo numerico
        $InstallTemp = $register->unique_transaction;
        $sufix_unique = 2;
        }*/
        if (!empty($register->inv_detail_id) && !empty($register->msisdn_parent) && !empty($register->unique_transaction)) {

          $infoInstall = Installations::getInstallById($register->installations_id);
          if (!empty($infoInstall)) {

            $objClient = Client::getClientINEorDN($infoInstall->clients_dni);

            $plan = Pack::getInfoPack($register->pack_id, $register->service_pay);

            $artiDetail = Inventory::getDnsById($register->inv_detail_id);

            $service = Service::getService($register->service_id);

            if (!empty($objClient) && !empty($plan) && !empty($register->info_imei) && !empty($artiDetail) && !empty($service)) {

              $isPort = false;
              if (!empty($register->conf_port)) {
                $isPort = true;
                $infoPort = json_decode(json_encode($register->conf_port));
              }
              $infoImei = json_decode(json_encode($register->info_imei));

              $altaArt = ProcessRegAlt::doProcessRegAlt(
                $typeProduct[$register->dn_type], /*1*/
                $artiDetail->msisdn, /*2*/
                false, /*3*/
                false, /*4*/
                false, /*5*/
                $service, /*6*/
                $artiDetail, /*7*/
                $register->service_pay, /*8*/
                $register->unique_transaction/*. '-' . $sufix_unique*/, /*9*/
                $objClient, /*10*/
                $plan, /*11*/
                $isPort, /*12*/
                ($isPort) ? $infoPort->port_nip : false, /*13*/
                ($isPort) ? $infoPort->port_dn : false, /*14*/
                ($isPort) ? $infoPort->port_supplier_id : false, /*15*/
                false, /*16*/
                false, /*17*/
                $infoImei->imei, /*18*/
                'C', /*19*/
                !empty($register->isBandTE) ? $register->isBandTE : false, /*20*/
                false, /*21*/
                false, /*22*/
                false, /*23*/
                false, /*24*/
                $register->id/*25*/
              );

              if ($altaArt['success']) {
                //Actualizo que se proceso el hijo del bundle
                $register->status = 'P';

                $dateEndOne = microtime(true);
                $totalOne = $dateEndOne - $dateInitialOne;
                $this->info('<fg=black;bg=white> >> Childre bundle (' . $register->id . ') procesado.! Tiempo: ' . $totalOne . ' seg');
/*
$processCron = new \stdClass;
$processCron->end = date("Y-m-d H:i:s");

if (!empty($register->dateProcess)) {
$initCron = json_decode(json_encode($register->dateProcess));
$processCron->start = $initCron->start;
$fechaSegundos_start = strtotime($initCron->start); //Convertimos a segundos

$timeInSeg = strtotime($processCron->end) - strtotime($processCron->start);
$register->processing_time_cron = Common::transforTime($timeInSeg);
} else {
//No tengo registro de insercion en cron
$processCron->start = '';
$register->processing_time_cron = Common::transforTime($totalOne);
// $register->processing_time_cron = round($totalOne, 2);
}
$register->dateProcess = $processCron;*/
              } else {
                //El alta del servicio fallo
                if (isset($altaArt['messageAltan']) && !empty($altaArt['messageAltan'])) {
                  $textObs = $altaArt['messageAltan'];
                } elseif (isset($altaArt['message']) && !empty($altaArt['message'])) {
                  $textObs = $altaArt['message'];
                } else {
                  $textObs = $altaArt;
                }

                $register->obs = (String) json_encode($textObs);
                $register->status = 'E';
              }
              $register->save();
              sleep(3);
              //$sufix_unique++;
            } else {
              $this->info('<error> No se pudo obtener informacion del cliente o del pack o el imei para el hijo bundle id (' . $register->installations_id . ') <error>');
            }
          } else {
            $this->info('<error> No se pudo obtener informacion de la instalacion id (' . $register->installations_id . ') <error>');
          }
        } else {
          $this->info('<error> Faltan datos del hijo del bundle id (' . $register->id . ') para proceder con el alta <error>');
        }
        if ($register->status != 'P') {
          $dateEndTwo = microtime(true);
          $totalOne = $dateEndTwo - $dateInitialOne;
          $this->info('<fg=black;bg=white> >> Childre bundle (' . $register->id . ') Tiempo: ' . $totalOne . ' seg');
        }
        $item++;
      }
    } else {
      $this->info('<fg=white;bg=red> > No hay hijos de bundle por activar!');
      // Log::info('No hay hijos de bundle por activar!');
    }
    $dateEndAll = microtime(true);
    $totalTime = $dateEndAll - $dateInitialAll;
    //$this->info('<error> Comando finalizado... <error>');
    $this->info('');
    $this->info('<fg=black;bg=white> Comando finalizado... Duracion: ' . $totalTime . ' seg');
    return 0;
  }
}
