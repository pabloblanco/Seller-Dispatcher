<?php

namespace App\Console\Commands;

use App\Models\AltasSpeed;
use App\Models\ClientNetwey;
use App\Models\DNMigration;
use Illuminate\Console\Command;

class activateMasiveMigration extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */

  protected $signature = 'command:ActivateListMigrate {idStar?} {idCant?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Pasar MSISDN de activacion masiva en lista para ser migradas';

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
    $idStar = $this->argument('idStar') - 1;
    $idCant = $this->argument('idCant');

    if ((!empty($idStar) || $idStar == 0) && !empty($idCant)) {

      $InfDatos  = AltasSpeed::getDnsActivateMigrations($idStar, $idCant);
      $cant      = 0;
      $cantExist = 0;
      $cantCli   = 0;
      $this->info('**************************');
      $this->info('Cantidad filtrada ' . count($InfDatos));
      $this->info('**************************');

      foreach ($InfDatos as $value) {

        //si no esta registrado como cliente se procede a verificar que no este en inventario

        if (!ClientNetwey::isClient($value->msisdn)) {
          if (!DNMigration::getExist_MSISDNlistMigrations($value->msisdn)) {
            $temp = DNMigration::setMSISDN_listMigrations($value->msisdn);
            $cant++;
          } else {
            $cantExist++;
            $this->info($cantExist . '- ' . $value->msisdn . ' Ya esta en la tabla islim_dns_migrations');
          }
        } else {
          $cantCli++;
          $this->info($cantCli . '=> ' . $value->msisdn . ' Esta asignado a un cliente <=');
        }
      }
      $this->info('');
      $this->info('**************************');
      $this->info('msisdn en tabla de migracion: ' . $cantExist);
      $this->info('msisdn en uso por clientes: ' . $cantCli);
      $this->info('**************************');
      $this->info('Activados masivamente en lista para migracion (islim_altas_speed) desde: ' . $idStar . ' cantidad: ' . $idCant . '. MSISDN PROCESADOS=> ' . $cant);
    } else {
      if (empty($idCant) && !empty($idStar)) {
        $this->info('No se pudo ejecutar el comando, hace falta indicar cuantos registros seran procesados');
      } else {
        $this->info('No se pudo ejecutar el comando, debes enviar como parámetro el registro inicial o *punto de partida* de islim_altas_speed y la cantidad a procesar');
      }
    }
  }
}
