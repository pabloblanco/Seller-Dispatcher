<?php
use App\Http\Controllers\PaguitosController;

Route::get('/sale/associate-paguitos-sale', [PaguitosController::class, 'associatePaguitos'])
  ->name('paguitos.associatePaguitos')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/verify-paguitos', [PaguitosController::class, 'verifyPaguitos'])
  ->name('paguitos.verifyPaguitos')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/save-paguitos', [PaguitosController::class, 'savePaguitos'])
  ->name('paguitos.savePaguitos')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');
