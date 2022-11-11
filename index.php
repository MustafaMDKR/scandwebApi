<?php
declare(strict_types=1);

use API\controllers\ProductController;
use API\core\Database;
use API\models\ProductModel;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

set_error_handler("\API\core\ErrorHandler::handleError");
set_exception_handler("\API\core\ErrorHandler::handleException");

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$driver = $_ENV['DRIVER'];
$host = $_ENV['HOST'];
$name = $_ENV['NAME'];
$user = $_ENV['USER'];
$pass = $_ENV['PASS'];

$parts = explode('/', $_SERVER['REQUEST_URI']);

if ($parts[1] != 'products') {
    http_response_code(404);
    exit;
}

$id = $parts[2] ?? null;

$db = new Database($driver, $host, $name, $user, $pass);
$model = new ProductModel($db);
$controller = new ProductController($model);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
