<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Test the API endpoint directly
$request = \Illuminate\Http\Request::create('/api/catalog/labor/5', 'GET');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content:\n";
echo $response->getContent() . "\n";

$kernel->terminate($request, $response);
