<?php

namespace App\Console\Commands;

use App\Models\Installations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class resetECFiber extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:resetECFiber';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Si en caso de que el cron quede con token y en espera de cron y no ha salido de la lista se limpia el token para que lo tome de nuevo otro cron';

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

    $exisActive = Installations::getConnect('W')
      ->where(function($query){
        $query->orWhere(function($qry1){
          $qry1->where('status', 'EC')
                ->whereNull('msisdn');
        })
        ->orWhere(function($qry2){
          $qry2->where('status', 'PA')
                ->whereNotNull('msisdn');
        });
      })
      ->whereNotNull('token_activate')
      ->get();

    $cant = count($exisActive);
    $this->info('<fg=white;bg=red> > Citas con token ' . $cant);

    if ($cant) {

      $actual = time();
      foreach ($exisActive as &$item) {
        $porciones = explode("-", $item->token_activate);
        if (!empty($porciones[1])) {
          $diff = (int) $actual - (int) $porciones[1];
          if ($diff > 301) {
            //5 minutos
            $item->token_activate = null;
            $item->save();
            $this->info('<fg=white;bg=red> > Se resetearon tokes en citas de fibra id ' . $item->id . ' Diff: ' . $diff);
          } else {
            $this->info('<fg=black;bg=yellow> * Aun en el margen de 5 minutos. Cita: ' . $item->id);
          }
        } else {
          $tex = "La cita de fibra (" . $item->id . ") en alta asincrona tiene un formato no esperado";
          Log::error($tex);
          $this->info('<fg=black;bg=white> * FAIL ' . $tex);
        }
      }
      unset($item); // rompe la referencia con el último elemento

    } else {
      $this->info('<fg=white;bg=red> > No hay instalaciones de fibra por revisar!');
    }
    $dateEndAll = microtime(true);
    $totalTime = $dateEndAll - $dateInitialAll;
    //$this->info('<error> Comando finalizado... <error>');
    $this->info('');
    $this->info('<fg=black;bg=white> Comando finalizado... Duracion: ' . $totalTime . ' seg');
    return 0;
  }
}
