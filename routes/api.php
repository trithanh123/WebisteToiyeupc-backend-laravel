<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\StatisticalController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Middleware\CheckadminRole;
use App\Http\Middleware\SecurityHeaders;    
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/me', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::get('/my-orders', [PurchaseController::class, 'myOrders'])->middleware('auth:sanctum');
Route::put('/my-orders/{id}/cancel', [PurchaseController::class, 'cancelOrder'])->middleware('auth:sanctum');

Route::prefix('')->controller(AuthController::class)->name('auth.')->group(function () {
    Route::post('/register', 'register')->name('register')->middleware('throttle:register');
    Route::post('/login',    'login')->name('login')->middleware('throttle:login');
    Route::get('/auth/{provider}/redirect', 'redirectToProvider')->name('provider.redirect');
    Route::get('/auth/{provider}/callback', 'handleProviderCallback')->name('provider.callback');
    Route::post('/auth/exchange-code', 'exchangeCode')->name('exchange-code')->middleware('throttle:login');
});
Route::prefix('forgot-password')->controller(PasswordResetController::class)->name('forgot-password.')->group(function () {
    Route::post('/send-otp',      'sendOtp')->name('send-otp')->middleware('throttle:forgot-password');
    Route::post('/verify-otp',    'verifyOtp')->name('verify-otp')->middleware('throttle:forgot-password');
    Route::post('/reset-password','resetPassword')->name('reset-password');
});
Route::prefix('categories')->controller(CategoryController::class)
->name('categories.')
->group(function () {
    Route::get('/','index')->name('index');
    Route::get('/all','all')->name('all');
    Route::get('/{id}','show')->name('show');
});
Route::prefix('products')->controller(ProductController::class)
->name('products.')
->group(function () {
    Route::get('/','index')->name('index');
    Route::get('/by-category','byCategory')->name('by-category'); 
    Route::get('/{id}','show')->name('show');
    Route::post('/ai-search','aiSearch')->name('ai-search');
});
Route::prefix('branches')->controller(BranchController::class)->name('branches.')->group(function () {
    Route::get('/', 'index')->name('index');
});
Route::prefix('vouchers')->controller(VoucherController::class)->name('vouchers.')->group(function () {
    Route::get('/active', 'activeVouchers')->name('active');
});
Route::get('/vnpay/callback', [PurchaseController::class, 'vnpayCallback'])->name('purchase.vnpay.callback');
Route::middleware('auth:sanctum')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/checkout', [PurchaseController::class, 'checkout'])->name('checkout');
    Route::prefix('users')->controller(UserController::class)
    ->name('users.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::get('/{id}','show')->name('show');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('categories')->controller(CategoryController::class)
    ->name('categories.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
        Route::patch('/{id}/toggle','toggle')->name('toggle');
    });
    Route::prefix('products')->controller(ProductController::class)
    ->name('products.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('vouchers')->controller(VoucherController::class)
    ->name('vouchers.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('branches')->controller(BranchController::class)
    ->name('branches.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::get('/{id}','show')->name('show');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('personnel')->controller(PersonnelController::class)
    ->name('personnel.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('warehouse')->controller(WarehouseController::class)
    ->name('warehouse.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store','store')->name('store');
        Route::get('/{id}/serials','getSerials')->name('serials');
        Route::put('/{id}','update')->name('update');
        Route::delete('/{id}','destroy')->name('destroy');
    });
    Route::prefix('')->controller(StatisticalController::class)
    ->name('statistical.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
    });
    Route::prefix('orders')->controller(PurchaseController::class)
    ->name('orders.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::get('/monitor','monitorOrders')->name('monitor');
        Route::get('/emergency','handleEmergency')->name('emergency');
        Route::get('/{id}','show')->name('show');
        Route::put('/{id}/status','updateStatus')->name('update_status');
        Route::get('/{id}/print','printInvoice')->name('print');
        Route::put('/{id}/allocate','allocateOrder')->name('allocate');
    });
    Route::prefix('notifications')->controller(NotificationController::class)->name('notifications.')->group(function () {
        Route::get('/','index')->name('index');
        Route::put('/read-all','markAllAsRead')->name('read-all');
        Route::put('/{id}/read','markAsRead')->name('read');
        Route::delete('/{id}','destroy')->name('destroy');
    });
});
