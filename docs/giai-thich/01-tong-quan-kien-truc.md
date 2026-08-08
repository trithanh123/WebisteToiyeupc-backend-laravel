# 01 — TỔNG QUAN KIẾN TRÚC

## 1.1. Hệ thống gồm mấy phần?

```
┌────────────────┐      HTTP/JSON      ┌──────────────────────┐
│  Frontend      │ ──────────────────► │  Backend Laravel 11  │
│  (React/Vue)   │ ◄────────────────── │  (thư mục này)       │
└────────────────┘                      └──────────┬───────────┘
                                                   │
                            ┌──────────────────────┼────────────────────┐
                            │                      │                    │
                            ▼                      ▼                    ▼
                    ┌──────────────┐      ┌────────────────┐   ┌───────────────┐
                    │  MySQL       │      │ Python Service │   │  VNPay        │
                    │  (dữ liệu)   │      │ FastAPI :8001  │   │  (thanh toán) │
                    └──────────────┘      │  + Qdrant      │   └───────────────┘
                                          │  (vector DB)   │
                                          └────────────────┘
```

Backend Laravel đóng vai trò **API server thuần** (stateless, trả JSON), không render giao diện.
Xác thực bằng **Laravel Sanctum** (token-based), không dùng session cookie.

---

## 1.2. `bootstrap/app.php` — điểm khởi động ứng dụng

Từ Laravel 11, file `Kernel.php` bị bỏ, mọi cấu hình gom vào đây.

```php
return Application::configure(basePath: dirname(__DIR__))
```
- `Application::configure()` tạo instance ứng dụng.
- `basePath: dirname(__DIR__)` — `__DIR__` là `/bootstrap`, `dirname()` lùi 1 cấp → thư mục gốc dự án.

```php
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
```
- Khai báo 3 file định tuyến. File `api.php` được Laravel tự động gắn tiền tố `/api` và
  gán middleware group `api` (bao gồm throttle + `SubstituteBindings`).
- `health: '/up'` tạo sẵn endpoint `GET /up` trả 200 — dùng cho load balancer / Docker healthcheck.

```php
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
```
- `append()` gắn `SecurityHeaders` vào **cuối** chuỗi middleware toàn cục → chạy cho **mọi** request.
- Giải thích cho thầy: middleware toàn cục khác middleware theo route (`CheckadminRole`) ở chỗ
  cái này không cần khai báo lại ở từng route.

```php
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        });
    })->create();
```
- Bắt riêng lỗi `AuthenticationException`. Mặc định Laravel sẽ **redirect** về route `login`
  (dành cho web). Vì đây là API nên phải ép trả JSON 401, nếu không frontend sẽ nhận HTML.
- `->create()` hoàn tất và trả về `$app`.

---

## 1.3. `routes/api.php` — bản đồ toàn bộ API

### Nguyên tắc tổ chức

Route được chia theo **3 tầng quyền**:

| Tầng | Middleware | Ví dụ |
|------|-----------|-------|
| Công khai | (không có) | `GET /api/products`, `POST /api/login` |
| Khách đã đăng nhập | `auth:sanctum` | `GET /api/me`, `GET /api/my-orders` |
| Admin | `auth:sanctum` + `CheckadminRole` | `/api/admin/users`, `/api/admin/orders` |
| Nhân viên | `auth:sanctum` + `CheckstaffRole` | `/api/staff/orders`, `/api/staff/transfers` |

### Đọc chi tiết

```php
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
```
- Route closure mặc định của Laravel. `$request->user()` lấy user từ token Bearer.
- Guard `sanctum` sẽ đọc header `Authorization: Bearer <token>`, tra bảng `personal_access_tokens`.

```php
Route::get('/me',  [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/me',  [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
```
- Cùng URI `/me` nhưng khác HTTP verb → RESTful. GET = đọc, PUT = cập nhật.

```php
Route::prefix('my-notifications')
     ->controller(ClientNotificationController::class)
     ->middleware('auth:sanctum')
     ->group(function () {
        Route::get('/', 'index');
        Route::put('/read-all', 'markAllAsRead');
        Route::put('/{id}/read', 'markAsRead');
});
```
- `prefix()` — mọi route bên trong có tiền tố `/api/my-notifications`.
- `controller()` — khỏi lặp lại tên controller ở từng dòng.
- **Lưu ý thứ tự**: `/read-all` phải đặt **trước** `/{id}/read`? Ở đây không xung đột vì
  `/read-all` là 1 segment còn `/{id}/read` là 2 segment. Nhưng nếu có `/{id}` thì phải cẩn thận —
  Laravel khớp theo thứ tự khai báo, `read-all` sẽ bị hiểu là `{id}`.

```php
Route::post('/login','login')->name('login')->middleware('throttle:login');
```
- `throttle:login` dùng rate limiter tên `login` định nghĩa trong `AppServiceProvider` (xem 1.5).
- Đây là biện pháp chống **brute-force** mật khẩu.

```php
Route::get('/{id}','show')->where('id', '[0-9]+')->name('show');
Route::post('/ai-search','aiSearch')->name('ai-search');
```
- `where('id','[0-9]+')` ràng buộc `{id}` chỉ nhận số. Nhờ vậy `/products/ai-search`
  **không** bị khớp nhầm vào `/products/{id}`. Đây là kỹ thuật quan trọng, thầy có thể hỏi.

```php
Route::middleware('auth:sanctum')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/checkout', [PurchaseController::class, 'checkout'])->name('checkout');
    Route::prefix('users')->controller(UserController::class)
        ->middleware([CheckadminRole::class])
        ->group(...);
```
- Nhóm ngoài chỉ yêu cầu đăng nhập, các nhóm con mới yêu cầu quyền admin.
- **Điểm đáng chú ý (thầy có thể bắt)**: `/api/admin/checkout` nằm trong prefix `admin` nhưng
  **không** có `CheckadminRole` — đây là API khách hàng đặt hàng, chỉ cần đăng nhập.
  Đặt trong prefix `admin` là **đặt sai chỗ về mặt ngữ nghĩa**. Nếu thầy hỏi, thừa nhận đây
  là điểm cần refactor: nên tách ra `/api/checkout`.

```php
Route::prefix('admin/warranty')->...->middleware(['auth:sanctum', CheckadminRole::class])->group(...)
```
- Khối này bị **khai báo 2 lần** (dòng 169 và dòng 239). Lần khai báo ở dòng 169 nằm bên trong
  nhóm `prefix('admin')` nên URI thực tế là `/api/admin/admin/warranty` — lặp chữ `admin`.
  Khối ở dòng 239 mới đúng: `/api/admin/warranty`.
  → Đây là **route chết**, nên xóa. Nếu thầy phát hiện, trả lời: "route thừa do copy, không ảnh hưởng
  chức năng vì khối đúng đã tồn tại, em sẽ dọn."

---

## 1.4. Middleware

### `CheckadminRole.php`

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
```
- `handle()` là phương thức bắt buộc của middleware Laravel.
- `$next` là closure gọi tiếp middleware kế / controller.

```php
    if (!$user) {
        return response()->json([...], 401);
    }
```
- **401 Unauthorized** = chưa xác thực (chưa biết anh là ai).

```php
    if ((int) $user->phanquyen !== 1) {
        return response()->json([...], 403);
    }
    return $next($request);
}
```
- **403 Forbidden** = đã biết anh là ai nhưng không đủ quyền. Phân biệt 401 vs 403 là câu hỏi kinh điển.
- `(int)` ép kiểu phòng trường hợp cột trả về chuỗi `"1"`.
- `!==` so sánh nghiêm ngặt (cả giá trị lẫn kiểu).

Quy ước phân quyền trong hệ thống:
| `phanquyen` | Vai trò |
|---|---|
| 1 | Admin |
| 2 | Nhân viên |
| 3 | Khách hàng |
| -1 | Nhân viên đã bị gỡ (xem `PersonnelController::destroy`) |

> ⚠️ Quy ước này **không tuần tự** (không có giá trị 0) và dùng số âm làm cờ trạng thái.
> Nếu thầy hỏi, thừa nhận nên dùng `enum` hoặc bảng `roles` riêng cho rõ ràng.

### `CheckstaffRole.php`
Giống hệt trên, chỉ đổi `!== 1` thành `!== 2`.

> **Điểm yếu cần biết**: Admin (`phanquyen=1`) **không** vào được route `/staff/*` vì middleware
> chỉ chấp nhận đúng 2. Nếu thầy hỏi "admin có xem được đơn của nhân viên không?" → trả lời:
> hiện tại không, admin có bộ route riêng `/admin/orders`. Thiết kế này tách biệt rõ nhưng
> nếu muốn admin bao trùm thì đổi điều kiện thành `!in_array($user->phanquyen, [1,2])`.

### `SecurityHeaders.php`

```php
if (app()->environment('production') && !$request->secure()) {
    return redirect()->secure($request->getRequestUri(), 301);
}
```
- Chỉ ở môi trường production: nếu request đi bằng HTTP (không phải HTTPS) thì
  **redirect 301** (permanent) sang HTTPS. Ở local vẫn cho HTTP để dev.

```php
$response = $next($request);
```
- Đây là middleware kiểu **after** — cho request chạy xong rồi mới xử lý response.

| Header | Chống lại |
|---|---|
| `X-Frame-Options: DENY` | Clickjacking (nhúng site vào iframe lừa click) |
| `X-Content-Type-Options: nosniff` | Trình duyệt tự đoán MIME type → chạy nhầm file upload thành script |
| `X-XSS-Protection: 1; mode=block` | XSS phản xạ (đã lỗi thời, trình duyệt mới bỏ, nhưng vô hại) |
| `Referrer-Policy: strict-origin-when-cross-origin` | Rò rỉ URL đầy đủ (có thể chứa token) sang site khác |
| `Strict-Transport-Security` | SSL stripping — ép trình duyệt luôn dùng HTTPS trong 1 năm |
| `Content-Security-Policy` | XSS — whitelist nguồn được phép tải script/style/font/ảnh |

```php
$response->headers->remove('X-Powered-By');
$response->headers->remove('Server');
```
- Giấu thông tin phiên bản PHP/web server → giảm bề mặt tấn công (security through obscurity,
  chỉ là lớp bổ trợ chứ không phải biện pháp chính).

> **CSP có `'unsafe-inline'`** cho script — về lý thuyết làm giảm hiệu quả chống XSS.
> Nếu thầy hỏi: thừa nhận đây là đánh đổi vì frontend còn dùng inline script;
> hướng khắc phục là dùng nonce/hash.

---

## 1.5. `AppServiceProvider.php`

### Phần A — Ghi nhật ký tự động qua Model Events

```php
$models = [san_pham::class, danh_muc::class, Nguoi_dung::class,
           khuyen_mai::class, chi_nhanh::class];

foreach ($models as $modelClass) {
    $modelClass::created(function ($item) use ($modelClass) {
        self::createNotification('Thêm mới dữ liệu', 'vừa thêm mới một bản ghi vào', $modelClass);
    });
    $modelClass::updated(...);
    $modelClass::deleted(...);
}
```
- Đăng ký **Eloquent model event listener**. Mỗi khi 1 trong 5 model được tạo/sửa/xóa,
  hệ thống tự sinh 1 bản ghi trong bảng `thong_bao`.
- Ưu điểm: không phải rải code ghi log ở từng controller (DRY).
- Nhược điểm (thầy có thể hỏi): các thao tác `DB::table()->update()` hoặc
  `Model::query()->update()` hàng loạt **không** kích hoạt event → sót log.

```php
private static function createNotification($title, $actionText, $modelClass)
{
    $user = auth('sanctum')->user();
    $name = $user ? $user->Ten : 'Hệ thống';
```
- ⚠️ **Lỗi tiềm ẩn**: cột trong DB là `ten` (chữ thường, xem model `Nguoi_dung`), ở đây viết `$user->Ten`.
  MySQL không phân biệt hoa thường ở tên cột nhưng **PHP/Eloquent thì có** — `$user->Ten` sẽ trả `null`,
  nên tên người luôn hiển thị rỗng. Nếu thầy soi, thừa nhận là bug nhỏ.

```php
    \App\Models\ThongBao::create([
        'loai_thong_bao' => strtoupper($modelName),
        'tieu_de' => $title,
        'noi_dung' => "{$name} {$actionText} quản lý {$modelName}.",
        'da_doc' => false
    ]);
```
- ⚠️ `'da_doc'` **không** có trong `$fillable` của model `ThongBao` (model chỉ có `nguoi_doc`).
  Do đó trường này bị Laravel bỏ qua (mass-assignment protection). Không gây lỗi, chỉ là code thừa.

### Phần B — Rate Limiter

```php
RateLimiter::for('login', function (Request $request) {
    $identifier = $request->input('email') ?? $request->input('identifier') ?? 'guest';
    return Limit::perMinute(5)
        ->by($request->ip() . '|' . $identifier)
        ->response(fn() => response()->json([...], 429));
});
```
- Định nghĩa limiter tên `login`, dùng bởi `->middleware('throttle:login')`.
- `perMinute(5)` — tối đa 5 lần/phút.
- `by(ip . '|' . email)` — khóa đếm là **cặp IP + email**. Nghĩa là:
  - Cùng IP tấn công 10 email khác nhau → mỗi email vẫn được 5 lần.
  - Điểm mạnh: không khóa oan người dùng chung IP (mạng công ty/NAT).
  - Điểm yếu: kẻ tấn công có nhiều IP (botnet) vẫn dò được. Muốn chặt hơn thì thêm limiter thứ hai
    chỉ theo IP.
- `429 Too Many Requests` là mã HTTP chuẩn cho rate limit.

```php
RateLimiter::for('register', fn() => Limit::perMinutes(10, 3)->by($request->ip())...);
RateLimiter::for('forgot-password', fn() => Limit::perMinutes(5, 3)->by($request->ip())...);
```
- `perMinutes(10, 3)` = 3 lần trong 10 phút. Chống spam gửi OTP (mỗi lần gửi tốn tiền/quota email).
- ⚠️ Limiter `register` **không được dùng ở route nào** (route `/register/send-otp` không có
  `throttle:register`). Nếu thầy hỏi → thừa nhận là thiếu sót, route đăng ký hiện chưa bị giới hạn.

---

## 1.6. `app/Mail/OtpMail.php`

```php
class OtpMail extends Mailable
{
    use Queueable, SerializesModels;
```
- `Queueable` cho phép đẩy email vào hàng đợi (`Mail::queue()`), tránh chặn request.
  Dự án hiện gọi `Mail::to()->send()` (đồng bộ) nên chưa tận dụng.
- `SerializesModels` giúp serialize model an toàn khi đưa vào queue (chỉ lưu ID, khi chạy thì query lại).

```php
    public string $otp;
    public string $userName;
    public string $type;
```
- Thuộc tính `public` của Mailable được **tự động truyền vào view Blade**.
  Trong `resources/views/emails/otp.blade.php` dùng trực tiếp `{{ $otp }}`.

```php
    public function __construct(string $otp, string $userName = 'Quý khách', string $type = 'reset')
```
- Tham số có giá trị mặc định → gọi `new OtpMail('123456')` vẫn chạy.

```php
    public function envelope(): Envelope
    {
        $subject = $this->type === 'register'
            ? '🎉 [TOIYEUPC] Mã xác nhận đăng ký tài khoản'
            : '🔐 [TOIYEUPC] Mã xác nhận đặt lại mật khẩu';
        return new Envelope(subject: $subject);
    }
```
- `envelope()` định nghĩa phần "bì thư": tiêu đề, người gửi, người nhận.
- Toán tử 3 ngôi đổi tiêu đề theo loại OTP — tái sử dụng 1 lớp cho 2 luồng.
- `subject:` là **named argument** (PHP 8+).

```php
    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
    public function attachments(): array { return []; }
```
- `content()` chỉ định view Blade dựng nội dung.
- `attachments()` trả mảng rỗng — email không đính kèm file.

---

## 1.7. `app/Http/Controllers/Controller.php`

```php
abstract class Controller
{
}
```
- Laravel 11 làm base controller **rỗng**. Trước đây nó `use AuthorizesRequests, ValidatesRequests`.
  Giờ nếu cần thì tự thêm trait. Các controller trong dự án extend lớp này (hoặc không extend gì).

---

## 1.8. Câu trả lời mẫu cho "Kiến trúc hệ thống của em là gì?"

> Hệ thống theo kiến trúc **client–server tách rời**. Backend là Laravel 11 đóng vai trò
> RESTful API server, trả JSON, không render giao diện. Frontend là ứng dụng SPA riêng.
>
> Trong backend em áp dụng mô hình **MVC biến thể cho API**:
> - **Model** (Eloquent ORM) ánh xạ bảng CSDL và khai báo quan hệ.
> - **Controller** chứa logic nghiệp vụ, trả JSON.
> - Thay cho **View** là tầng **FormRequest** đảm nhận validate đầu vào, giúp controller gọn.
> - **Middleware** xử lý xuyên suốt: xác thực (Sanctum), phân quyền (CheckadminRole/CheckstaffRole),
>   bảo mật HTTP header.
>
> Ngoài ra có **Service bên ngoài**: một Python FastAPI + Qdrant làm vector search cho chức năng
> tìm kiếm/gợi ý sản phẩm bằng ngôn ngữ tự nhiên, và cổng VNPay cho thanh toán online.
