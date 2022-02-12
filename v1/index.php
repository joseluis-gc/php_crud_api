<?php

declare(strict_types=1);

ini_set('display_errors','On');

require dirname(__DIR__) . "/vendor/autoload.php";

set_error_handler("\\ErrorHandler::handleError");
set_exception_handler("\\ErrorHandler::handleException");


//$dotenv = Dotenv\Dotenv::createImmutable(dir(__DIR__));
//$dotenv->load();
$path =  parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$parts = explode("/", $path);

//print_r($parts);
$resource = $parts[4];
$id = $parts[5] ?? null;
//echo $resource . ',' . $id;
//echo $_SERVER["REQUEST_METHOD"];

if($resource != "tasks"){
    //header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found.");
    http_response_code(404);
    exit();
}

//require dirname(__DIR__) . "/src/Controller.php";

header("Content-type: application/json; charset=UTF-8");

$database = new Database("localhost", "php_api", "root", "");

//$database->getConnection();

$task_gateway = new TaskGateWay($database);

$controller = new Controller($task_gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);


