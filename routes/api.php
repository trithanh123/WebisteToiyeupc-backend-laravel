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
use App\Http\Controllers\Api\ClientNotificationController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Middleware\CheckadminRole;
use App\Http\Middleware\SecurityHeaders;    
use App\Http\Controllers\Api\AddressController;
use App\Http\Middleware\CheckstaffRole;
use App\Http\Controllers\Api\StockStaffController;
use App\Http\Controllers\Api\OrderStaffController;
use App\Http\Controllers\Api\SupportWarrantyController;
use App\Http\Controllers\Api\AdminTransferController;
use App\Http\Controllers\Api\StaffTransferController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/me', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::get('/my-orders', [PurchaseController::class, 'myOrders'])->middleware('auth:sanctum');
Route::put('/my-orders/{id}/cancel', [PurchaseController::class, 'cancelOrder'])->middleware('auth:sanctum');
Route::get('/addresses', [AddressController::class, 'index'])->middleware('auth:sanctum');
Route::post('/addresses', [AddressController::class, 'store'])->middleware('auth:sanctum');
Route::put('/addresses/{id}', [AddressController::class, 'update'])->middleware('auth:sanctum');
Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault'])->middleware('auth:sanctum');
Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->middleware('auth:sanctum');

Route::prefix('my-notifications')->controller(ClientNotificationController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index');
    Route::put('/read-all', 'markAllAsRead');
    Route::put('/{id}/read', 'markAsRead');
});

Route::prefix('wishlist')->controller(WishlistController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index');
    Route::post('/toggle', 'toggle');
});

Route::prefix('')->controller(AuthController::class)->name('auth.')->group(function () {
    Route::post('/login','login')->name('login')->middleware('throttle:login');
    Route::post('/auth/exchange-code', 'exchangeCode')->name('exchange-code')->middleware('throttle:login');
    Route::post('/register/send-otp','sendRegisterOTP');
    Route::post('/register/verify-otp','verifyRegisterOTP') ;
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
    Route::get('/builder-components','builderComponents')->name('builder-components');
    Route::get('/{id}','show')->where('id', '[0-9]+')->name('show');
    Route::post('/ai-search','aiSearch')->name('ai-search');
    Route::post('/build-pc','buildPc')->name('build-pc');
    Route::get('/{id}/check-stock','checkStock')->where('id', '[0-9]+')->name('check-stock');
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
        Route::post('/upload-image', 'uploadImage')->name('upload-image');
        Route::put('/{id}','update')->where('id', '[0-9]+')->name('update');
        Route::patch('/{id}/toggle','toggleStatus')->where('id', '[0-9]+')->name('toggle');
        Route::delete('/{id}','destroy')->where('id', '[0-9]+')->name('destroy');
        Route::get('/{id}/check-stock', 'checkStock')->where('id', '[0-9]+')->name('check-stock');
    });
    Route::prefix('vouchers')->controller(VoucherController::class)
    ->name('vouchers.')
    ->middleware([CheckadminRole::class])
    ->group(function () {
        Route::get('/','index')->name('index');
        Route::post('/','store')->name('store');
        Route::patch('/{id}','update')->name('update');
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
        Route::put('/{id}/restore','restore')->name('restore');
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
        Route::get('/by-branch/{branchId}','getByBranch')->name('by-branch');
    });
    
    Route::prefix('transfers')->controller(AdminTransferController::class)->name('transfers.')->middleware([CheckadminRole::class])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/serials', 'getAvailableSerials')->name('serials');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}/approve', 'approve')->name('approve');
        Route::put('/{id}/complete', 'complete')->name('complete');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::put('/{id}/reject', 'reject')->name('reject');
    });
    Route::prefix('admin/warranty')->controller(SupportWarrantyController::class)->name('admin.warranty.')->middleware(['auth:sanctum', CheckadminRole::class])->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/search-serial', 'searchSerial')->name('search-serial');
    Route::get('/orders-by-user', 'getOrdersByUser')->name('orders-by-user');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}/status', 'updateStatus')->name('update-status');
    Route::delete('/{id}', 'destroy')->name('destroy');
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

Route::prefix('staff')->controller(StockStaffController::class)->name('staff.')->middleware(['auth:sanctum', CheckstaffRole::class])->group(function () {
    Route::get('/local-stock', 'index')->name('local-stock.index');
    Route::put('/local-stock/{id_khoton}', 'update')->name('local-stock.update');
    Route::get('/warehouse-overview', 'warehouseOverview')->name('warehouse-overview');
});

use App\Http\Controllers\Api\StaffDashboardController;

Route::prefix('staff')->middleware(['auth:sanctum', CheckstaffRole::class])->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'dashboard'])->name('staff.dashboard');
});

Route::prefix('staff/orders')->controller(OrderStaffController::class)->name('staff.orders.')->middleware(['auth:sanctum', CheckstaffRole::class])->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}/status', 'updateStatus')->name('update-status');
});

Route::prefix('staff/transfers')->controller(StaffTransferController::class)->name('staff.transfers.')->middleware(['auth:sanctum', CheckstaffRole::class])->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/serials', 'getAvailableSerials')->name('serials');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}/approve', 'approve')->name('approve');
    Route::put('/{id}/complete', 'complete')->name('complete');
});

Route::prefix('staff/warranty')->controller(SupportWarrantyController::class)->name('staff.warranty.')->middleware(['auth:sanctum', CheckstaffRole::class])->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/search-serial', 'searchSerial')->name('search-serial');
    Route::get('/orders-by-user', 'getOrdersByUser')->name('orders-by-user');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}/status', 'updateStatus')->name('update-status');
});

Route::prefix('admin/warranty')->controller(SupportWarrantyController::class)->name('admin.warranty.')->middleware(['auth:sanctum', CheckadminRole::class])->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/search-serial', 'searchSerial')->name('search-serial');
    Route::get('/orders-by-user', 'getOrdersByUser')->name('orders-by-user');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}/status', 'updateStatus')->name('update-status');
    Route::delete('/{id}', 'destroy')->name('destroy');
});
