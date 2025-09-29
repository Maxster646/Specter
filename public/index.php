<?php

use Specter\Core\App;

require __DIR__ . '/../vendor/autoload.php';

$app = new App();

// --------------------
// Simple Routes
// --------------------
$app->get('/', function () use ($app) {
    return $app->json(['message' => 'Welcome to Specter Framework']);
});

$app->get('/user/{id}', function ($id) use ($app) {
    return $app->json(['status' => 'ok', 'user_id' => $id]);
});

// --------------------
// Route Groups Example
// --------------------
$app->group('/api', function ($app) {
    $app->get('/status', function () use ($app) {
        return $app->json(['status' => 'API is running']);
    });

    $app->post('/echo', function () use ($app) {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        return $app->json(['echo' => $data]);
    });
});

$app->run();
