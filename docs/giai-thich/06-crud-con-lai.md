# 06 — CRUD CÒN LẠI: User, Branch, Personnel, Voucher, Address, Wishlist

6 controller trong file này đều là nghiệp vụ quản trị/tiện ích, cấu trúc đơn giản hơn
`ProductController`. Nhưng chính vì đơn giản nên thầy dễ hỏi sâu vào **chi tiết bảo mật**
và **tính toàn vẹn dữ liệu**. File này đi từng dòng.

---

## 6.1. `UserController` — Quản lý người dùng (admin)

Route: `/api/admin/users/*`, bảo vệ bởi `auth:sanctum` + `CheckadminRole`.

### a) `index()` — Danh sách người dùng

```php
$users = Nguoi_dung::select(['id_nguoidung','ten','email','sdt','phanquyen','mancc','avatar','created_at'])
    ->orderBy('id_nguoidung', 'asc')
    ->get()
```
- **`select([...])` là chi tiết bảo mật quan trọng.** Chỉ lấy 8 cột cần thiết, **không lấy `matkhau`**.
  Model đã có `$hidden = ['matkhau']` rồi, nhưng đây là **phòng thủ theo chiều sâu (defense in depth)**:
  cột không được SELECT thì hash mật khẩu **không bao giờ rời khỏi MySQL**, giảm cả rủi ro lộ qua log query.
- Bonus: giảm băng thông và bộ nhớ — không kéo cột `embedding`/`remember_token` vô ích.

```php
    ->map(function ($user) {
        $user->role_label = match ((int) $user->phanquyen) {
            1 => 'Admin',
            2 => 'Nhân viên',
            3 => 'Khách hàng',
            4 => 'Khách VIP',
            default => 'Không xác định',
        };
        return $user;
    });
```
- `map()` chạy trên **Collection** (dữ liệu đã nằm trong RAM), không phải query DB → không gây N+1.
- `match` là cú pháp PHP 8 — giống `switch` nhưng **so sánh nghiêm ngặt (`===`)** và là một
  *expression* (trả về giá trị, gán trực tiếp được). Vì so sánh `===` nên bắt buộc phải ép `(int)`,
  nếu `phanquyen` trả về chuỗi `"1"` thì `match` sẽ rơi vào `default`.
- `$user->role_label = ...` — gán một thuộc tính **không tồn tại trong DB**. Eloquent cho phép,
  và khi `toJson()` nó vẫn xuất hiện trong response. Frontend khỏi phải tự map số → chữ.

> ⚠️ **Điểm thầy có thể bắt**: ở đây có giá trị `4 => 'Khách VIP'` nhưng **không nơi nào trong hệ thống
> gán `phanquyen = 4`**, và `StoreUserRequest` chỉ cho `in:1,2,3`. Đây là **code chết** — dấu vết của
> tính năng dự định làm rồi bỏ. Đồng thời **thiếu nhãn cho `-1`** (nhân viên đã nghỉ, xem 6.3) →
> nhân viên nghỉ việc sẽ hiện "Không xác định" trên giao diện admin.
> Nếu thầy hỏi: thừa nhận và nói cách sửa là dùng **PHP Enum** (`enum VaiTro: int`) để một chỗ
> định nghĩa duy nhất, tránh lệch giữa validate và hiển thị.

```php
return response()->json(['status'=>'success','total'=>$users->count(),'data'=>$users], 200);
```
- **Không phân trang.** Với vài trăm user thì ổn, nhưng khi có 100.000 user thì API này sẽ
  làm sập server (kéo toàn bộ bảng vào RAM). So sánh với `ProductController::index()` đã có
  `paginate()` → thiếu nhất quán. Đây là hạn chế nên tự nhận trước khi thầy hỏi.

### b) `store()` — Admin tạo tài khoản

```php
$user = Nguoi_dung::create([
    'ten'       => $request->ten,
    'email'     => $request->email,
    'sdt'       => $request->sdt,
    'matkhau'   => Hash::make($request->matkhau),
    'phanquyen' => $request->phanquyen ?? 3,
]);
```
- **Gán tay từng trường** thay vì `create($request->validated())` → chống mass-assignment
  triệt để nhất: kể cả `$fillable` có lỗ hổng thì cũng chỉ 5 trường này được ghi.
- `Hash::make()` = **bcrypt**, có salt ngẫu nhiên tự động, cost mặc định 12.
  Bcrypt cố tình **chậm** (~100ms) để chống brute-force offline khi DB bị lộ.
  → Nếu thầy hỏi "sao không dùng MD5/SHA256?": MD5/SHA nhanh (hàng tỉ hash/giây trên GPU),
  bcrypt/argon2 mới là thuật toán dành cho mật khẩu.
- `$request->phanquyen ?? 3` — toán tử **null coalescing**: nếu admin không chọn quyền thì
  mặc định là **3 = Khách hàng** (quyền thấp nhất). Đây là nguyên tắc
  **secure by default / least privilege** — mặc định phải là an toàn nhất.

```php
ThongBao::create([
    'loai_thong_bao' => 'USER',
    'tieu_de' => 'Tài khoản mới',
    'noi_dung' => 'Admin vừa tạo thành công tài khoản cho ' . $user->ten . '.',
    'link' => '/admin/nguoi-dung'
]);
```
- Ghi nhật ký nội bộ cho admin.
- ⚠️ **Trùng lặp thông báo**: `AppServiceProvider` đã đăng ký event `Nguoi_dung::created()`
  tự sinh thông báo rồi (xem file 01, mục 1.5). Vậy 1 lần tạo user sẽ sinh **2 bản ghi thông báo**.
  Nếu thầy phát hiện: thừa nhận trùng, nên bỏ một trong hai — giữ event (DRY) hoặc giữ thủ công
  (nội dung chi tiết hơn), không nên giữ cả hai.

```php
], 201);
```
- **201 Created** đúng chuẩn REST cho thao tác tạo mới (khác 200 OK).

Response chỉ trả 5 trường đã chọn lọc — lần nữa không lộ hash mật khẩu.

### c) `show()`

```php
$user = Nguoi_dung::select([...])->find($id);
if (!$user) { return response()->json([...], 404); }
```
- `find()` trả `null` nếu không có → tự kiểm tra và trả **404 Not Found**.
- So sánh: nếu dùng `findOrFail()` thì Laravel tự ném `ModelNotFoundException` → cũng ra 404
  nhưng thông điệp tiếng Anh mặc định. Ở đây tự bắt để trả message tiếng Việt.

### d) `update()`

```php
$user->update($request->validated());
```
- `$request->validated()` **chỉ trả về những trường có trong `rules()` và đã qua validate**.
  Nếu hacker gửi thêm `matkhau` mà `UpdateUserRequest` không khai báo → trường đó bị loại bỏ.
  Đây là điểm mạnh của FormRequest so với `$request->all()`.
- `update()` = `fill()` + `save()`, vẫn chịu kiểm soát của `$fillable`.

> ⚠️ **Lỗ hổng leo thang đặc quyền cần chủ động nêu**: `UpdateUserRequest` cho phép sửa `phanquyen`.
> Route có `CheckadminRole` nên chỉ admin gọi được — về lý thuyết ổn. **Nhưng không có ràng buộc
> admin tự hạ quyền chính mình** (`update` chính `id` của mình với `phanquyen=3`) → tự khóa mình
> ra khỏi trang quản trị. Đối chiếu: `destroy()` **có** chặn tự xóa. Vậy là **chặn không đồng bộ**.
> Cách sửa: thêm cùng một điều kiện `$user->id_nguoidung === $request->user()->id_nguoidung`
> vào nhánh đổi quyền.

### e) `destroy()`

```php
if ($user->id_nguoidung === $request->user()->id_nguoidung) {
    return response()->json([... 'Bạn không thể tự xóa tài khoản của chính mình!'], 403);
}
```
- **Business rule quan trọng**: chống admin tự xóa → tránh trường hợp hệ thống không còn admin nào.
- Dùng `===` (so sánh cả kiểu). Cả hai vế đều lấy từ Eloquent nên cùng kiểu int → an toàn.
  (Nếu một vế đến từ URL param dạng chuỗi thì `===` sẽ luôn `false` — bẫy kinh điển, ở đây tránh được
  vì so sánh giữa hai model, không phải với `$id` từ URL.)

```php
$user->tokens()->delete();
$user->delete();
```
- **`tokens()->delete()` trước, rất quan trọng.** Xóa toàn bộ token Sanctum của user đó.
  Nếu chỉ xóa dòng `nguoi_dung` mà để token lại thì bảng `personal_access_tokens` còn rác,
  và trong khoảnh khắc giữa hai lệnh, token vẫn hợp lệ.
- Đây là **hard delete** (xóa vĩnh viễn). ⚠️ Nếu user đó đã có đơn hàng thì `don_hang.ma_nguoidung`
  trở thành **khóa ngoại mồ côi** — hoặc DB ném lỗi ràng buộc FK, hoặc dữ liệu lịch sử hỏng.
  → Nếu thầy hỏi: nên dùng `SoftDeletes` như `chi_nhanh` để giữ lịch sử đơn hàng.
  Hoặc chặn xóa khi user còn đơn (trả 409 Conflict như `CategoryController::destroy`).

---

## 6.2. `BranchController` — Chi nhánh (mẫu SoftDeletes)

### a) `index()`

```php
$branches = chi_nhanh::withTrashed()->orderBy('id_chinhanh', 'desc')->get();
```
- **`withTrashed()`** — lấy **cả** chi nhánh đã bị xóa mềm. Mặc định, model có trait `SoftDeletes`
  sẽ tự thêm `WHERE deleted_at IS NULL` vào mọi query (qua **Global Scope**). `withTrashed()`
  gỡ scope đó ra.
- Vì sao trang admin cần thấy cả chi nhánh đã ẩn? Để có nút **Khôi phục**. Frontend dựa vào
  trường `deleted_at` (khác null = đã ẩn) để hiển thị trạng thái.
- `orderBy('id_chinhanh','desc')` — chi nhánh mới nhất lên đầu.

### b) `show()`

```php
$branch = chi_nhanh::find($id);
```
- ⚠️ **Không** có `withTrashed()` → không xem được chi tiết chi nhánh đã ẩn, trả 404.
  Không nhất quán với `index()`. Nhỏ, nhưng nếu thầy soi thì đây là điểm để thừa nhận.

### c) `store()` / `update()`

```php
$branch = chi_nhanh::create($request->validated());
$branch->update($request->validated());
```
- Ngắn gọn nhờ đã đẩy hết validate sang `StoreBranchRequest` / `updateStoreBranch`.
  Đây chính là lý do dự án dùng FormRequest — controller **mỏng (thin controller)**.
- ⚠️ Tên class `updateStoreBranch` viết **camelCase** thay vì `UpdateBranchRequest`
  (PascalCase, có hậu tố `Request`) — vi phạm PSR-1 và không nhất quán với các file khác.

### d) `destroy()` — Xóa mềm

```php
$branch->delete();
return response()->json(['message' => 'Đã ẩn chi nhánh thành công!'], 200);
```
- Nhờ trait `SoftDeletes`, `delete()` **không** chạy `DELETE FROM` mà chạy
  `UPDATE chi_nhanh SET deleted_at = NOW()`.
- Message dùng chữ **"ẩn"** chứ không phải "xóa" — trung thực với hành vi thật, tốt cho UX.

> **Vì sao chi nhánh phải xóa mềm?** Vì `don_hang.ma_chinhanh`, `ton_kho_cuc_bo.ma_chinhanh`,
> `nhanvien.machinhanh` đều tham chiếu tới nó. Xóa cứng → hỏng toàn bộ báo cáo doanh thu quá khứ.
> Xóa mềm cho phép "đóng cửa chi nhánh" mà đơn hàng cũ vẫn join ra được tên chi nhánh.

### e) `restore()`

```php
$branch = chi_nhanh::withTrashed()->find($id);
$branch->restore();
```
- **Bắt buộc `withTrashed()`** — nếu không thì `find()` sẽ không thấy bản ghi đã xóa mềm
  (global scope lọc mất) → luôn trả 404, không bao giờ khôi phục được.
- `restore()` set `deleted_at = NULL`.

> ⚠️ Route `restore` cần bảo vệ bằng `CheckadminRole` — nếu không, ai cũng có thể "hồi sinh"
> chi nhánh. Kiểm tra `routes/api.php` trước khi bảo vệ luận văn.

---

## 6.3. `PersonnelController` — Nhân sự (có transaction)

Đây là controller phức tạp nhất trong nhóm này vì thao tác **2 bảng cùng lúc**:
`nguoi_dung` (tài khoản) và `nhanvien` (hồ sơ nhân viên).

### a) `index()`

```php
$personnel = nhan_vien::with(['chiNhanh', 'nguoiDung'])
                        ->orderBy('id_nhanvien', 'desc')
                        ->get();
```
- **`with([...])` = eager loading**, chống **N+1 query**.
  - Không có `with`: 1 query lấy 20 nhân viên + 20 query lấy chi nhánh + 20 query lấy người dùng = **41 query**.
  - Có `with`: 1 + 1 + 1 = **3 query** (Laravel gom `WHERE id IN (...)`).
- Đây là câu hỏi thầy chắc chắn hỏi — nhớ con số 41 vs 3.
- ⚠️ Không dùng `select` giới hạn cột → quan hệ `nguoiDung` trả về **toàn bộ** model.
  May là `$hidden` của `Nguoi_dung` che `matkhau`, nên không lộ. Nhưng đối chiếu với
  `UserController::index()` (có `select`) thì thấy không nhất quán.

### b) `store()` — Thêm nhân viên (2 kịch bản)

```php
try {
    DB::beginTransaction();
```
- **Transaction thủ công** (khác `DB::transaction(function(){...})` dạng closure).
  Cần transaction vì phải ghi 2 bảng: nếu tạo `nguoi_dung` thành công mà tạo `nhanvien` thất bại
  → sẽ có một tài khoản `phanquyen=2` **không thuộc chi nhánh nào** → dữ liệu bẩn.
- 4 tính chất ACID: **A**tomicity (tất cả hoặc không gì cả) chính là thứ ta cần ở đây.

```php
$Usertontai = $request->filled('existing_user_id');
```
- `filled()` = có mặt **và** không rỗng (khác `has()` chỉ kiểm tra có mặt).
- Biến này quyết định 2 nhánh nghiệp vụ.

**Nhánh 1 — Nâng cấp tài khoản khách sẵn có thành nhân viên:**
```php
$user = Nguoi_dung::find($request->existing_user_id);
$user->phanquyen = 2;
$user->save();
```
- Kịch bản thực tế: một khách hàng cũ được tuyển vào làm → giữ nguyên tài khoản, chỉ đổi quyền.
- ⚠️ **Không kiểm tra `$user` có null hay không.** `PersonnelstoreRequest` có rule `exists:` nên
  thực tế không null. Nhưng viết `?->` hoặc kiểm tra tường minh sẽ chắc chắn hơn.
  (Nếu null thì `->phanquyen` ném lỗi → rơi vào `catch` → rollback → trả 500. Vẫn an toàn dữ liệu.)

**Nhánh 2 — Tạo tài khoản mới:**
```php
$user = Nguoi_dung::create([
    'ten' => ..., 'email' => ...,
    'matkhau' => Hash::make($request->matkhau),
    'sdt' => ..., 'phanquyen' => 2,
]);
ThongBao::create([... 'loai_thong_bao' => 'STAFF' ...]);
```
- `phanquyen = 2` **hard-code** — nhân viên. Admin nhập mật khẩu ban đầu cho nhân viên.
- ⚠️ Nhánh 1 **không** sinh `ThongBao`, nhánh 2 có → nhật ký thiếu sự kiện "nâng cấp thành nhân viên".

```php
if ($Usertontai) {
     nhan_vien::where('id_nguoidung', $user->id_nguoidung)->delete();
}
```
- **Dọn hồ sơ nhân viên cũ trước khi tạo mới** — chống trường hợp một tài khoản có 2 dòng
  trong bảng `nhanvien` (2 chi nhánh cùng lúc). Đây là cách xử lý idempotent: xóa rồi tạo lại.
- ⚠️ **Lưu ý kỹ thuật**: `Model::where(...)->delete()` là **mass delete** →
  **không kích hoạt model event** `deleting`/`deleted`. Ở đây không sao vì `nhan_vien` không có
  observer nào, nhưng đây là điểm thầy có thể hỏi ("`delete()` này khác `$model->delete()` ở đâu?").

```php
$nhanVien = nhan_vien::create([
    'id_nguoidung' => $user->id_nguoidung,
    'chucvu'       => $request->chucvu,
    'machinhanh'   => $request->machinhanh,
]);
DB::commit();
$nhanVien->load(['chiNhanh', 'nguoiDung']);
```
- `load()` = **lazy eager loading** — nạp quan hệ cho model **đã có sẵn** (khác `with()` dùng lúc query).
  Gọi **sau `commit`** nên là dữ liệu đã chốt.

```php
} catch (\Exception $e) {
    DB::rollBack();
    return response()->json([..., 'error_detail' => $e->getMessage()], 500);
}
```
- `rollBack()` hoàn tác mọi thay đổi trong transaction.
- ⚠️ **`'error_detail' => $e->getMessage()` là lỗ hổng rò rỉ thông tin (information disclosure).**
  Thông điệp lỗi MySQL có thể chứa **tên bảng, tên cột, câu SQL** → giúp kẻ tấn công lập bản đồ CSDL.
  Cách đúng: `Log::error($e)` ghi vào file log cho dev, còn client chỉ nhận message chung chung.
  Đây là điểm **nên chủ động thừa nhận**, thầy dạy bảo mật rất hay bắt lỗi này.

### c) `update()`

```php
if ($request->has('ten'))     $user->ten = $request->ten;
if ($request->has('email'))   $user->email = $request->email;
if ($request->has('sdt'))     $user->sdt = $request->sdt;
if ($request->filled('matkhau')) $user->matkhau = Hash::make($request->matkhau);
```
- **Partial update (PATCH semantics)**: chỉ cập nhật trường nào frontend gửi lên.
- Điểm tinh tế: `matkhau` dùng **`filled()`** chứ không phải `has()`.
  Vì form sửa nhân viên thường gửi `matkhau: ""` (ô để trống = không đổi mật khẩu).
  `has('matkhau')` sẽ là `true` → hash chuỗi rỗng → **mật khẩu nhân viên bị đổi thành rỗng**.
  `filled()` loại bỏ trường hợp đó. **Đây là chi tiết rất đáng khoe khi phản biện.**

```php
if ($request->has('chucvu'))     $nhanVien->chucvu = $request->chucvu;
if ($request->has('machinhanh')) $nhanVien->machinhanh = $request->machinhanh;
$nhanVien->save();
```
- Đổi `machinhanh` = **điều chuyển nhân viên sang chi nhánh khác**. Từ lúc này mọi API `/staff/*`
  của người đó sẽ lọc theo chi nhánh mới.

### d) `destroy()` — Cho nghỉ việc

```php
$user->phanquyen = -1;
$user->save();
return response()->json([..., 'Đã đánh dấu nghỉ việc thành công!'], 200);
```
- **Không xóa gì cả** — chỉ đổi quyền thành `-1`.
- Hệ quả: `CheckstaffRole` yêu cầu `=== 2` → người này **mất ngay quyền truy cập** `/staff/*`.
  `CheckadminRole` yêu cầu `=== 1` → cũng không vào được admin. Hiệu quả tương đương khóa tài khoản.
- Dòng `nhanvien` **vẫn còn** → lịch sử "ai đã lập phiếu điều chuyển này" vẫn tra được.

> **Câu hỏi chắc chắn có: "Vì sao dùng `-1` mà không xóa?"**
> Trả lời: vì nhân viên đã tham chiếu ở `phieu_dieu_chuyen.nguoi_tao`, `nguoi_duyet`,
> `bao_hanh.ma_nhanvien`. Xóa cứng làm hỏng truy vết. `-1` là **soft-disable ở tầng quyền**.
> Hạn chế tự nhận: `-1` là **magic number**, nên thay bằng cột `trang_thai` riêng hoặc PHP Enum,
> vì trộn "vai trò" và "trạng thái làm việc" vào cùng một cột là vi phạm nguyên tắc
> mỗi cột một ý nghĩa.

> ⚠️ **Thiếu sót nghiêm trọng hơn**: `destroy()` **không** gọi `$user->tokens()->delete()`.
> Nhân viên vừa bị cho nghỉ, nếu app của họ đang mở, token vẫn hợp lệ — họ vẫn gọi được các API
> chỉ yêu cầu `auth:sanctum` (ví dụ `/api/me`, đặt hàng). Đối chiếu `UserController::destroy()`
> **có** thu hồi token → không nhất quán. Nêu ra trước sẽ ghi điểm.

> ⚠️ Ngoài ra `destroy()` **không** có transaction và **không** kiểm tra admin có tự cho mình nghỉ việc không.

---

## 6.4. `VoucherController` — Khuyến mãi

### a) `index()` / `store()` / `update()` / `destroy()`
Mẫu CRUD chuẩn, logic đã nằm hết ở `StoreVoucherRequest` / `updateVoucherRequest` (xem file 03).

```php
$voucher->delete();
```
- **Hard delete.** ⚠️ Nếu voucher đã được dùng trong `don_hang.ma_khuyenmai` thì đơn hàng cũ
  mất tham chiếu → không giải thích được vì sao đơn đó được giảm giá.
  → Nên chặn xóa khi voucher đã dùng (`dasudung > 0`) và trả **409 Conflict**,
  hoặc dùng SoftDeletes. Đây là hạn chế thật.

### b) `activeVouchers()` — Voucher khách được dùng

```php
$now = now();
$vouchers = khuyen_mai::where('ngaybdchuongtrinh', '<=', $now)
    ->where('ngayketthucchuongtrinh', '>=', $now)
    ->whereRaw('soluongma > dasudung')
    ->orderBy('id_khuyenmai', 'desc')
    ->get();
```
Ba điều kiện lọc, giải thích từng cái:
1. `ngaybdchuongtrinh <= now` — chương trình **đã bắt đầu**.
2. `ngayketthucchuongtrinh >= now` — **chưa kết thúc**.
3. `whereRaw('soluongma > dasudung')` — **còn lượt dùng**.

- **Vì sao phải `whereRaw`?** Vì đây là so sánh **cột với cột**, không phải cột với giá trị.
  `where('soluongma','>','dasudung')` sẽ bị Eloquent hiểu `dasudung` là **chuỗi ký tự**
  và bind thành `'dasudung'` → so sánh số với chuỗi, luôn sai.
  Cách khác an toàn hơn: `whereColumn('soluongma', '>', 'dasudung')`.

> **Câu hỏi bảo mật kinh điển: "`whereRaw` có bị SQL Injection không?"**
> → Ở đây **không**, vì chuỗi truyền vào là **hằng số do lập trình viên viết cứng**, không có
> dữ liệu người dùng. `whereRaw` chỉ nguy hiểm khi nối chuỗi kiểu
> `whereRaw("gia > $request->min")`. Trong dự án, mọi giá trị từ người dùng đều đi qua
> **prepared statement** (dấu `?`). Nếu cần truyền tham số vào raw thì dùng
> `whereRaw('a > ?', [$value])`.
> Nhớ kỹ nguyên tắc: **giá trị thì bind được, tên cột/tên bảng thì không** — nên tên cột
> phải whitelist (đã áp dụng ở `ProductController::index`, xem file 05).

- ⚠️ Endpoint này trả về **toàn bộ trường** của voucher, kể cả `soluongma`/`dasudung` — lộ
  thông tin nội bộ (khách biết mã sắp hết để tranh nhau). Không nghiêm trọng, nhưng nêu được
  thì tốt.

---

## 6.5. `AddressController` — Sổ địa chỉ (bài toán "chỉ một mặc định")

Đây là controller thể hiện rõ nhất kỹ thuật **ownership check** — kiến thức bảo mật thầy hay hỏi.

### a) `index()`

```php
$user = $request->user();
$addresses = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
    ->orderBy('matudien_diachi', 'desc')
    ->get();
```
- `$request->user()` lấy user từ **token Sanctum**, không phải từ input → kẻ tấn công
  không thể giả mạo `id_nguoidung`.
- `orderBy('matudien_diachi','desc')` — `matudien_diachi` là boolean (1/0),
  sắp giảm dần → **địa chỉ mặc định luôn nằm đầu danh sách**. Mẹo nhỏ nhưng hữu ích cho UI.

### b) `store()` — Thêm địa chỉ

```php
$addressCount = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)->count();
$isDefault = $request->input('matudien_diachi', false);

if ($addressCount === 0) {
    $isDefault = true;
}
```
- **Quy tắc nghiệp vụ 1**: địa chỉ **đầu tiên** bắt buộc là mặc định, dù người dùng không chọn.
  Đảm bảo luôn tồn tại ít nhất một địa chỉ mặc định để trang thanh toán có sẵn dữ liệu.
- `input('key', $default)` — lấy giá trị, nếu không có thì dùng mặc định.

```php
if ($isDefault) {
    diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
        ->update(['matudien_diachi' => false]);
}
```
- **Quy tắc nghiệp vụ 2**: chỉ **một** địa chỉ mặc định. Trước khi đặt cái mới,
  gỡ cờ của tất cả cái cũ.
- ⚠️ Có `where('id_nguoidung', ...)` → **chỉ ảnh hưởng địa chỉ của chính user**.
  Nếu thiếu điều kiện này thì sẽ gỡ mặc định của **toàn bộ hệ thống** — lỗi thảm họa.
  Đây là điểm đáng nhấn mạnh: mọi mass-update phải có điều kiện phạm vi.

```php
$address = diachi_nguoidung::create([
    'id_nguoidung'    => $user->id_nguoidung,
    ...
]);
```
- `id_nguoidung` **lấy từ token**, không lấy từ request body → khách **không thể tạo địa chỉ
  cho tài khoản người khác**. Đây là chống **IDOR (Insecure Direct Object Reference)** ở khâu ghi.

> ⚠️ **Hạn chế: 2 lệnh ghi (`update` gỡ cờ + `create`) không nằm trong transaction.**
> Nếu `create` lỗi sau khi `update` đã chạy → user **mất địa chỉ mặc định** (tất cả đều `false`).
> Nên bọc `DB::transaction()`. Lỗi này lặp lại ở `update()` và `setDefault()`.
> Nêu ra được sẽ chứng minh mình hiểu ACID chứ không chỉ học vẹt.

### c) `update()`

```php
$address = diachi_nguoidung::where('id_diachinguoidung', $id)
            ->where('id_nguoidung', $user->id_nguoidung)
            ->first();
if (!$address) {
    return response()->json(['message' => 'Không tìm thấy địa chỉ'], 404);
}
```
- **Đây là đoạn code quan trọng nhất file này.** Không dùng `find($id)` mà **kết hợp 2 điều kiện**:
  đúng ID **và** đúng chủ sở hữu.
- Chống **IDOR**: user A gọi `PUT /api/addresses/999` (địa chỉ của user B) → query không ra kết quả → 404.
- Vì sao trả **404** chứ không phải **403**? Vì 403 sẽ ngầm xác nhận "địa chỉ 999 có tồn tại,
  chỉ là bạn không có quyền" → rò rỉ thông tin. 404 khiến kẻ tấn công không phân biệt được
  "không tồn tại" và "không phải của tôi". Đây là lựa chọn **đúng về mặt bảo mật**,
  rất đáng nói khi phản biện.

```php
if ($isDefault) {
    diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
        ->where('id_diachinguoidung', '!=', $id)
        ->update(['matudien_diachi' => false]);
}
```
- `!=  $id` — loại trừ chính bản ghi đang sửa khỏi việc gỡ cờ (nếu không sẽ gỡ rồi lại set lại,
  thừa một thao tác và có thể gây nhấp nháy trạng thái).

```php
$address->update([... 'matudien_diachi' => $isDefault]);
```
- ⚠️ **Cập nhật toàn phần (full update)**: nếu frontend không gửi `sdt_nguoinhan` thì trường đó
  bị ghi thành `null`. Khác với `PersonnelController::update()` dùng `if ($request->has(...))`.
  Không nhất quán giữa hai controller — nếu thầy hỏi, giải thích rằng form địa chỉ của frontend
  luôn gửi đủ trường nên thực tế không lỗi, nhưng API nên tự bảo vệ mình.
- ⚠️ **Lỗi logic nhỏ hơn nhưng thật**: nếu user bỏ tick "mặc định" trên chính địa chỉ mặc định
  duy nhất → `$isDefault = false` → user **không còn địa chỉ mặc định nào**.

### d) `destroy()`

```php
if ($address->matudien_diachi) {
    return response()->json(['message' => 'Không thể xóa địa chỉ mặc định'], 422);
}
$address->delete();
```
- **Quy tắc nghiệp vụ 3**: không xóa địa chỉ mặc định → luôn còn ít nhất 1 địa chỉ dùng được.
- Muốn xóa thì phải `setDefault` cho địa chỉ khác trước.
- **422 Unprocessable Entity** = yêu cầu hợp lệ về cú pháp nhưng vi phạm quy tắc nghiệp vụ.
  (Có thể tranh luận nên dùng **409 Conflict**. Cả hai đều chấp nhận được, miễn giải thích được.)
- ⚠️ Nếu đây là địa chỉ **duy nhất** thì user không xóa được → phải thêm địa chỉ mới rồi mới xóa.
  Chấp nhận được về nghiệp vụ.
- ⚠️ **Hard delete**: `don_hang.ma_diachinguoidung` trỏ tới đây → đơn hàng cũ mất địa chỉ giao.
  Đây mới là rủi ro thật sự. Cách chuẩn: **snapshot địa chỉ vào đơn hàng** lúc đặt
  (giống cách `chi_tiet_don_hang` snapshot `don_gia`, xem file 02 mục 2.5), hoặc SoftDeletes.

### e) `setDefault()`

```php
diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
    ->update(['matudien_diachi' => false]);
$address->update(['matudien_diachi' => true]);
```
- 2 bước: gỡ hết → set một cái. Đơn giản, đúng.
- ⚠️ Lại thiếu transaction. Nếu đứt giữa chừng → 0 địa chỉ mặc định.
- Ownership check đã làm ở trên (cùng mẫu `where` kép) → an toàn.

---

## 6.6. `WishlistController` — Yêu thích (Query Builder thuần)

### a) `index()`

```php
$wishlist = DB::table('sanpham_yeuthich')
    ->join('san_pham', 'sanpham_yeuthich.id_sanpham', '=', 'san_pham.id_sanpham')
    ->where('sanpham_yeuthich.id_nguoidung', $user->id_nguoidung)
    ->select('san_pham.*', 'sanpham_yeuthich.created_at as liked_at')
    ->orderBy('sanpham_yeuthich.created_at', 'desc')
    ->get();
```
- Dùng **Query Builder (`DB::table`)** thay vì Eloquent — điểm khác biệt duy nhất trong dự án,
  thầy rất dễ hỏi "sao chỗ này viết khác?".

> **Trả lời**: Query Builder trả về mảng `stdClass` thuần, **nhẹ hơn** Eloquent (không phải
> khởi tạo model, không chạy event, không hydrate quan hệ). Với truy vấn chỉ đọc và cần
> **JOIN + đổi tên cột (`as liked_at`)** thì Query Builder gọn hơn.
> **Nhược điểm**: mất `$casts` (cột `specifications` sẽ ra chuỗi JSON thô, không tự
> `json_decode` thành mảng như model `san_pham`) và mất `$hidden`.
> Viết bằng Eloquent tương đương:
> ```php
> $user->sanPhamYeuThich()->withPivot('created_at')->orderByPivot('created_at','desc')->get();
> ```
> → nhất quán hơn với phần còn lại của dự án. Đây là điểm có thể cải thiện.

- `select('san_pham.*', '... as liked_at')` — lấy hết cột sản phẩm, cộng thêm thời điểm
  người dùng bấm thích (đổi tên để không đụng `san_pham.created_at`).
  Đây là lý do phải nêu **tên bảng đầy đủ** cho mọi cột: cả hai bảng đều có `created_at`
  và `id_sanpham` → không ghi rõ sẽ lỗi `Column 'id_sanpham' in where clause is ambiguous`.
- `where(... id_nguoidung, $user->id_nguoidung)` — lại là ownership từ token.
- Các giá trị vẫn được **bind tham số** → không có SQL Injection dù dùng Query Builder.

### b) `toggle()` — Thích / Bỏ thích trong một endpoint

```php
$request->validate(['id_sanpham' => 'required|integer']);
```
- ⚠️ **Validate inline**, không dùng FormRequest — không nhất quán với 33 FormRequest còn lại.
  Thiếu rule **`exists:san_pham,id_sanpham`** → user có thể thích một sản phẩm **không tồn tại**
  (hoặc đã bị xóa) → rác trong bảng, và `index()` join sẽ lọc mất nên user thấy "thích rồi
  mà không hiện". Nêu được lỗi này là điểm cộng.
- Cũng **thiếu unique composite** → nếu 2 request đồng thời cùng vào nhánh `create`
  thì tạo 2 dòng trùng (race condition). Nên đặt UNIQUE INDEX `(id_nguoidung, id_sanpham)` ở DB.

```php
$existing = SanPhamYeuThich::where('id_nguoidung', $user->id_nguoidung)
    ->where('id_sanpham', $request->id_sanpham)
    ->first();

if ($existing) {
    $existing->delete();
    return response()->json(['action' => 'removed', ...]);
}

SanPhamYeuThich::create([...]);
return response()->json(['action' => 'added', ...]);
```
- Mẫu **toggle**: một endpoint làm hai việc, trạng thái quyết định bởi dữ liệu hiện có.
- Trường `'action' => 'removed' | 'added'` cho frontend biết đổi icon trái tim thành đầy hay rỗng
  mà **không cần gọi lại API** để kiểm tra.
- Về REST thuần thì "một endpoint hai hành vi" là không chuẩn (nên là `POST` để thêm,
  `DELETE` để bỏ). Nhưng toggle rất phổ biến trong thực tế vì frontend gọn hơn.
  Nếu thầy hỏi thì trả lời theo hướng **đánh đổi có ý thức**, không phải làm ẩu.
- ⚠️ Cả hai nhánh đều trả **200**; nhánh `added` đúng ra nên trả **201 Created**.

---

## 6.7. Bảng câu hỏi phản biện — nhóm CRUD này

| Câu hỏi | Trả lời gọn |
|---|---|
| Vì sao `UserController::index` có `select([...])`? | Không kéo cột `matkhau` ra khỏi DB — phòng thủ chiều sâu, bổ sung cho `$hidden` |
| `match` khác `switch` chỗ nào? | `match` so sánh `===`, là expression trả giá trị, không bị "rơi tầng" (fallthrough) |
| Vì sao admin không tự xóa được mình? | Tránh hệ thống mất hết admin. Nhưng `update` chưa chặn tự hạ quyền — em nhận là thiếu sót |
| `tokens()->delete()` để làm gì? | Thu hồi mọi token Sanctum, ép đăng xuất trên tất cả thiết bị |
| Vì sao chi nhánh xóa mềm còn voucher xóa cứng? | Chi nhánh bị đơn hàng tham chiếu nên phải giữ. Voucher cũng bị tham chiếu — đây là điểm chưa nhất quán, nên sửa |
| `withTrashed()` là gì? | Gỡ global scope `deleted_at IS NULL` của trait `SoftDeletes`, để thấy bản ghi đã ẩn |
| Vì sao `PersonnelController::store` cần transaction? | Ghi 2 bảng (`nguoi_dung` + `nhanvien`), phải toàn vẹn — hoặc cả hai thành công hoặc không gì cả |
| Vì sao `matkhau` dùng `filled()` mà không `has()`? | Form gửi `matkhau: ""` khi không đổi; `has()` sẽ hash chuỗi rỗng làm mất mật khẩu |
| `phanquyen = -1` nghĩa là gì? | Nhân viên nghỉ việc — soft-disable ở tầng quyền, giữ nguyên lịch sử. Nhược điểm: magic number |
| `whereRaw` có bị SQL Injection không? | Không, vì chuỗi là hằng số do lập trình viên viết. Chỉ nguy hiểm khi nối biến người dùng vào |
| Vì sao so sánh cột với cột phải `whereRaw`? | `where('a','>','b')` sẽ bind `'b'` thành chuỗi. Nên dùng `whereColumn()` |
| Làm sao chống user A sửa địa chỉ user B? | Query kết hợp `where(id)` **và** `where(id_nguoidung = token user)` → không ra kết quả → 404 |
| Sao trả 404 mà không 403 khi không phải chủ sở hữu? | 403 xác nhận tài nguyên tồn tại → rò rỉ thông tin. 404 không phân biệt được |
| Đảm bảo "chỉ một địa chỉ mặc định" bằng cách nào? | Trước khi set cờ mới thì mass-update gỡ cờ tất cả địa chỉ **của user đó** |
| Sao `WishlistController` dùng `DB::table` mà không Eloquent? | Truy vấn chỉ đọc có JOIN + alias, Query Builder nhẹ hơn. Đánh đổi: mất `$casts`/`$hidden` |
| `toggle` có đúng REST không? | Không thuần REST, nhưng là đánh đổi có chủ ý để frontend đơn giản |

---

## 6.8. Danh sách hạn chế tự nhận (nhóm 06)

1. **`error_detail` => `$e->getMessage()`** trong `PersonnelController` — rò rỉ cấu trúc CSDL ra client.
   Nghiêm trọng nhất nhóm này, nên sửa ngay.
2. **`PersonnelController::destroy` không thu hồi token** — nhân viên đã nghỉ vẫn dùng được token cũ.
3. **`UserController::update` không chặn admin tự hạ quyền** — trong khi `destroy` lại có chặn.
4. **Hard delete ở `UserController`, `VoucherController`, `AddressController`** — phá hỏng
   khóa ngoại và dữ liệu lịch sử. Nên SoftDeletes hoặc chặn bằng 409 như `CategoryController`.
5. **`AddressController` thiếu transaction** ở cả 3 chỗ ghi kép → có thể mất địa chỉ mặc định.
6. **`AddressController::update` là full update** — thiếu trường nào sẽ ghi `null` đè lên.
   Cũng cho phép bỏ mặc định mà không set cái khác thay thế.
7. **`WishlistController::toggle` validate inline, thiếu `exists:`** và thiếu UNIQUE INDEX
   chống trùng do race condition.
8. **`UserController::index` không phân trang** — không mở rộng được, trong khi
   `ProductController` đã có `paginate()`.
9. **`role_label` có `4 => 'Khách VIP'` là code chết**, đồng thời **thiếu nhãn cho `-1`**.
   Nên dùng PHP Enum để một nguồn sự thật duy nhất.
10. **Thông báo bị trùng** giữa model event trong `AppServiceProvider` và `ThongBao::create` thủ công.
11. **Tên class `updateStoreBranch`** sai quy ước PSR-1 (phải PascalCase + hậu tố `Request`).
12. **`BranchController::show` thiếu `withTrashed()`** nên không xem được chi tiết chi nhánh đã ẩn.
13. **`PersonnelController` dùng `Model::where()->delete()`** (mass delete) — không kích hoạt
    model event; hiện chưa gây hại nhưng cần biết khi thêm observer sau này.
