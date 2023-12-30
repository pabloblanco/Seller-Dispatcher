<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EndLogTime
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $response = $next($request);

    $startTime = session('startTime');
    $endTime   = round((microtime(true) - $startTime), 2);

    $data = [
      'route' => !empty($request->route()->getName()) ? $request->route()->getName() : 'S/I',
      'url'   => !empty($request->fullUrl()) ? $request->fullUrl() : 'S/I',
      'time'  => $endTime,
      'ip'    => !empty($request->ip()) ? $request->ip() : 'S/I',
      'user'  => !empty(session('user')) ? session('user') : 'S/I',
    ];

    if (env('APP_ENV') != 'local') {
      //Log::channel('daily')
      //  ->notice('(Fin) ' . $startTime . ' - ' . session()->getId() . ' (' . $endTime . 's): ', $data);
    }
    return $response;
  }
}
