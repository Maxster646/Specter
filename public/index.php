<?php

require __DIR__ . '/../vendor/autoload.php';

use Specter\Core\App;

$app = new App();

// Global middleware: simple request logger
$app->middleware(function () {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    error_log("[$method] $uri");
});

// Home route
$app->get('/', function () {
    return 'Welcome to Specter Framework';
});

// Route with parameter
$app->get('/user/{id}', function ($id) {
    return [
        'status' => 'ok',
        'user_id' => $id
    ];
});

// POST route that echoes received JSON
$app->post('/echo', function () {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    return ['received' => $data];
});

// Run the application
$app->run();
