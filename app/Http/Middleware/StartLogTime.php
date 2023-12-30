<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StartLogTime
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
    $startTime = microtime(true);
    session(['startTime' => $startTime]);

    $data = [
      'route' => !empty($request->route()->getName()) ? $request->route()->getName() : 'S/I',
      'url'   => !empty($request->fullUrl()) ? $request->fullUrl() : 'S/I',
      'ip'    => !empty($request->ip()) ? $request->ip() : 'S/I',
      'user'  => !empty(session('user')) ? session('user') : 'S/I',
    ];

    if (env('APP_ENV') != 'local') {
      //Log::channel('daily')
      //  ->notice('(Inicio) ' . $startTime . ' - ' . session()->getId() . ': ', $data);
    }
    return $next($request);
  }
}
