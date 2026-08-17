<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// Create an authenticated user
$user = App\Models\User::first();
auth('sanctum')->setUser($user);

$request = Illuminate\Http\Request::create('/api/admin/orders/38/status', 'PUT', ['trang_thai_dh' => 'Đang giao']);
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
