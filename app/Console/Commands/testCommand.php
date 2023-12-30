<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Utilities\Common;

class testCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */

  protected $signature = 'command:testCommand';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Comando para testing';

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

      $this->info('');
      $this->info('**************************');
      $this->output->writeln("-------------------------------------------------------");
      $this->output->writeln("Paguitos ->".Common::getDiscount('PAGUITOS'));
      $this->output->writeln("Payjoy ->".Common::getDiscount('PAYJOY'));
      $this->info('**************************');
  }
}
