<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
    Commands\activationBundleAltan::class,
    Commands\activateFiber::class,
    Commands\resetECFiber::class,
  ];

  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    $schedule->command('command:activateFiber')
      ->everyMinute();

    $schedule->command('command:activateBundleAltan')
      ->everyMinute()->withoutOverlapping(3);

    $schedule->command('command:resetECFiber')
      ->everyFiveMinutes()->withoutOverlapping(3);

    // $schedule->command('command:provisioningOLT')
    //   ->everyMinute()->withoutOverlapping(3);
  }

/**
 * Register the commands for the application.
 *
 * @return void
 */
  public function commands()
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
