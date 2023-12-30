<?php

use App\Http\Controllers\chargerController;
use App\Http\Controllers\clientController;
use App\Http\Controllers\coordinationController;
use App\Http\Controllers\CoppelController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\identityController;
use App\Http\Controllers\InstallmentsController;
use App\Http\Controllers\InventaryController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\migrationController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\PayJoyController;
use App\Http\Controllers\reportFiberController;
use App\Http\Controllers\sellerController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\InventoryInstallersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//Rutas para login
Route::match(['get', 'post'], '/', [loginController::class, 'index'])
  ->name('login')
  ->middleware('startLogTime')
  ->middleware('endLogTime');

Route::post('/reset-password', [loginController::class, 'resetPassword'])
  ->name('login.resetPassword')
  ->middleware('startLogTime')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/change-password/{hash}', [loginController::class, 'changePassword'])
  ->name('login.changePassword')
  ->middleware('startLogTime')
  ->middleware('endLogTime');

Route::get('/logout', [loginController::class, 'logout'])
  ->name('logout')
  ->middleware('startLogTime')
  ->middleware('endLogTime');

//Dashboard
Route::get('/dashboard/{email?}', [dashboardController::class, 'index'])
  ->name('dashboard')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/dashboard/download-inv', [dashboardController::class, 'downloadInv'])
  ->name('dashboard.downloadInv')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/dashboard/serviciability', [dashboardController::class, 'serviciability'])
  ->name('dashboard.serviciability')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/dashboard/get-sales', [dashboardController::class, 'getTotalSalesByDate'])
  ->name('getTotalSalesByDate')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/dashboard/regconex', [dashboardController::class, 'registerConection'])
  ->name('dashboard.regconex')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Clientes
Route::match(['get', 'post'], '/client-list/client-edit/{id}', [clientController::class, 'editClient'])
  ->name('clientNP.edit')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/client-list/{page?}/{search?}', [clientController::class, 'listClient'])
  ->name('client.listClient')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/client-list-ajax/{page?}/{search?}', [clientController::class, 'listClientAjax'])
  ->name('client.listClientAjax')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/client-get-by-dn', [clientController::class, 'clientGetByDN'])
  ->name('client.getByDn')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Prospectos
Route::get('/prospect/client-list/{page?}/{search?}', [clientController::class, 'list'])
  ->name('client.list')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/prospect/client-list-ajax/{page?}/{search?}', [clientController::class, 'listAjax'])
  ->name('client.listAjax')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/prospect/client-edit/{id}', [clientController::class, 'edit'])
  ->name('client.edit')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/prospect/client-register', [clientController::class, 'register'])
  ->name('client.register')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/prospect/client-register-ajax', [clientController::class, 'registerAjax'])
  ->name('client.registerAjax')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Citas
Route::get('/date/client-list/{page?}/{search?}', [clientController::class, 'list'])
  ->name('date.new')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/date/client-schedule-list', [clientController::class, 'listSchedule'])
  ->name('client.scheduleList')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/date/get-schedule/{date?}', [clientController::class, 'getSchedule'])
  ->name('client.getSchedule')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/date/new-schedule/{dni?}', [clientController::class, 'newSchedule'])
  ->name('call.newschedule')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/date/date-list', [clientController::class, 'listDate'])
  ->name('call.listDate')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/date/edit-schedule/{idSche}', [clientController::class, 'editschedule'])
  ->name('client.editschedule')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Sales
Route::get('/sale', [sellerController::class, 'index'])
  ->name('seller.index')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/client/find/{search?}', [sellerController::class, 'findClient'])
  ->name('seller.findClient')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/client/showClientN', [sellerController::class, 'showClientN'])
  ->name('seller.showClientN')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/valid-imei', [sellerController::class, 'validImei'])
  ->name('seller.validImei')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/valid-qty-dn/{type}', [sellerController::class, 'validQtyDns'])
  ->name('seller.validQtyDns')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/packs', [sellerController::class, 'showPacks'])
  ->name('seller.showPacks')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/packs-mov', [sellerController::class, 'showPackMov'])
  ->name('seller.showPackMov')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/seller/process-sale', [sellerController::class, 'processSale'])
  ->name('seller.processSale')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('isLocked')
  ->middleware('endLogTime');

Route::match(['get', 'post'], '/status-line', [sellerController::class, 'getStatusNumber'])
  ->name('seller.statusNumber')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/seller/valid-number-sale', [sellerController::class, 'validNumberSale'])
  ->name('seller.validNumberSale')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/seller/valid-identity', [identityController::class, 'validIdentity'])
  ->name('seller.validIdentity')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/seller/check-valid-identity', [identityController::class, 'checkValidIdentity'])
  ->name('seller.checkValidIdentity')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/seller/redirect-truora', [identityController::class, 'redirectTruora'])
  ->name('seller.redirectTruora');

/*rutas CRUD telmovPay*/
require_once 'apis/telmovPay.php';

/*rutas CRUD Fibra*/
require_once 'apis/fiber.php';

//Migraciones
Route::get('/migrations', [migrationController::class, 'migrations'])
  ->name('seller.migrations')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/migrations/find-client', [migrationController::class, 'findClientForMigration'])
  ->name('seller.findClientMigration')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/migrations/update-client', [migrationController::class, 'updateClient'])
  ->name('seller.updateClientM')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/migrations/do-migration', [migrationController::class, 'doMigration'])
  ->name('seller.doMigration')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//venta con solo desbloqueo de producto
Route::get('/sale-product', [sellerController::class, 'saleProduct'])
  ->name('seller.onlyProduct')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale-product/get-pack', [sellerController::class, 'getPackProduct'])
  ->name('seller.getPackProduct')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale-product/confirm-sale', [sellerController::class, 'confirmSaleProduct'])
  ->name('seller.confirmSaleProduct')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale-product/do-sale', [sellerController::class, 'doSaleProduct'])
  ->name('seller.doSaleProduct')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Recargas
Route::match(['get', 'post'], '/sale/charger', [chargerController::class, 'index'])
  ->name('charger.index')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/charger/find', [chargerController::class, 'find'])
  ->name('charger.find')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Coordinacion
Route::get('/coordination/assign-stock', [coordinationController::class, 'stock'])
  ->name('coordination.stock')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/stock-seller', [coordinationController::class, 'findInveSeller'])
  ->name('coordination.findInveSeller')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/add-stock-seller', [coordinationController::class, 'addStock'])
  ->name('coordination.addStock')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('isLocked')
  ->middleware('endLogTime');

Route::post('/coordination/remove-stock-seller', [coordinationController::class, 'removeStock'])
  ->name('coordination.removeStock')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('isLocked')
  ->middleware('endLogTime');

Route::get('/coordination/effective-reception/{email?}', [coordinationController::class, 'reception'])
  ->name('coordination.reception')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/find-relation-users', [coordinationController::class, 'findRelationUsers'])
  ->name('findRelationUsers')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/effective-reception-noti', [coordinationController::class, 'receptionNoti'])
  ->name('coordination.receptionNoti')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/effective-reception-list/{email?}', [coordinationController::class, 'receptionList'])
  ->name('coordination.receptionList')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/effective-reception-status', [coordinationController::class, 'receptionStatus'])
  ->name('coordination.receptionStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Información
Route::get('/comparative', [sellerController::class, 'comparative'])
  ->name('seller.comparative')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reportar entrega de efectivo a coordinador
Route::match(['get', 'post'], '/cash-delivery', [sellerController::class, 'cashDelivery'])
  ->name('seller.cashDelivery')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/cash-delivery-deny', [sellerController::class, 'cashDeliveryDeny'])
  ->name('seller.cashDeliveryDeny')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reporte de altas para coordinador
Route::get('/report-activations', [coordinationController::class, 'reportActivations'])
  ->name('coordination.reportActivations')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-activations', [coordinationController::class, 'getReportActivarions'])
  ->name('coordination.getReportActivarions')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reporte de conciliaciones
Route::get('/report-concilations', [coordinationController::class, 'reportConcilations'])
  ->name('coordination.reportConcilations')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-concilations', [coordinationController::class, 'reportgetConcilations'])
  ->name('coordination.reportgetConcilations')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/download-report-concilations', [coordinationController::class, 'downloadReportConc'])
  ->name('coordination.downloadReportConc')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reporte de ventas con conciliadas entre vendedores y coordinadores
Route::get('/report-sales-not-conc', [coordinationController::class, 'reportUnConcSales'])
  ->name('coordination.reportUnConcSales')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-unconc-sales', [coordinationController::class, 'getReportUnConcSales'])
  ->name('coordination.getReportUnConcSales')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reporte de fibra
Route::get('/get-report-fiber-pending', [reportFiberController::class, 'getReportFiberPending'])
  ->name('fiber.getFiberPendingReport')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

/*Route::get('/set-permits', [coordinationController::class, 'setPermits'])
->name('coordination.setPermits')
->middleware('userLogged');*/

//Nomina
Route::get('/nomina', [NominaController::class, 'index'])
  ->name('Nomina.index')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/nomina/get-file/{type?}', [NominaController::class, 'getFile'])
  ->name('Nomina.getFile')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/nomina/get-file-contract', [NominaController::class, 'getFileContract'])
  ->name('Nomina.getFileContract')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Venta en abono
Route::get('/installments/requests', [InstallmentsController::class, 'requests'])
  ->name('installments.requests')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/installments/pending-pay/{saleid?}', [InstallmentsController::class, 'pendingPay'])
  ->name('installments.pendingPay')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/installments/pending-pay-seller/{saleid?}', [InstallmentsController::class, 'pendingPaySeller'])
  ->name('installments.pendingPaySeller')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/get-pending-pay', [InstallmentsController::class, 'getPendingPay'])
  ->name('installments.getPendingPay')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/do-pay', [InstallmentsController::class, 'doPay'])
  ->name('installments.doPay')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/pay-notification', [InstallmentsController::class, 'payNotification'])
  ->name('installments.payNotification')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/find-client', [InstallmentsController::class, 'findClient'])
  ->name('installments.findClient')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/check-request', [InstallmentsController::class, 'checkRequest'])
  ->name('installments.checkRequest')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/accept-request', [InstallmentsController::class, 'acceptRequest'])
  ->name('installments.acceptRequest')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/installments/seller-requests', [InstallmentsController::class, 'sellerRequests'])
  ->name('installments.sellerRequests')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/installments/seller-requests/final-step', [InstallmentsController::class, 'finalStep'])
  ->name('installments.finalStep')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/installments/report-modems-inst/{userc?}', [InstallmentsController::class, 'reportsMI'])
  ->name('installments.reportsMI')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');


//Inventario instaladores view
Route::get('/installers/requests/assign-stock', [InventoryInstallersController::class, 'stock'])
  ->name('inventory.installers.stock')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Recupera instaladores asociados al jefe de instaladores
Route::post('/find-relation-installers', [InventoryInstallersController::class, 'findRelationInstallers'])
  ->name('findRelationInstallers')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Recupera inventario del instalador
Route::post('/installers/stock-installer', [InventoryInstallersController::class, 'findInveInstaller'])
->name('inventory.installers.findInveInstaller')
  ->middleware('startLogTime')
->middleware('userLogged')
->middleware('endLogTime');

//Jefe de instaladores asigna inventario al instalador
Route::post('/installers/add-stock-seller', [InventoryInstallersController::class, 'addStock'])
->name('inventory.installers.addStock')
->middleware('startLogTime')
->middleware('userLogged')
->middleware('isLocked')
->middleware('endLogTime');

//Jefe de instaladores retira inventario del instalador
Route::post('/installers/remove-stock-seller', [InventoryInstallersController::class, 'removeStock'])
->name('inventory.installers.removeStock')
->middleware('startLogTime')
->middleware('userLogged')
->middleware('isLocked')
->middleware('endLogTime');


  //Payjoy
Route::post('/sale/save-payjoy', [PayJoyController::class, 'savePayjoy'])
  ->name('payjoy.savePayjoy')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::get('/sale/associate-payjoy-sale', [PayJoyController::class, 'associatePayjoy'])
  ->name('payjoy.associatePayjoy')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/sale/verify-payjoy', [PayJoyController::class, 'verifyPayjoy'])
  ->name('payjoy.verifyPayjoy')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/webhook/payjoy', [PayJoyController::class, 'webhook'])
  ->name('payjoy.webhook');

Route::get('/webhook/devices', [PayJoyController::class, 'devices'])
  ->name('payjoy.devices');

//Coppel
Route::get('/coppel/test-coppel', [CoppelController::class, 'testCoppel'])
  ->name('testCoppels')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Listado de guias pendientes
Route::get('/coordination/pending-folios', [InventaryController::class, 'pendingFolios'])
  ->name('inventory.pendingFolios')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/box-detail', [InventaryController::class, 'boxDetail'])
  ->name('inventory.boxDetail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/coordination/accept-box-detail', [InventaryController::class, 'acceptBoxDetail'])
  ->name('inventory.acceptBoxDetail')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Listado de dns en estatus naranja o rojo
Route::get('/inventary/list-dns-orange-red', [InventaryController::class, 'listDNOOR'])
  ->name('inventory.listDNOOR')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/searchList-dns-orange-red', [InventaryController::class, 'viewListDN_OR'])
  ->name('inventory.searchListDN_OR')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/download-inv', [InventaryController::class, 'downloadInvNoty'])
  ->name('inventory.downloadInvNoty')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/request-change-status', [InventaryController::class, 'changeStatus'])
  ->name('inventory.changeStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/verify-dn-status', [InventaryController::class, 'verifyDnStatus'])
  ->name('inventory.verifyDnStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/verify-status-request', [InventaryController::class, 'chekingRequestStatus'])
  ->name('inventory.chekingRequestStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//vista para aceptar o rechazar inventario pre-asignado
Route::get('/inventary/pre-assigned', [InventaryController::class, 'preassignedInv'])
  ->name('inventory.preassigned')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//vista estatus inventario pre-asignado
Route::get('/inventary/pre-assigned-status', [InventaryController::class, 'preassignedStatus'])
  ->name('inventory.preassignedStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/reject-pre-assigned', [InventaryController::class, 'rejectPreassignedInv'])
  ->name('inventory.rejectPreassignedInv')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/inventary/accept-pre-assigned', [InventaryController::class, 'acceptPreassignedInv'])
  ->name('inventory.acceptPreassignedInv')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Reporte Estado de deudas
Route::get('/report-debt-status', [coordinationController::class, 'reportDebtStatus'])
  ->name('coordination.reportDebtStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-debt-status', [coordinationController::class, 'reportgetDebtStatus'])
  ->name('coordination.reportgetDebtStatus')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-debt-status-ups', [coordinationController::class, 'reportgetDebtStatusUps'])
  ->name('coordination.reportgetDebtStatusUps')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-debt-status-rec', [coordinationController::class, 'reportgetDebtStatusRec'])
  ->name('coordination.reportgetDebtStatusRec')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-debt-status-del', [coordinationController::class, 'reportgetDebtStatusDel'])
  ->name('coordination.reportgetDebtStatusDel')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

Route::post('/get-report-debt-status-dep', [coordinationController::class, 'reportgetDebtStatusDep'])
  ->name('coordination.reportgetDebtStatusDep')
  ->middleware('startLogTime')
  ->middleware('userLogged')
  ->middleware('endLogTime');

//Probar emails
// Route::get('/test/view-email', [TestController::class, 'viewEmail'])
//   ->name('test.viewEmail')
//   ->middleware('startLogTime')
//   ->middleware('userLogged')
//   ->middleware('endLogTime');

// Route::get('/test/test-email/{email?}', [TestController::class, 'testEmail'])
//   ->name('test.testEmail')
//   ->middleware('startLogTime')
//   ->middleware('userLogged')
//   ->middleware('endLogTime');

/*rutas CRUD Bajas de vendedores*/
require_once 'apis/low.php';

/*rutas CRUD Paguitos*/
require_once 'apis/paguitos.php';
