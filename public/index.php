<?php

require_once __DIR__ . '/../app/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// Buffer output agar redirect / halaman error tetap rapi walau terjadi exception di tengah view.
ob_start();

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

set_exception_handler(static function (Throwable $e): void {
    error_log('[SistemParkir] ' . $e);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    $title = 'Terjadi Kesalahan';
    require __DIR__ . '/../app/views/errors/500.php';
});

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/validation.php';

$router = new Router();

$router->get('/', ['HomeController', 'index']);
$router->get('/login', ['AuthController', 'login']);
$router->post('/login', ['AuthController', 'authenticate']);
$router->get('/logout', ['AuthController', 'logout']);
$router->get('/dashboard', ['DashboardController', 'index']);

$router->get('/customers', ['CustomerController', 'index']);
$router->get('/customers/create', ['CustomerController', 'create']);
$router->post('/customers/store', ['CustomerController', 'store']);
$router->get('/customers/edit', ['CustomerController', 'edit']);
$router->post('/customers/update', ['CustomerController', 'update']);
$router->post('/customers/delete', ['CustomerController', 'delete']);

$router->get('/vehicle-types', ['VehicleTypeController', 'index']);
$router->get('/vehicle-types/create', ['VehicleTypeController', 'create']);
$router->post('/vehicle-types/store', ['VehicleTypeController', 'store']);
$router->get('/vehicle-types/edit', ['VehicleTypeController', 'edit']);
$router->post('/vehicle-types/update', ['VehicleTypeController', 'update']);
$router->post('/vehicle-types/delete', ['VehicleTypeController', 'delete']);

$router->get('/vehicles', ['VehicleController', 'index']);
$router->get('/vehicles/create', ['VehicleController', 'create']);
$router->post('/vehicles/store', ['VehicleController', 'store']);
$router->get('/vehicles/edit', ['VehicleController', 'edit']);
$router->post('/vehicles/update', ['VehicleController', 'update']);
$router->post('/vehicles/delete', ['VehicleController', 'delete']);

$router->get('/parking-areas', ['ParkingAreaController', 'index']);
$router->get('/parking-areas/create', ['ParkingAreaController', 'create']);
$router->post('/parking-areas/store', ['ParkingAreaController', 'store']);
$router->get('/parking-areas/edit', ['ParkingAreaController', 'edit']);
$router->post('/parking-areas/update', ['ParkingAreaController', 'update']);
$router->post('/parking-areas/delete', ['ParkingAreaController', 'delete']);

$router->get('/parking-slots', ['ParkingSlotController', 'index']);
$router->get('/parking-slots/create', ['ParkingSlotController', 'create']);
$router->post('/parking-slots/store', ['ParkingSlotController', 'store']);
$router->get('/parking-slots/edit', ['ParkingSlotController', 'edit']);
$router->post('/parking-slots/update', ['ParkingSlotController', 'update']);
$router->post('/parking-slots/delete', ['ParkingSlotController', 'delete']);

$router->get('/parking-rates', ['ParkingRateController', 'index']);
$router->get('/parking-rates/create', ['ParkingRateController', 'create']);
$router->post('/parking-rates/store', ['ParkingRateController', 'store']);
$router->get('/parking-rates/edit', ['ParkingRateController', 'edit']);
$router->post('/parking-rates/update', ['ParkingRateController', 'update']);
$router->post('/parking-rates/delete', ['ParkingRateController', 'delete']);

$router->get('/users', ['UserController', 'index']);
$router->get('/users/create', ['UserController', 'create']);
$router->post('/users/store', ['UserController', 'store']);
$router->get('/users/edit', ['UserController', 'edit']);
$router->post('/users/update', ['UserController', 'update']);
$router->post('/users/delete', ['UserController', 'delete']);

$router->get('/parking-entries', ['ParkingEntryController', 'index']);
$router->get('/parking-entries/create', ['ParkingEntryController', 'create']);
$router->get('/parking-entries/slots', ['ParkingEntryController', 'slots']);
$router->post('/parking-entries/store', ['ParkingEntryController', 'store']);

$router->get('/parking-exits', ['ParkingExitController', 'index']);
$router->get('/parking-exits/create', ['ParkingExitController', 'create']);
$router->post('/parking-exits/store', ['ParkingExitController', 'store']);

$router->get('/parking-payments', ['ParkingPaymentController', 'index']);
$router->get('/parking-payments/create', ['ParkingPaymentController', 'create']);
$router->post('/parking-payments/store', ['ParkingPaymentController', 'store']);
$router->get('/parking-payments/receipt', ['ParkingPaymentController', 'receipt']);

$router->get('/parking-history', ['ParkingHistoryController', 'index']);
$router->get('/rekap-transaksi', ['TransactionReportController', 'index']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
