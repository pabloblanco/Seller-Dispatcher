<?php
use App\Http\Controllers\SellerFiberController;

Route::get('/fiber/sale-fiber', [SellerFiberController::class, 'index'])
  ->name('sellerFiber.index')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/show-client', [SellerFiberController::class, 'showClient'])
  ->name('sellerFiber.showClient')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-plan', [SellerFiberController::class, 'getPlan'])
  ->name('sellerFiber.getPlan')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/find-installer/{search?}', [SellerFiberController::class, 'findInstaller'])
  ->name('seller.findInstaller')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/register-install', [SellerFiberController::class, 'regInstall'])
  ->name('sellerFiber.regInstall')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/detail-install-modal', [SellerFiberController::class, 'detailInsModal'])
  ->name('sellerFiber.detailInsModal')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/load-more-detail-install-modal', [SellerFiberController::class, 'loadMoredetailInsModal'])
  ->name('sellerFiber.loadMoredetailInsModal')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/save-change-install-modal', [SellerFiberController::class, 'saveDetailInsModal'])
  ->name('sellerFiber.saveDetailInsModal')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/delete-install', [SellerFiberController::class, 'deleteInstall'])
  ->name('sellerFiber.deleteInstall')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/fiber/do-install/{id?}', [SellerFiberController::class, 'doInstall'])
  ->name('sellerFiber.doInstall')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-msisdn', [SellerFiberController::class, 'getMSISDNSFiber'])
  ->name('sellerFiber.getMSISDNSFiber')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/do-register-815', [SellerFiberController::class, 'doRegister'])
  ->name('sellerFiber.doRegister')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/detail-pending-paid-install-modal', [SellerFiberController::class, 'detailPendingPaidInsModal'])
  ->name('sellerFiber.detailPendingPaidInsModal')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/mark-as-paid-install', [SellerFiberController::class, 'markAsPaidInstall'])
  ->name('sellerFiber.markAsPaidInstall')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-components-address', [SellerFiberController::class, 'getCompAddress'])
  ->name('sellerFiber.getCompAddress')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/fiber/pay-pending/{page?}/{search?}', [SellerFiberController::class, 'payPending'])
  ->name('sellerFiber.payPending')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/fiber/pay-pending-list-ajax/{page?}/{search?}', [SellerFiberController::class, 'payPendingAjax'])
  ->name('sellerFiber.payPendingAjax')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-citys-fiber', [SellerFiberController::class, 'getCitys'])
  ->name('sellerFiber.getCitys')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-olts-fiber', [SellerFiberController::class, 'getOlts'])
  ->name('sellerFiber.getOlts')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-mapFiber', [SellerFiberController::class, 'getMapCoverage'])
  ->name('sellerFiber.getMapCoverage')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-getPlanes', [SellerFiberController::class, 'getPlanes'])
  ->name('sellerFiber.getPlanes')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-msisdn-generate', [SellerFiberController::class, 'getMSISDNGenerate'])
  ->name('sellerFiber.getMSISDNGenerate')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/cheking-mac', [SellerFiberController::class, 'chekingMac'])
  ->name('sellerFiber.chekingMac')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/change-mac', [SellerFiberController::class, 'changeMac'])
  ->name('sellerFiber.changeMac')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/cheking-coverage-fiber', [SellerFiberController::class, 'chekingCoverageFiber'])
  ->name('sellerFiber.chekingCoverageFiber')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-coord-to-address', [SellerFiberController::class, 'getCoordFromAddress'])
  ->name('sellerFiber.getCoordFromAddress')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-nodes-red', [SellerFiberController::class, 'getNodesRed'])
  ->name('sellerFiber.getNodesRed')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-calendar', [SellerFiberController::class, 'getCalendar'])
  ->name('sellerFiber.getCalendar')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-clock', [SellerFiberController::class, 'getClock'])
  ->name('sellerFiber.getClock')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-list-installer', [SellerFiberController::class, 'getListInstaller'])
  ->name('sellerFiber.getListInstaller')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/get-typification', [SellerFiberController::class, 'getTypification'])
  ->name('sellerFiber.getTypification')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/cancelInstalation', [SellerFiberController::class, 'cancelInstalation'])
  ->name('sellerFiber.cancelInstalation')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/getQrForce', [SellerFiberController::class, 'getQrForce'])
  ->name('sellerFiber.getQrForce')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/sendMailQr', [SellerFiberController::class, 'sendMailQr'])
  ->name('sellerFiber.sendMailQr')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/verifyQr', [SellerFiberController::class, 'verifyQr'])
  ->name('sellerFiber.verifyQr')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/fiber/installerSurvey/{id?}', [SellerFiberController::class, 'installerSurvey'])
  ->name('sellerFiber.installerSurvey')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/doSurvey', [SellerFiberController::class, 'doSurvey'])
  ->name('sellerFiber.doSurvey')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/setQrForce', [SellerFiberController::class, 'setQrForce'])
  ->name('sellerFiber.setQrForce')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/getPaymentSubscrip', [SellerFiberController::class, 'getPaymentSubscrip'])
  ->name('sellerFiber.getPaymentSubscrip')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/changerInCash', [SellerFiberController::class, 'changerInCash'])
  ->name('sellerFiber.changer_incash')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/cancelQrPayment', [SellerFiberController::class, 'cancelQrPayment'])
  ->name('sellerFiber.cancelQrPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/reloadQrPayment', [SellerFiberController::class, 'reloadQrPayment'])
  ->name('sellerFiber.reloadQrPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/getMailPayment', [SellerFiberController::class, 'getMailPayment'])
  ->name('sellerFiber.getMailPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/setMailPayment', [SellerFiberController::class, 'setMailPayment'])
  ->name('sellerFiber.setMailPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/setChangerPack', [SellerFiberController::class, 'setChangerPack'])
  ->name('sellerFiber.setChangerPack')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/sendMailQrPayment', [SellerFiberController::class, 'sendMailQrPayment'])
  ->name('sellerFiber.sendMailQrPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/verifyPayment', [SellerFiberController::class, 'verifyPayment'])
  ->name('sellerFiber.verifyPayment')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/getInstallerCharges', [SellerFiberController::class, 'getInstallerCharges'])
  ->name('sellerFiber.getInstallerCharges')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/verifyInfoPort', [SellerFiberController::class, 'verifyInfoPort'])
  ->name('sellerFiber.verifyInfoPort')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/findInventoryAsigned', [SellerFiberController::class, 'findInventoryAsigned'])
  ->name('sellerFiber.findInventoryAsigned')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/viewProcessFail', [SellerFiberController::class, 'viewProcessFail'])
  ->name('sellerFiber.viewProcessFail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/processFail', [SellerFiberController::class, 'processFail'])
  ->name('sellerFiber.processFail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/refresFail', [SellerFiberController::class, 'refresFail'])
  ->name('sellerFiber.refresFail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/refresComponent', [SellerFiberController::class, 'refresComponent'])
  ->name('sellerFiber.refresComponent')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/cantToken', [SellerFiberController::class, 'cantToken'])
  ->name('sellerFiber.cantToken')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/newToken', [SellerFiberController::class, 'newToken'])
  ->name('sellerFiber.newToken')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/verifyPhone', [SellerFiberController::class, 'verifyPhone'])
  ->name('sellerFiber.verifyPhone')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/requestAutorized', [SellerFiberController::class, 'requestAutorized'])
  ->name('sellerFiber.requestAutorized')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/checkingAutorized', [SellerFiberController::class, 'checkingAutorized'])
  ->name('sellerFiber.checkingAutorized')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/reSendForceURL', [SellerFiberController::class, 'reSendForceURL'])
  ->name('sellerFiber.reSendForceURL')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/fiber/checkingActiveFiber', [SellerFiberController::class, 'checkingActiveFiber'])
  ->name('sellerFiber.checkingActiveFiber')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');
