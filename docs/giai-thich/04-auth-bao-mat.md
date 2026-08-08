# 04 — XÁC THỰC & BẢO MẬT

Hai controller: `AuthController` (204 dòng) và `PasswordResetController` (99 dòng).
Đây là phần thầy chắc chắn sẽ hỏi vì liên quan trực tiếp đến bảo mật.

---

## 4.0. Sanctum hoạt động thế nào? (phải thuộc)

```
1. Client POST /api/login  { email, password }
2. Server kiểm tra mật khẩu → tạo bản ghi trong bảng `personal_access_tokens`
   ├─ cột `token`     : SHA-256 hash của phần bí mật
   └─ trả về client   : "<id>|<chuỗi_bí_mật_dạng_plain>"
3. Client lưu token, mọi request sau gửi header:
   Authorization: Bearer <id>|<chuỗi_bí_mật>
4. Guard `sanctum` tách id → tìm bản ghi → hash chuỗi bí mật → so sánh
   → nếu khớp thì gán $request->user()
```

**Vì sao dùng token thay vì session?**
- API stateless → server không lưu phiên, dễ scale ngang (nhiều server).
- Frontend là SPA/mobile riêng domain → cookie session gặp vấn đề CORS/SameSite.
- Không có cookie tự động gửi kèm → **miễn nhiễm CSRF** theo thiết kế.

**Vì sao Sanctum mà không phải JWT?**
- Token Sanctum lưu trong DB → **thu hồi được ngay lập tức** (`$user->tokens()->delete()`).
  JWT là stateless, đã cấp thì không hủy được cho đến khi hết hạn (trừ khi làm blacklist).
- Dự án cần thu hồi khi đổi mật khẩu → Sanctum phù hợp hơn.
- Đánh đổi: mỗi request phải query DB 1 lần. Chấp nhận được ở quy mô này.

---

## 4.1. `AuthController::login()`

```php
public function login(LoginAuthRequest $request)
{
    $identifier = trim($request->email);
    $user = Nguoi_dung::where('email', $identifier)
                      ->orWhere('sdt', $identifier)
                      ->first();
```
- `LoginAuthRequest` đã đảm bảo `email`/`password` không rỗng (xem file 03).
- `trim()` — cắt khoảng trắng thừa. Người dùng copy-paste email hay dính space.
- `where(...)->orWhere(...)` sinh SQL: `WHERE email = ? OR sdt = ?`
  → **một ô nhập, hai cách đăng nhập**. Đây là lý do trường vẫn tên `email`
  nhưng không validate định dạng email.
- `first()` trả model đầu tiên hoặc `null`.

```php
    if (!$user || !Hash::check($request->password, $user->matkhau)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Email/Số điện thoại hoặc mật khẩu không chính xác!'
        ], 401);
    }
```
- `Hash::check($plain, $hash)` — băm lại mật khẩu người dùng nhập với cùng salt
  lưu trong `$hash` rồi so sánh. **Không bao giờ giải mã ngược** (bcrypt là hàm 1 chiều).
- Laravel mặc định dùng **bcrypt** với cost 12 → cố tình chậm (~100ms) để chống brute-force.

> **Điểm bảo mật đáng nêu**: thông báo lỗi **gộp chung** cho cả 2 trường hợp
> "không có tài khoản" và "sai mật khẩu". Nếu tách riêng, kẻ tấn công có thể
> **dò xem email nào đã đăng ký** (user enumeration). Đây là chủ đích, nên nói ra.
>
> ⚠️ Tuy nhiên vẫn còn rò rỉ qua **thời gian phản hồi**: nếu user không tồn tại,
> code `return` ngay (nhanh); nếu user tồn tại thì phải chạy `Hash::check` (~100ms).
> Đo thời gian là đoán được. Đây gọi là **timing attack**. Cách khắc phục:
> luôn chạy `Hash::check` với một hash giả khi user không tồn tại.
> Nếu thầy hỏi sâu, nêu điểm này sẽ được đánh giá cao.

```php
    $token = $user->createToken('ToiYeuPCToken')->plainTextToken;
```
- `createToken()` do trait `HasApiTokens` cung cấp. Tham số là **tên token**
  (để người dùng nhận biết thiết bị, giống "Đăng nhập trên Chrome — Windows").
- `->plainTextToken` là **lần duy nhất** đọc được chuỗi gốc. DB chỉ lưu hash.
- ⚠️ Token này **không có thời hạn** (`expires_at = null`). Nếu bị lộ thì dùng được
  mãi đến khi user logout. Cách siết: đặt `config/sanctum.php → 'expiration' => 60*24*7`.

```php
    return response()->json([
        'status' => 'success', 'message' => '...',
        'data'   => $user,
        'token'  => $token
    ], 200);
```
- ⚠️ `'data' => $user` trả **nguyên model**. An toàn vì `$hidden = ['matkhau','remember_token']`
  đã che 2 cột nhạy cảm (xem file 02). Nhưng vẫn lộ `mancc_id`, `email_verified_at`…
  Chuẩn hơn nên dùng **API Resource** để chọn lọc trường — như hàm `me()` bên dưới đã làm.
  Không nhất quán giữa 2 hàm.

---

## 4.2. `logout()`

```php
public function logout(Request $request)
{
    if ($request->user()) {
        $request->user()->currentAccessToken()->delete();
    }
    return response()->json(['status'=>'success','message'=>'Đăng xuất thành công!']);
}
```
- `currentAccessToken()` — token đang dùng cho **chính request này**.
  Xóa nó = đăng xuất **thiết bị hiện tại**, các thiết bị khác vẫn đăng nhập.
- Nếu muốn "đăng xuất mọi nơi" thì dùng `$request->user()->tokens()->delete()`.
- Kiểm tra `if ($request->user())` là thừa (route đã có `auth:sanctum`) nhưng vô hại.

---

## 4.3. Đăng nhập Google (OAuth 2.0) — luồng 3 chân

```php
public function redirectToProvider($provider)
{
    return Socialite::driver($provider)->stateless()->redirect();
}
```
- `Socialite` là package chính thức của Laravel cho OAuth.
- `$provider` = `'google'` (lấy từ URL `/auth/{provider}/redirect`).
- `stateless()` — **quan trọng**: bỏ kiểm tra `state` lưu trong session.
  Vì API không có session. Đánh đổi: mất lớp chống **CSRF trong luồng OAuth**.
  Nếu thầy hỏi thì thừa nhận, giải pháp là tự sinh & kiểm tra `state` qua Cache.
- `redirect()` trả HTTP 302 sang trang đăng nhập Google.

```php
public function handleProviderCallback($provider)
{
    try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
```
- Google gọi lại URL này kèm `?code=...`. Socialite tự đổi `code` lấy `access_token`
  rồi gọi API Google lấy hồ sơ người dùng. Toàn bộ gói trong 1 dòng.

```php
        $user = Nguoi_dung::updateOrCreate(
            ['email' => $socialUser->getEmail()],      // điều kiện tìm
            [                                          // dữ liệu tạo/cập nhật
                'ten'               => $socialUser->getName(),
                'matkhau'           => Hash::make(Str::random(24)),
                'mancc'             => 'google',
                'mancc_id'          => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
                'phanquyen'         => 3,
                'email_verified_at' => now(),
            ]
        );
```
- `updateOrCreate` — nếu email đã có thì cập nhật, chưa có thì tạo mới.
  Nhờ vậy người dùng đăng ký thường rồi đăng nhập Google vẫn vào đúng tài khoản.
- `Hash::make(Str::random(24))` — sinh mật khẩu ngẫu nhiên. Tài khoản Google
  không dùng mật khẩu, nhưng cột `matkhau` là `NOT NULL` nên phải điền gì đó.
  Chuỗi 24 ký tự ngẫu nhiên không ai đoán được → an toàn.
- `email_verified_at = now()` — Google đã xác thực email hộ, khỏi gửi OTP.

> ⚠️ **Lỗ hổng nghiêm trọng cần biết**: `updateOrCreate` sẽ **ghi đè `phanquyen` = 3**
> mỗi lần đăng nhập Google. Nếu một admin (`phanquyen=1`) dùng Google để đăng nhập,
> tài khoản của họ **bị hạ xuống thành khách hàng**. Đây là bug thật, khá nặng.
> Cách sửa: bỏ `phanquyen` và `ten` ra khỏi mảng cập nhật, chỉ set khi tạo mới
> (dùng `firstOrCreate` hoặc kiểm tra `$user->wasRecentlyCreated`).
> **Nếu thầy hỏi, chủ động nêu ra sẽ được đánh giá là hiểu code mình viết.**

### Vấn đề: làm sao trả token về SPA?

```php
        $token = $user->createToken('auth_token')->plainTextToken;
        $exchangeCode = Str::random(40);
        Cache::put('oauth_code_' . $exchangeCode, $token, now()->addSeconds(60));
        return redirect('http://localhost:5173/oauth/callback?code=' . $exchangeCode);
```
- **Bài toán**: callback của Google là một **redirect trình duyệt**, không phải AJAX.
  Không thể trả JSON. Mà nhét token thẳng vào URL thì token sẽ nằm trong
  lịch sử trình duyệt, log server, header `Referer` → **rò rỉ**.
- **Giải pháp**: kỹ thuật **mã trung gian dùng một lần** (giống Authorization Code Flow).
  1. Sinh `$exchangeCode` ngẫu nhiên 40 ký tự.
  2. Lưu cặp `code → token` vào Cache, **hết hạn sau 60 giây**.
  3. Redirect về frontend kèm `code` (vô hại nếu lộ vì rất ngắn hạn).
  4. Frontend gọi AJAX `POST /exchange-code` để đổi lấy token thật.
- Đây là **điểm sáng thiết kế** của dự án, nên chủ động trình bày khi phản biện.

```php
    } catch (\Exception $e) {
        return redirect('http://localhost:5173/oauth/callback?error=login_failed');
    }
```
- Bắt mọi lỗi (Google từ chối, mạng lỗi…) và redirect kèm cờ lỗi.
  Không lộ chi tiết exception ra ngoài — đúng nguyên tắc bảo mật.
- ⚠️ URL `http://localhost:5173` **hard-code**. Lên production phải sửa code.
  Nên đưa vào `.env` → `config('app.frontend_url')`.

### `exchangeCode()`

```php
    $code     = $request->input('code');
    $cacheKey = 'oauth_code_' . $code;
    $token    = Cache::get($cacheKey);
    if (!$token) {
        return response()->json([... 'Mã xác thực không hợp lệ hoặc đã hết hạn.'], 401);
    }
    Cache::forget($cacheKey);
    return response()->json(['status'=>'success','token'=>$token]);
```
- `Cache::forget()` **ngay sau khi đọc** → mã chỉ dùng được **đúng 1 lần**.
  Nếu kẻ tấn công đọc trộm được `code` từ URL nhưng frontend đã đổi trước → vô dụng.
- Kết hợp 2 lớp: hết hạn 60s **và** dùng một lần.

---

## 4.4. `me()` và `updateProfile()`

```php
public function me(Request $request)
{
    $user = $request->user();
    return response()->json(['status'=>'success','user'=>[
        'id' => $user->id_nguoidung, 'ten' => $user->ten, /* ... 9 trường */
    ]]);
}
```
- **Whitelist trường** trả về — chỉ những gì frontend cần. Đây mới là cách đúng
  (khác với `login()` trả cả model).
- Đổi tên `id_nguoidung` → `id` cho frontend gọn.

```php
public function updateProfile(Request $request)
{
    $user = $request->user();
    $request->validate([
        'ten' => 'sometimes|string|max:255',
        'sdt' => 'sometimes|string|max:20',
        ...
    ]);
```
- ⚠️ Validate **inline trong controller**, không dùng FormRequest như các hàm khác.
  Không nhất quán với kiến trúc đã chọn.
- `sometimes` — chỉ validate **nếu trường có mặt** trong request. Phù hợp cập nhật từng phần.
- ⚠️ `sdt` **không kiểm tra `unique`** → hai người có thể có cùng số điện thoại,
  và nếu ai đó đổi `sdt` trùng với email/sdt người khác thì hàm `login()`
  (dùng `orWhere`) sẽ trả về **sai tài khoản**. Đây là bug bảo mật đáng lưu ý.

```php
    if ($request->has('ten')) { $user->ten = $request->ten; }
    if ($request->has('sdt')) { $user->sdt = $request->sdt; }
    ...
    $user->save();
```
- Gán **thủ công từng trường** thay vì `$user->update($request->all())`.
- **Đây là biện pháp chống mass assignment tốt nhất**: dù client gửi thêm
  `phanquyen: 1` thì cũng bị bỏ qua, vì code chỉ đọc đúng 4 trường.
  Nhớ rằng `phanquyen` **có** trong `$fillable` (xem file 02), nên nếu ở đây dùng
  `update($request->all())` thì user tự phong admin được. Cách viết này chặn điều đó.
  → **Nêu ra khi thầy hỏi về mass assignment.**

---

## 4.5. Đăng ký 2 bước bằng OTP

### Bước 1 — `sendRegisterOTP()`

```php
public function sendRegisterOTP(RegisterRequest $request){
    $otp = rand(100000,999999);
    $identifier = $request->email ? $request->email : $request->sdt;
    Cache::put('register_otp_'.$identifier,
        ['otp'=>$otp, 'data'=>$request->all()],
        now()->addMinutes(5));
```
- **Thiết kế then chốt**: dữ liệu đăng ký **chưa ghi vào DB**, chỉ nằm trong Cache 5 phút.
  Nếu người dùng bỏ giữa chừng → không để lại tài khoản rác.
  → Đây là câu trả lời cho "làm sao tránh tài khoản ảo?"
- ⚠️ `rand()` là bộ sinh số **giả ngẫu nhiên không an toàn mật mã**.
  Đúng ra phải dùng `random_int()` (như `PasswordResetController` đã dùng).
  Với OTP có hiệu lực 5 phút thì rủi ro thấp, nhưng **không nhất quán trong cùng dự án**.
- ⚠️ OTP lưu **dạng plain text** trong Cache. Nếu Redis bị lộ thì đọc được.

```php
    if($request->email){
        try{
            Mail::to($request->email)->send(new OtpMail($otp,$request->ten,'register'));
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>'Không thể gửi mã OTP...'],500);
        }
    }
    return response()->json(['status'=>'success','message'=>'Đã gửi mã OTP thành công']);
```
- `send()` là **đồng bộ** — request bị chặn ~1-3 giây chờ SMTP.
  Cải tiến: `Mail::queue()` + chạy `php artisan queue:work`. Class `OtpMail`
  đã `use Queueable` sẵn, chỉ cần đổi lời gọi.
- ⚠️ Nếu đăng ký bằng **SĐT** thì `if($request->email)` false → **không gửi gì cả**
  nhưng vẫn báo "Đã gửi mã OTP thành công". Người dùng sẽ không bao giờ nhận được mã.
  → Chức năng đăng ký bằng SĐT **chưa hoàn thiện** (chưa tích hợp SMS gateway).
  Thừa nhận thẳng nếu thầy hỏi.
- ⚠️ Route này **không gắn** `throttle:register` (limiter đã định nghĩa trong
  `AppServiceProvider` nhưng bỏ quên) → có thể spam gửi mail. Xem file 01.

### Bước 2 — `verifyRegisterOTP()`

```php
    $identifier = $request->email ? $request->email : $request->sdt;
    $cachedData = Cache::get('register_otp_'.$identifier);
    if(!$cachedData || $cachedData['otp'] != $request->otp){
        return response()->json(['status'=>'error','message'=>'Mã OTP không chính xác hoặc đã hết hạn']);
    }
```
- Không cần kiểm tra hết hạn thủ công — `Cache::get()` trả `null` khi TTL hết.
- ⚠️ Response này **không có mã HTTP** → mặc định trả **200 OK** dù là lỗi.
  Frontend phải đọc `status` trong body mới biết. Không chuẩn REST, nên trả 400.
- ⚠️ So sánh `!=` (lỏng) giữa int và string. `"000123" != 123` → PHP ép kiểu,
  có thể sinh kết quả bất ngờ. Nên dùng `!==` và ép cả hai về string.
- ⚠️ **Không giới hạn số lần thử OTP**. Kẻ tấn công gọi API 1 triệu lần là dò ra
  (không gian chỉ 900.000 mã). Cần đếm số lần sai trong Cache và khóa sau 5 lần.
  **Đây là lỗ hổng đáng kể nhất của luồng này** — nên chuẩn bị câu trả lời.

```php
    $data = $cachedData['data'];
    $user = Nguoi_dung::create([
        'ten'       => $data['ho'] . ' ' . $data['ten'],
        'email'     => $data['email'] ?? null,
        'sdt'       => $data['sdt'] ?? null,
        'matkhau'   => Hash::make($data['matkhau']),
        'phanquyen' => 3,
    ]);
```
- Giờ mới thực sự ghi DB.
- Ghép `ho` + `ten` thành 1 cột `ten` — DB không tách họ/tên riêng.
- `Hash::make()` — bcrypt. **Không bao giờ lưu mật khẩu thô**, câu hỏi kinh điển.
- `?? null` — toán tử null coalescing, tránh lỗi khi khóa không tồn tại.
- **`phanquyen = 3`** → đây là giá trị thực tế của **khách hàng** trong hệ thống
  (admin = 1, nhân viên = 2). Nhớ kỹ con số này.

```php
    ThongBao::create([
        'loai_thong_bao' => 'USER',
        'tieu_de'        => 'Khách hàng mới',
        'noi_dung'       => 'Khách hàng ' . $user->ten . ' vừa đăng ký tài khoản.',
        'link'           => '/admin/nguoi-dung'
    ]);
```
- Bắn thông báo cho admin.
- ⚠️ **Trùng lặp**: `AppServiceProvider` đã đăng ký sự kiện `Nguoi_dung::created`
  tự sinh thông báo rồi (xem file 01). Nên mỗi lần đăng ký sẽ tạo **2 thông báo**.
  Nếu thầy soi, thừa nhận là dư thừa cần dọn.

```php
    $token = $user->createToken('auth_token')->plainTextToken;
    Cache::forget('register_otp_' . $identifier);
```
- Đăng ký xong **tự đăng nhập luôn** — UX tốt.
- Xóa cache để OTP không dùng lại được.
- ⚠️ Toàn bộ khối này **không bọc `DB::transaction()`**. Nếu `ThongBao::create()`
  lỗi thì user đã tạo nhưng response trả 500 → người dùng đăng ký lại sẽ báo
  "email đã tồn tại" mà không hiểu vì sao.

---

## 4.6. `PasswordResetController` — quên mật khẩu

Khác `AuthController` ở chỗ dùng **bảng DB** (`otp_tokens`) thay vì Cache.

### `sendOtp()`

```php
    $identifier = trim($request->identifier);
    $user = Nguoi_dung::where('email',$identifier)->orWhere('sdt',$identifier)->first();
    if (!$user) {
        return response()->json([... 'Không tìm thấy tài khoản...'], 404);
    }
```
- ⚠️ **Rò rỉ thông tin (user enumeration)**: trả 404 khi email chưa đăng ký
  → kẻ tấn công dò được danh sách email có trong hệ thống.
  Chuẩn bảo mật là **luôn trả 200** với thông báo chung
  "Nếu email tồn tại, chúng tôi đã gửi mã".
  Mâu thuẫn với `login()` (nơi đã làm đúng). **Nên nêu ra làm điểm cải tiến.**

```php
    OtpToken::where('identifier', $identifier)->delete();
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
```
- Xóa OTP cũ trước → **mỗi thời điểm chỉ 1 mã hợp lệ**. Chống việc kẻ tấn công
  yêu cầu 100 mã rồi thử song song.
- `random_int()` — **bộ sinh ngẫu nhiên an toàn mật mã (CSPRNG)**. Đúng chuẩn.
- `str_pad(..., 6, '0', STR_PAD_LEFT)` — nếu random ra `42` thì thành `000042`.
  Đảm bảo luôn đủ 6 ký tự, khớp luật `size:6` ở FormRequest.
  Đồng thời giữ nguyên không gian đầy đủ 000000–999999 (nếu dùng `rand(100000,999999)`
  như bên `AuthController` thì mất 100.000 khả năng).

```php
    OtpToken::create([
        'identifier' => $identifier,
        'token'      => $otp,
        'expires_at' => now()->addMinutes(10),
    ]);
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        Mail::to($identifier)->send(new OtpMail($otp, $user->ten));
    }
```
- `filter_var(..., FILTER_VALIDATE_EMAIL)` — hàm PHP kiểm tra chuỗi có phải email không.
  Dùng để phân biệt người dùng nhập email hay SĐT.
- `new OtpMail($otp, $user->ten)` — không truyền tham số thứ 3 nên `$type` mặc định
  `'reset'` → tiêu đề mail thành "Mã xác nhận đặt lại mật khẩu" (xem file 01).
- ⚠️ Vẫn lưu OTP **plain text** trong DB. Nếu DB bị dump thì đọc được mọi OTP đang sống.
  Chuẩn hơn: `Hash::make($otp)` rồi `Hash::check` khi xác thực.

### `verifyOtp()`

```php
    $otpRecord = OtpToken::where('identifier',$identifier)->latest()->first();
    if (!$otpRecord || $request->otp !== $otpRecord->token) {
        return response()->json([...'Mã OTP không chính xác!'], 400);
    }
    if ($otpRecord->isExpired()) {
        $otpRecord->delete();
        return response()->json([...'Mã OTP đã hết hạn!...'], 400);
    }
```
- `latest()` = `orderBy('created_at','desc')` → lấy mã mới nhất.
- `!==` so sánh nghiêm ngặt (tốt hơn `!=` ở `AuthController`).
- `isExpired()` là helper trong model `OtpToken` (xem file 02).
- Xóa bản ghi khi hết hạn → dọn rác.
- ⚠️ Tương tự, **không giới hạn số lần thử**. Route `/forgot-password` có
  `throttle:forgot-password` (3 lần/5 phút) nhưng đó là giới hạn **gửi mã**,
  còn `/verify-otp` không bị chặn.
- ⚠️ So sánh chuỗi bằng `!==` dễ bị **timing attack** về lý thuyết.
  Chuẩn dùng `hash_equals()`. Với OTP thì rủi ro rất nhỏ, nhưng biết để trả lời.

### `resetPassword()`

```php
    $otpRecord = OtpToken::where('identifier',$identifier)->latest()->first();
    if (!$otpRecord || $request->otp !== $otpRecord->token || $otpRecord->isExpired()) {
        return response()->json([...'Phiên xác thực đã hết hạn...'], 400);
    }
```
- **Xác thực lại OTP lần nữa** — vì HTTP stateless, bước `verifyOtp` không để lại
  trạng thái nào. Nếu không kiểm tra ở đây thì ai cũng gọi thẳng API này để
  đổi mật khẩu người khác. **Đây là điểm bảo mật quan trọng nhất của luồng, phải nhớ.**

```php
    $user->update(['matkhau' => Hash::make($request->password)]);
    OtpToken::where('identifier', $identifier)->delete();
    $user->tokens()->delete();
```
- Ba bước theo đúng thứ tự:
  1. Đổi mật khẩu (băm mới).
  2. **Xóa OTP** → mã dùng một lần, không reset lại được.
  3. **Xóa toàn bộ token Sanctum** → đăng xuất mọi thiết bị.
- Bước 3 là **best practice bảo mật**: giả sử tài khoản đang bị chiếm quyền,
  kẻ tấn công vẫn giữ token cũ. Đổi mật khẩu mà không hủy token thì vô nghĩa.
  → **Đây là điểm sáng, nhớ nêu chủ động.**
- Đây cũng chính là lý do chọn Sanctum thay JWT (JWT không hủy được).
- ⚠️ Ba thao tác không bọc transaction. Nếu lỗi giữa chừng có thể đổi mật khẩu
  nhưng OTP còn sống. Rủi ro thấp.

---

## 4.7. Bảng tổng hợp để trả lời thầy

| Câu hỏi | Trả lời ngắn |
|---|---|
| Mật khẩu lưu thế nào? | Băm **bcrypt** qua `Hash::make()`, một chiều, có salt tự động, cost 12 |
| Chống brute-force? | `throttle:login` 5 lần/phút theo cặp **IP + email** |
| Token có thu hồi được? | Có — Sanctum lưu DB, `logout` xóa token hiện tại, đổi mật khẩu xóa hết |
| Chống CSRF? | Không dùng cookie session → token Bearer miễn nhiễm CSRF theo thiết kế |
| Chống SQL Injection? | Eloquent dùng **prepared statement**, tham số được bind chứ không nối chuỗi |
| Chống mass assignment? | `$fillable` ở model + gán thủ công từng trường ở `updateProfile()` |
| OTP an toàn không? | 6 số, hết hạn 5-10 phút, dùng 1 lần, xóa mã cũ khi cấp mã mới |
| Sao dùng Sanctum không dùng JWT? | Cần thu hồi token ngay khi đổi mật khẩu |

## 4.8. Danh sách điểm yếu tự nhận (thuộc để chủ động nêu)

1. **`updateOrCreate` trong OAuth ghi đè `phanquyen` = 3** → hạ cấp admin. Bug nặng nhất.
2. **Không giới hạn số lần thử OTP** → dò được mã bằng brute-force.
3. **`sendOtp` trả 404** khi email không tồn tại → rò rỉ danh sách người dùng.
4. **Đăng ký bằng SĐT chưa gửi được mã** (chưa có SMS gateway) nhưng vẫn báo thành công.
5. Route `/register/send-otp` **thiếu** `throttle:register`.
6. OTP lưu **plain text** ở cả Cache lẫn DB.
7. `rand()` (không an toàn) ở `AuthController` vs `random_int()` ở `PasswordResetController` — không nhất quán.
8. Token Sanctum **không hết hạn**.
9. `updateProfile` không kiểm tra `unique` cho `sdt` → có thể gây đăng nhập nhầm tài khoản.
10. URL frontend `localhost:5173` **hard-code**, chưa đưa vào `.env`.
11. Thông báo đăng ký bị tạo **2 lần** (controller + model event).
12. `stateless()` trong Socialite làm mất lớp chống CSRF của OAuth.
