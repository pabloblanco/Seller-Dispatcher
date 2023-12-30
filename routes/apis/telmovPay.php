<?php
use App\Http\Controllers\SellerTelmovPayController;

Route::post('/sale/cheking-identy-telmov', [SellerTelmovPayController::class, 'chekingIdentiTelmov'])
  ->name('seller.chekingIdentiTelmov')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/list-model-telmov', [SellerTelmovPayController::class, 'listModelSmartPhone'])
  ->name('seller.listModelSmartPhone')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/sale/init-telmov', [SellerTelmovPayController::class, 'initTelmov'])
  ->name('telmovpay.initTelmov')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');
/*
Route::get('/sale/asociate-finance-telmov', [SellerTelmovPayController::class, 'asociateFinanceTelmov'])
->name('telmovpay.asociateFinanceTelmov')
->middleware('startLogTime')
->middleware('userLogged')
->middleware('endLogTime');
 */
/*
Route::post('/sale/verify-init-telmov', [SellerTelmovPayController::class, 'verifyInitTelmov'])
->name('telmovpay.verifyInitTelmov')
->middleware('startLogTime')
->middleware('userLogged')
->middleware('endLogTime');
 */
/*
Route::post('/sale/associate-cash-telmov', [SellerTelmovPayController::class, 'associateCashTelmov'])
->name('telmovpay.associateCashTelmov')
->middleware('startLogTime')
->middleware('userLogged')
->middleware('endLogTime');
 */
Route::post('/sale/save-contact-telmov', [SellerTelmovPayController::class, 'updateConctactClient'])
  ->name('telmovpay.updateConctactClient')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/step1Init', [SellerTelmovPayController::class, 'step1InitFinance'])
  ->name('telmovpay.step1InitFinace')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/cancelTelmov', [SellerTelmovPayController::class, 'cancelTelmov'])
  ->name('telmovpay.cancelTelmov')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/chekingMail', [SellerTelmovPayController::class, 'chekingMail'])
  ->name('telmovpay.chekingMail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/requestQr', [SellerTelmovPayController::class, 'requestQr'])
  ->name('telmovpay.requestQr')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/requestQrVerifyLast', [SellerTelmovPayController::class, 'requestQrVerifyLast'])
  ->name('telmovpay.requestQrVerifyLast')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/buildPlan', [SellerTelmovPayController::class, 'buildPlan'])
  ->name('telmovpay.buildPlan')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/getModels', [SellerTelmovPayController::class, 'getModels'])
  ->name('telmovpay.getModels')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/initContract', [SellerTelmovPayController::class, 'initContract'])
  ->name('telmovpay.initContract')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/endContract', [SellerTelmovPayController::class, 'endContract'])
  ->name('telmovpay.endContract')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/endEnrole', [SellerTelmovPayController::class, 'endEnrole'])
  ->name('telmovpay.endEnrole')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/telmov/sincronizeApp', [SellerTelmovPayController::class, 'sincronizeApp'])
  ->name('telmovpay.sincronizeApp')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');
