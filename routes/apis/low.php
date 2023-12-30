<?php
use App\Http\Controllers\LowController;

Route::prefix('low')->group(function () {

  Route::get('/new-request', [LowController::class, 'viewNewRequest'])
    ->name('low.new-request')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

  Route::post('/find-sales-user', [LowController::class, 'getSalesUser'])
    ->name('low.getSalesUser')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

  Route::post('/find-deuda-user', [LowController::class, 'getDeudaUser'])
    ->name('low.getDeudaUser')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

  Route::post('/send-low-user', [LowController::class, 'regLowUser'])
    ->name('low.regLowUser')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

  Route::get('/view-requests-list', [LowController::class, 'viewRequestsList'])
    ->name('low.viewRequestsList')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

  Route::post('/get-requests-list', [LowController::class, 'getRequestsList'])
    ->name('low.getRequestsList')
    ->middleware('startLogTime')
    ->middleware('userLogged')
    ->middleware('endLogTime');

});
