<?php

namespace App\Console\Commands;

use App\Models\Installations;
use App\Models\FiberZone;
use Illuminate\Console\Command;
use App\Utilities\Api815;
use Log;

class provisioningOLT extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */

  protected $signature = 'command:provisioningOLT';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Busca en la tabla de instalaciones todas aquellas que esten con estatus PA(Por Aprovisionar) y ejecuta el aprovisionamiento a traves del api de 815';

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
    $this->output->writeln('Inicia ' . date('Y-m-d H:i:s'));

    $dateInitialAll = microtime(true);
    $listZone = [];

    $exisActive = Installations::getConnect('R')
      ->where('status', 'PA')
      ->whereNotNull('msisdn')
      ->whereNull('token_activate')
      ->first();

    if (!empty($exisActive)) {
      $zones = FiberZone::getAllZone();
      $cantZone = count($zones);
      $txt = (env('APP_ENV') == 'production') ? "Productivo" : "Test";
      $this->info("<fg=white;bg=black> Existen  " . $cantZone . " configuradas en ambiente " . $txt);
      $pos = 0;

      foreach ($zones as $item) {
        if (!in_array($item->id, $listZone)) {
          $pos++;
          $dateStartZone = microtime(true);
          //Verifico que este activo el servidor
          $zone = 'Zona ' . $item->id;
          $this->info('<fg=white;bg=red>' . $pos . '. > Verificando ' . $zone);
          $statusActive = FiberZone::chekingZone($item->id);
          if ($statusActive['success']) {
            array_push($listZone, $item->id);
            $texZone = ' > ' . $zone . ' OK!';
          } else {
            $texZone = ' > ' . $zone . ' Fallo! >> ' . $statusActive['msg'];
          }
          $dateEndZone = microtime(true);
          $totalTimeZone = $dateEndZone - $dateStartZone;
          $this->info('<fg=white;bg=red> ' . $texZone . ' Tiempo: ' . $totalTimeZone);
        }
      }

    } else {
      $this->info('<fg=white;bg=red> > No hay aprovisionamientos pendientes! <');
      return 0;
    }

    $tokenGroup = "CPA-" . (String) time();

    $AsigneToken = Installations::getConnect('W')
      ->where('status', 'PA')
      ->whereNull('token_activate')
      ->whereNotNull('msisdn')
      ->whereIn('id_fiber_zone', $listZone)
      ->update(['token_activate' => $tokenGroup]);
    sleep(3);

    if ($AsigneToken) {
      $PaFiber = Installations::getConnect('W')
        ->where('token_activate', $tokenGroup)
        ->orderBy('id', 'ASC')
        ->get();

      $cant = count($PaFiber);
      $this->info("Cant " . $cant);

      if ($cant) {
        $pos = 0;
        foreach ($PaFiber as &$dataIns) {

          $dataProvisioned = new \stdClass;

          if(empty($dataIns->dataProvisioned['start'])){
              $dataProvisioned->start = date("Y-m-d H:i:s");
          }
          else{
            $dataProvisioned->start = $dataIns->dataProvisioned['start'];
          }

          $resp = Api815::provisioning($dataIns->msisdn);
          if($resp['success']){

            $dataProvisioned->end = date("Y-m-d H:i:s");
            $dataProvisioned->success = 'Y';

            $dataIns->date_provisioned = date('Y-m-d H:i:s');
            $dataIns->status='P';
          }
          else{
            $dataProvisioned->success = 'N';

            $infoMsg = new \stdClass;
            $infoMsg->code = "ERR_APR";
            $infoMsg->message = $resp['msg'];
            $dataIns->token_activate=null;
            $dataIns->obs_activate = $infoMsg;
          }

          if(!empty($dataIns->dataProvisioned['try'])){
              $dataProvisioned->try=$dataIns->dataProvisioned['try']+1;
            }
            else{
              $dataProvisioned->try=1;
          }

          $this->output->writeln($resp);

          $dataIns->dataProvisioned = $dataProvisioned;

          if($dataProvisioned->try >= env('LIM_CANT_PROVISION',3)){
            $dataProvisioned->end = date("Y-m-d H:i:s");
            $dataIns->status='P';
          }

          $dataIns->save();
        }
      }
    }

    $this->output->writeln('Finaliza ' . date('Y-m-d H:i:s'));
  }
}
