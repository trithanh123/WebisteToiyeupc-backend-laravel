# 03 — FORM REQUESTS (tầng validation)

## 3.0. FormRequest là gì và tại sao dùng?

Thay vì viết validate ngay trong controller:
```php
public function store(Request $request) {
    $request->validate([...]);   // controller phình to
}
```
Laravel cho phép tách ra một lớp riêng:
```php
public function store(StoreProductRequest $request) {
    // đến được đây nghĩa là dữ liệu đã hợp lệ
}
```
Laravel dùng **Dependency Injection** — thấy tham số kiểu `FormRequest` thì tự khởi tạo,
tự chạy `authorize()` rồi `rules()`. Nếu fail → ném exception, **controller không bao giờ chạy**.

**Lợi ích khi phản biện:**
1. Controller chỉ còn logic nghiệp vụ (Single Responsibility).
2. Quy tắc validate tái sử dụng được.
3. Thông báo lỗi tiếng Việt tập trung một chỗ.
4. **Bảo mật**: mọi dữ liệu vào hệ thống đều qua một cửa kiểm soát.

## 3.1. Bốn phương thức chuẩn trong dự án

```php
public function authorize(): bool          // Có được phép gửi request này không?
public function rules(): array             // Quy tắc validate
public function messages(): array          // Thông báo lỗi tiếng Việt
protected function failedValidation(...)   // Định dạng response khi lỗi
```

Ngoài ra 2 hook nâng cao được dùng:
```php
protected function prepareForValidation()  // Chỉnh sửa dữ liệu TRƯỚC khi validate
protected function withValidator($v)       // Thêm luật phức tạp SAU các luật cơ bản
```

### `authorize()`
```php
public function authorize(): bool { return true; }
```
Hầu hết các Request trả `true` vì việc phân quyền đã do **middleware** đảm nhận
(`CheckadminRole`, `CheckstaffRole`). Không kiểm tra 2 lần.

> ⚠️ **Ngoại lệ**: `OrderStaffRequest.php` trả `false` và `rules()` rỗng — đây là file
> sinh tự động bằng `php artisan make:request` nhưng **chưa dùng đến**. Nếu inject vào
> controller sẽ luôn trả 403. Nếu thầy hỏi: đây là file thừa, nên xóa.

### `failedValidation()`
```php
protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(response()->json([
        'status'  => 'error',
        'message' => 'Dữ liệu không hợp lệ.',
        'errors'  => $validator->errors(),
    ], 422));
}
```
- Mặc định Laravel sẽ **redirect back với session flash** (dành cho form web).
  API cần JSON nên phải override.
- `422 Unprocessable Entity` — mã HTTP chuẩn cho "cú pháp đúng nhưng dữ liệu sai nghiệp vụ".
  Phân biệt với `400 Bad Request` (JSON hỏng) và `403` (không có quyền).
- `HttpResponseException` được Laravel bắt và trả response ngay lập tức, dừng mọi xử lý.

---

## 3.2. Nhóm xác thực

### `LoginAuthRequest.php`
```php
'email'    => 'required|string',
'password' => 'required|string',
```
- ⚠️ Cố ý **không** đặt luật `email` cho trường `email`, vì dự án cho phép đăng nhập
  bằng **email hoặc số điện thoại** qua cùng một ô nhập. Nếu ép `|email` thì đăng nhập
  bằng SĐT sẽ bị chặn. Đây là chủ đích thiết kế, nên nêu rõ nếu thầy thắc mắc tên biến.

### `RegisterRequest.php`
```php
'email'   => 'required_without:sdt|nullable|email|unique:nguoi_dung,email',
'sdt'     => 'required_without:email|nullable|unique:nguoi_dung,sdt',
```
- `required_without:sdt` — bắt buộc **nếu** `sdt` trống. Hai luật chéo nhau đảm bảo
  phải có **ít nhất một** trong hai định danh.
- `unique:bảng,cột` — Laravel tự chạy `SELECT ... WHERE email = ? LIMIT 1`.

```php
'matkhau' => 'required|string|confirmed|min:8',
'matkhau_confirmation' => 'required|string|min:8',
```
- `confirmed` là luật đặc biệt: Laravel tự tìm trường tên `<tên>_confirmation`
  và so khớp. Vì thế frontend **bắt buộc** đặt tên ô nhập lại là `matkhau_confirmation`.
- Dòng thứ 2 hơi dư (đã có `confirmed`) nhưng giúp có thông báo lỗi riêng nếu bỏ trống.
- `min:8` — độ dài tối thiểu.

> **Nếu thầy hỏi "mật khẩu có yêu cầu độ mạnh không?"**:
> Hiện chỉ có `min:8`. Có thể tăng cường bằng `Password::min(8)->letters()->numbers()->symbols()`
> hoặc `->uncompromised()` (đối chiếu cơ sở dữ liệu mật khẩu bị lộ HaveIBeenPwned).
> Thừa nhận là điểm có thể cải tiến.

### `sendOtpRequest` / `VerifyOtpRequest` / `resetPasswordRequest`
```php
// sendOtp
'identifier' => 'required|string',

// verifyOtp
'identifier' => 'required|string',
'otp'        => 'required|string|size:6',

// resetPassword
'identifier'            => 'required|string',
'otp'                   => 'required|string|size:6',
'password'              => 'required|string|min:8',
'password_confirmation' => 'required|same:password',
```
- `size:6` — **đúng** 6 ký tự (khác `max:6`). Vì OTP luôn 6 chữ số.
- `otp` để kiểu `string` chứ không `integer` — quan trọng, vì OTP `012345`
  nếu ép integer sẽ mất số 0 đầu thành `12345`.
- `same:password` tương đương `confirmed` nhưng viết tường minh.

> **Điểm bảo mật đáng nói ở luồng reset**: `resetPassword` yêu cầu gửi lại `otp`.
> Nghĩa là bước `verifyOtp` chỉ để UX (hiện form đổi mật khẩu), còn **kiểm tra thật sự
> diễn ra ở bước cuối**. Nhờ vậy kẻ tấn công không thể bỏ qua bước xác thực bằng cách
> gọi thẳng API `resetPassword`.

---

## 3.3. Nhóm quản lý người dùng

### `StoreUserRequest.php` — có **closure rule** (luật tùy biến)
```php
'email' => [
    'required_without:sdt', 'nullable', 'string', 'email', 'max:255',
    'unique:nguoi_dung,email',
    function ($attribute, $value, $fail) {
        if (empty($value)) return;
        $val = strtolower($value);
        if (!str_ends_with($val, '@gmail.com') && !str_ends_with($val, '@toiyeupc.vn')) {
            $fail('Chỉ hỗ trợ đăng ký bằng tài khoản @gmail.com hoặc @toiyeupc.vn');
        }
    },
],
```
- Khi luật có sẵn không đủ, truyền vào một **closure** nhận 3 tham số:
  tên trường, giá trị, và hàm `$fail` để báo lỗi.
- `strtolower()` để so sánh không phân biệt hoa thường (`@GMAIL.COM` vẫn hợp lệ).
- `str_ends_with()` — hàm PHP 8. Nghiệp vụ: chỉ nhận Gmail (khách) hoặc email nội bộ (nhân viên).

```php
'sdt' => ['required_without:email', 'nullable', 'string',
          'regex:/^[0-9]{9}$/', 'unique:nguoi_dung,sdt'],
```
- ⚠️ **Mâu thuẫn**: regex yêu cầu **đúng 9 chữ số**, nhưng thông báo lỗi lại ghi
  "phải bắt đầu bằng 0 và có 9 số" (tức 10 ký tự). SĐT Việt Nam là 10 số.
  Regex đúng phải là `/^0[0-9]{9}$/`. Đây là **bug thật**.
  Ở `StoreAddressRequest` thì viết đúng: `/^(0)[0-9]{9}$/`.

```php
'phanquyen' => 'nullable|integer|in:1,2,3',
```
- Đúng với quy ước hệ thống: 1 = admin, 2 = nhân viên, 3 = khách hàng.
  Không có giá trị 0 — hệ thống không dùng số này.
  Giá trị `-1` (nhân viên đã gỡ) cũng không cho phép gán trực tiếp — hợp lý,
  vì đó là trạng thái do `PersonnelController::destroy()` tự đặt.

```php
public function messages(){
    return [
        'sdt.regex' => 'Số điện thoại chỉ được nhập số...',
        // ...
        'sdt.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 9 số.',  // ghi đè dòng trên
    ];
}
```
- ⚠️ **Key trùng trong mảng PHP** — giá trị sau ghi đè giá trị trước. Dòng đầu là code chết.

### `UpdateUserRequest.php` — luật `unique` khi cập nhật
```php
$id = $this->route('id');
'sdt' => ['nullable','string','regex:/^[0-9]{9,11}$/',
          'unique:nguoi_dung,sdt,' . $id . ',id_nguoidung'],
```
- Cú pháp `unique:bảng,cột,giá_trị_bỏ_qua,tên_cột_khóa_chính`.
- **Vì sao cần?** Khi sửa hồ sơ mà không đổi SĐT, nếu dùng `unique` thường thì
  hệ thống sẽ báo "SĐT đã tồn tại" — vì chính bản ghi đang sửa đang giữ nó.
  Tham số thứ 3 nói: "bỏ qua bản ghi có `id_nguoidung = $id`".
- `$this->route('id')` lấy tham số `{id}` từ URL.
- ⚠️ Regex ở đây là `{9,11}` — khác `{9}` của `StoreUserRequest`. Không nhất quán.

### `PersonnelstoreRequest.php` — có `withValidator()`
```php
'chucvu'           => 'required|string|max:50',
'machinhanh'       => 'required|exists:chi_nhanh,id_chinhanh',
'existing_user_id' => 'nullable|exists:nguoi_dung,id_nguoidung',
'ten'     => 'required_without:existing_user_id|string|max:255',
'email'   => 'required_without:existing_user_id|email|max:255|unique:nguoi_dung,email',
'matkhau' => 'required_without:existing_user_id|string|min:6',
'sdt'     => ['required_without:existing_user_id', 'regex:/^0[0-9]{9}$/'],
```
- Thiết kế thông minh: API hỗ trợ **2 kịch bản trong 1 endpoint**.
  1. Nâng cấp user có sẵn thành nhân viên → chỉ gửi `existing_user_id`.
  2. Tạo mới hoàn toàn → gửi `ten/email/matkhau/sdt`.
- `required_without:existing_user_id` là bản lề chuyển đổi giữa 2 kịch bản.
- `exists:bảng,cột` — đảm bảo khóa ngoại tồn tại, chống lỗi ràng buộc CSDL.

```php
protected function withValidator($validator)
{
    $validator->after(function ($validator) {
        if ($this->filled('existing_user_id')) {
            $checkExists = nhan_vien::where('id_nguoidung', $this->existing_user_id)->first();
            if ($checkExists && $checkExists->nguoi_dung->phanquyen != -1) {
                $validator->errors()->add('existing_user_id', 'Người dùng này hiện đã là nhân viên rồi!');
            }
        }
    });
}
```
- `$validator->after()` chạy **sau** khi các luật cơ bản pass → tránh query DB vô ích.
- Nghiệp vụ: một người không thể là nhân viên 2 lần.
- ⚠️ **Hai bug**:
  1. `$checkExists->nguoi_dung` — model `nhan_vien` khai báo quan hệ tên `nguoiDung`
     (camelCase), không có `nguoi_dung`. Truy cập sẽ ném lỗi "Property does not exist"
     hoặc trả null → `->phanquyen` trên null gây **fatal error**.
  2. So sánh `!= -1`: giá trị `-1` là cờ "nhân viên đã bị gỡ"
     (`PersonnelController::destroy()` đặt `phanquyen = -1`). Ý đồ là: nếu bản ghi
     nhân viên còn tồn tại **và** người đó chưa bị gỡ thì báo lỗi. Logic đúng ý,
     nhưng phụ thuộc vào một "magic number" khó đọc — nên dùng hằng số hoặc enum.

### `UpdateStoreRequest.php` (sửa nhân viên)
```php
$nhanVienId = $this->route('id');
$nhanVien   = nhan_vien::find($nhanVienId);
$userId     = $nhanVien ? $nhanVien->id_nguoidung : null;

'email' => 'nullable|email|max:255|unique:nguoi_dung,email,' . $userId . ',id_nguoidung',
```
- Route truyền `id_nhanvien`, nhưng luật `unique` cần `id_nguoidung`.
  Nên phải **truy vấn DB ngay trong `rules()`** để đổi khóa.
- Toán tử 3 ngôi chống lỗi khi không tìm thấy nhân viên.

---

## 3.4. Nhóm danh mục & sản phẩm

### `StoreCategoryRequest.php` — có `prepareForValidation()`
```php
protected function prepareForValidation()
{
    if ($this->ten_danhmuc) {
        $slug = $this->slug ? Str::slug($this->slug) : Str::slug($this->ten_danhmuc);
        $originalSlug = $slug;
        $count = 1;
        while (danh_muc::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        $this->merge(['slug' => $slug]);
    }
}
```
- Hook chạy **trước** `rules()`. Dùng để chuẩn hóa/bổ sung dữ liệu.
- `Str::slug('Card Đồ Họa')` → `card-do-hoa` (bỏ dấu, thay khoảng trắng bằng gạch ngang).
- **Vòng while**: nếu slug đã tồn tại thì tự thêm hậu tố tăng dần:
  `card-do-hoa` → `card-do-hoa-1` → `card-do-hoa-2`.
- `$this->merge()` ghi giá trị mới vào request để luật `unique` bên dưới kiểm tra.

> ⚠️ **Race condition** (điểm thầy có thể hỏi): giữa lúc vòng `while` tìm được slug trống
> và lúc `INSERT` thực sự, một request khác có thể chiếm mất slug đó. Xác suất thấp
> nhưng để chắc chắn cần **unique index ở tầng CSDL** — mà cột `slug` đã có `unique`
> trong migration, nên tối đa chỉ gây lỗi 500 chứ không sinh dữ liệu trùng.

### `UpdateCategoryRequest.php`
```php
protected function prepareForValidation()
{
    if ($this->has('slug') && $this->slug !== null) {
        $this->merge(['slug' => Str::slug($this->slug)]);
    }
}
'slug' => 'nullable|string|max:255|unique:danh_muc,slug,' . $id . ',id_danhmuc',
```
- Khi sửa thì **không** tự sinh slug (tránh đổi URL đang có traffic/SEO), chỉ chuẩn hóa nếu người dùng nhập.
- ⚠️ **Thiếu kiểm tra vòng lặp cây**: nếu đặt `danhmuc_cha` của A là chính A (hoặc con của A),
  cây danh mục sẽ tạo chu trình → hàm đệ quy `conVaChau()` chạy vô hạn.
  Nên bổ sung `withValidator` kiểm tra. Đây là câu hỏi phản biện khá hiểm, nên chuẩn bị.

### `StorePRoductRequest.php`
```php
'ma_danhmuc'     => 'required|integer|exists:danh_muc,id_danhmuc',
'masp'           => 'required|string|max:255|unique:san_pham,masp',
'gia'            => 'required|integer|min:1',
'specifications' => 'nullable|array',
'weight'         => 'nullable|numeric|min:0'
```
- `gia` `min:1` khi thêm mới nhưng `min:0` khi sửa (`UpdateProductRequest`) — không nhất quán nhẹ.
- `specifications => array` — Laravel kiểm tra là mảng; model tự `json_encode` khi lưu nhờ `$casts`.
- ⚠️ `weight` không có trong `$fillable` của model `san_pham` → validate xong bị bỏ qua. Code thừa.
- Tên class viết hoa sai (`StorePRoductRequest`) — cần giữ nguyên vì đã import ở controller.

---

## 3.5. Nhóm chi nhánh & voucher

### `StoreBranchRequest.php`
```php
'Ma_chi_nhanh'  => 'required|string|max:100|unique:chi_nhanh,Ma_chi_nhanh',
'SDT_Chi_nhanh' => 'required|string|max:20',
'Maso_TP'       => 'nullable|integer',
```
- ⚠️ Tên trường ở đây (`Ma_chi_nhanh`, `SDT_Chi_nhanh`, `Maso_TP`) **khác** tên cột trong
  model `chi_nhanh` (`ma_chinhanh`, `sdt_chinhanh`, `maso_tp`).
  → Controller phải **map thủ công** giữa hai bộ tên. Xem file 06.
  Nếu thầy hỏi, thừa nhận đây là điểm gây rối, nên thống nhất tên.
- `unique:chi_nhanh,Ma_chi_nhanh` vẫn chạy được vì **MySQL không phân biệt hoa thường tên cột**.

### `updateStoreBranch.php`
```php
'Ma_chi_nhanh' => 'nullable|string|max:100|unique:chi_nhanh,Ma_chi_nhanh,' . $this->id . ',id_chinhanh',
```
- ⚠️ `$this->id` lấy từ **body request**, không phải `$this->route('id')` như các Request khác.
  Nếu frontend không gửi kèm `id` trong body thì tham số bỏ qua sẽ rỗng
  → sửa chi nhánh mà giữ nguyên mã sẽ bị báo trùng. **Bug tiềm ẩn**.

### `StoreVoucherRequest.php` — có cả `prepareForValidation` và `withValidator`
```php
protected function prepareForValidation()
{
    $this->merge([
        'don_toithieu' => $this->filled('don_toithieu') ? $this->don_toithieu : 0,
        'giam_toida'   => $this->filled('giam_toida')   ? $this->giam_toida   : null,
        'dasudung'     => 0,
    ]);
}
```
- Gán giá trị mặc định: không nhập đơn tối thiểu → 0 (áp dụng mọi đơn).
- `dasudung` **ép cứng bằng 0** — chống việc client tự gửi `dasudung: 999`
  để làm sai lệch thống kê. Đây là một biện pháp bảo mật tốt, đáng nêu.

```php
'loai_giamgia' => 'required|string|in:Phần trăm,Số tiền',
'ngayketthucchuongtrinh' => 'required|date|after:ngaybdchuongtrinh|after:today',
```
- `in:` — enum, chỉ nhận 2 giá trị.
- Hai luật `after` chồng nhau: ngày kết thúc phải sau ngày bắt đầu **và** sau hôm nay.
  Chặn việc tạo voucher đã hết hạn.

```php
public function withValidator($validator){
    $validator->after(function($validator){
        if($this->loai_giamgia === 'Phần trăm' && $this->gia_trigiam > 100){
            $validator->errors()->add("gia_trigiam","Giá trị giảm không được vượt quá 100%");
        }
    });
}
```
- **Luật liên trường (cross-field)**: giá trị hợp lệ của `gia_trigiam` phụ thuộc `loai_giamgia`.
  Nếu là phần trăm thì ≤ 100; nếu là số tiền thì không giới hạn trên.
  Không thể diễn đạt bằng luật tĩnh → phải dùng `withValidator`.

### `updateVoucherRequest.php`
Giống trên, khác ở chỗ mọi trường đều `nullable` (cập nhật từng phần — PATCH semantics)
và `prepareForValidation` chỉ chỉnh khi trường có mặt (`$this->has(...)`).

---

## 3.6. Nhóm địa chỉ

### `StoreAddressRequest.php`
```php
'sdt_nguoinhan' => 'required|string|regex:/^(0)[0-9]{9}$/',
'ma_thanhpho'   => 'required|integer',
'ma_quan'       => 'required|integer',
'ma_phuong'     => 'required|integer',
```
- Regex đúng chuẩn SĐT VN: bắt đầu bằng `0` + 9 chữ số = 10 số.
- 3 mã hành chính là số nguyên từ API địa giới (không `exists` vì không lưu bảng địa giới trong DB).

```php
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $user = $this->user();
        if ($user) {
            $count = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)->count();
            if ($count >= 5) {
                $validator->errors()->add('limit_address',
                    'Không thể thêm địa chỉ mới. Bạn đã đạt giới hạn tối đa (5 địa chỉ).');
            }
        }
    });
}
```
- **Luật nghiệp vụ có truy vấn DB**: giới hạn 5 địa chỉ/người.
- Mục đích: chống spam dữ liệu và giữ UI gọn.
- `$this->user()` lấy user đã xác thực từ Sanctum (route có `auth:sanctum`).
- ⚠️ Lớp này **không có** `failedValidation()` → khi lỗi sẽ trả về định dạng mặc định
  của Laravel, khác với các Request khác. Không nhất quán về response format.

### `UpdateAddressRequest.php`
Giống `Store` nhưng bỏ luật giới hạn 5 (vì sửa không làm tăng số lượng). Hợp lý.

---

## 3.7. Nhóm kho — phần phức tạp nhất

### `StoreWarehouseRequest.php` — validate mảng lồng nhau

Dữ liệu đầu vào có dạng:
```json
{
  "nha_cung_cap": "Công ty ABC",
  "ngay_nhap": "2026-08-01",
  "ma_chi_nhanh": 1,
  "san_phams": [
    { "ma_san_pham": 5, "soluongtonkho": 2, "soluongkhothap": 1,
      "serials": ["SN001", "SN002"] }
  ]
}
```

```php
'ngay_nhap'    => 'required|date|before_or_equal:today',
'ma_chi_nhanh' => 'required|integer|exists:chi_nhanh,id_chinhanh',
'san_phams'    => 'required|array|min:1',
```
- `before_or_equal:today` — không cho nhập kho ngày tương lai (chống gian lận sổ sách).
- `min:1` trên mảng = phải có ít nhất 1 phần tử.

```php
'san_phams.*.ma_san_pham'    => 'required|integer|exists:san_pham,id_sanpham',
'san_phams.*.serials'        => 'nullable|array',
'san_phams.*.serials.*'      => 'required_with:san_phams.*.serials|string|distinct|unique:sanpham_serials,serial_code',
'san_phams.*.soluongtonkho'  => 'required|integer|min:1',
'san_phams.*.soluongkhothap' => 'required|integer|min:0',
```
- Cú pháp `*` là **wildcard** — áp luật cho mọi phần tử của mảng.
  `san_phams.*.serials.*` = mọi serial của mọi sản phẩm.
- `distinct` — không trùng trong **cùng mảng** hiện tại.
- `unique:sanpham_serials,serial_code` — không trùng với **serial đã có trong DB**.
  → Hai luật kết hợp chống nhập trùng serial cả trong phiếu lẫn toàn hệ thống.
  Đây là bảo đảm tính duy nhất của serial — nền tảng của toàn bộ phân hệ bảo hành.

```php
protected function withValidator($validator)
{
    $validator->after(function ($validator) {
        $sanPhams = $this->input('san_phams', []);
        if (!is_array($sanPhams)) return;
        $allSerials = [];

        foreach ($sanPhams as $index => $item) {
            // (1) Số serial quét được phải khớp số lượng khai báo
            if (isset($item['serials']) && is_array($item['serials']) && count($item['serials']) > 0) {
                $soLuongKhaiBao = (int) ($item['soluongtonkho'] ?? 0);
                $soSerialThucTe = count($item['serials']);
                if ($soSerialThucTe !== $soLuongKhaiBao) {
                    $dieuChinhIndex = $index + 1;
                    $validator->errors()->add(
                        "san_phams.{$index}.serials",
                        "Sản phẩm dòng thứ {$dieuChinhIndex} khai báo nhập {$soLuongKhaiBao} cái, "
                        . "nhưng chỉ quét được {$soSerialThucTe} mã Serial!"
                    );
                }
            }
            if (!empty($item['serials'])) {
                $allSerials = array_merge($allSerials, $item['serials']);
            }
        }

        // (2) Serial không được trùng giữa các mặt hàng khác nhau
        if (count($allSerials) !== count(array_unique($allSerials))) {
            $validator->errors()->add("san_phams",
                "Phát hiện mã Serial bị trùng lặp giữa các mặt hàng khác nhau trong cùng phiếu nhập!");
        }
    });
}
```
- **(1) Ràng buộc toàn vẹn nghiệp vụ**: nếu khai nhập 5 cái thì phải quét đủ 5 serial.
  Không có luật Laravel sẵn nào làm được vì nó so sánh 2 trường trong cùng phần tử mảng.
  Đây là chốt chặn tránh lệch giữa `soluongtonkho` và số bản ghi serial thực tế —
  chính là rủi ro denormalization đã nêu ở file 02.
- `$index + 1` — chuyển index mảng (bắt đầu 0) sang số thứ tự người dùng hiểu (bắt đầu 1).
- **(2)** Luật `distinct` chỉ soi trong 1 mặt hàng. Đoạn này gom **toàn bộ** serial của cả
  phiếu rồi so `count()` với `count(array_unique())` — nếu khác nhau nghĩa là có trùng.
  Kỹ thuật đơn giản và hiệu quả.
- **Điểm yếu nhỏ**: nếu `serials` rỗng thì bỏ qua kiểm tra (1) → cho phép nhập hàng
  không serial (linh kiện nhỏ như cáp, ốc vít). Đây là chủ đích, không phải bug.

### `UpdateWarehouseRequest.php`
```php
'soluongtonkho'  => 'nullable|integer|min:0',
'soluongkhothap' => 'nullable|integer|min:0',
```
- Chỉ cho sửa 2 con số. Không cho sửa serial qua đây — serial phải đi qua luồng nhập/điều chuyển
  để đảm bảo truy vết.

---

## 3.8. Nhóm điều chuyển & đặt hàng

### `StoreAdminTransferRequest.php`
```php
"ma_kho_xuat"           => "required",
"ma_kho_nhap"           => "required",
"ly_do"                 => "required",
"chi_tiet"              => "required|array",
"chi_tiet.*.ma_sanpham" => "required",
"chi_tiet.*.so_luong"   => "required|integer|min:1",
```
- ⚠️ Yếu hơn các Request khác: thiếu `exists:chi_nhanh,id_chinhanh` cho 2 kho,
  thiếu `exists:san_pham,...`, thiếu luật `different:ma_kho_xuat` cho kho nhập
  (hiện có thể tạo phiếu chuyển từ kho A sang chính kho A).
- Việc kiểm tra được đẩy xuống controller (xem file 09). Nếu thầy hỏi, trả lời:
  "Em kiểm tra ở controller vì cần khóa bản ghi trong transaction, nhưng đúng ra
  các luật cơ bản nên đặt ở đây để trả lỗi sớm."

### `StoreStaffTransferRequest.php`
```php
'chi_tiet'   => 'required',      // thiếu |array
'chi_tiet.*' => 'required',
public function message(){ ... }  // ⚠️ sai tên: phải là messages()
```
- ⚠️ Hai lỗi: `chi_tiet` không có `|array`, và phương thức đặt tên `message()` (thiếu `s`)
  nên Laravel **không bao giờ gọi** → toàn bộ thông báo tiếng Việt bị vô hiệu,
  người dùng nhận thông báo tiếng Anh mặc định.
- Không có `failedValidation()` → response khác định dạng chuẩn.

### `PurchaseCheckoutRequest.php`
```php
'ma_chinhanh'        => 'required|integer',
'ma_khuyenmai'       => 'nullable|integer',
'ma_diachinguoidung' => 'required|integer',
'phuong_thuc_tt'     => 'required|string|in:Tiền mặt,VNPay',
'cart_items'         => 'required|array',
'tongtien'           => 'required|numeric'
```
- ⚠️ **`tongtien` do client gửi lên** — đây là lỗ hổng kinh điển nếu server tin tưởng nó.
  Khách có thể sửa request thành `tongtien: 1000` cho đơn 50 triệu.
  → **Bắt buộc phải kiểm tra**: controller `PurchaseController::checkout()` có tính lại
  tổng tiền từ giá trong DB không? Xem file 07. Đây gần như chắc chắn là câu hỏi thầy sẽ hỏi.
- ⚠️ `cart_items` chỉ validate là mảng, không validate cấu trúc bên trong
  (`cart_items.*.id_sanpham`, `cart_items.*.soluong`). Nên bổ sung.

---

## 3.9. Tổng kết nhóm FormRequest

**Điểm mạnh:**
1. Tách bạch validation khỏi controller đúng chuẩn Laravel.
2. Thông báo lỗi tiếng Việt đầy đủ, thân thiện người dùng cuối.
3. Định dạng response lỗi thống nhất (JSON 422) ở đa số file.
4. Dùng đúng các kỹ thuật nâng cao: `prepareForValidation`, `withValidator`,
   closure rule, wildcard mảng, `unique` có ngoại lệ.
5. `StoreWarehouseRequest` là ví dụ tốt về validate ràng buộc nghiệp vụ phức tạp.

**Điểm cần cải thiện (chuẩn bị sẵn để thừa nhận):**
| Vấn đề | File |
|---|---|
| Regex SĐT không nhất quán (`{9}` vs `{9,11}` vs `^0[0-9]{9}$`) | StoreUser, UpdateUser, StoreAddress |
| `message()` sai tên → thông báo tiếng Việt không hoạt động | StoreStaffTransferRequest |
| Key trùng trong mảng `messages()` | StoreUserRequest |
| Truy cập quan hệ sai tên (`nguoi_dung` thay vì `nguoiDung`) | PersonnelstoreRequest |
| Thiếu `failedValidation()` → response không đồng nhất | StoreAddress, UpdateAddress, UpdateWarehouse, StoreAdminTransfer, StoreStaffTransfer, PurchaseCheckout |
| Thiếu `exists` cho khóa ngoại | StoreAdminTransfer, StoreStaffTransfer |
| Tên trường khác tên cột DB | StoreBranchRequest |
| `$this->id` thay vì `$this->route('id')` | updateStoreBranch |
| File rỗng chưa dùng, `authorize()` trả false | OrderStaffRequest |
| `tongtien`, `cart_items` chưa validate chặt | PurchaseCheckoutRequest |
