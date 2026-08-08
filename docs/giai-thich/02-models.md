# 02 — MODELS (tầng dữ liệu)

25 model Eloquent, ánh xạ 1-1 với bảng MySQL. Trước khi đi từng file, cần nắm 5 khái niệm
mà thầy chắc chắn sẽ hỏi.

## 2.0. Năm khái niệm nền

### a) `$table`
Laravel mặc định đoán tên bảng = tên class ở dạng **số nhiều, snake_case**
(`SanPham` → `san_phams`). Vì DB dự án đặt tên tiếng Việt không theo quy ước Anh ngữ,
phải khai báo tay: `protected $table = 'san_pham';`

### b) `$primaryKey`
Mặc định Laravel giả định khóa chính tên `id`. Dự án dùng `id_sanpham`, `id_donhang`…
nên phải khai báo. Nếu quên → mọi lệnh `find()`, `save()` sẽ tìm cột `id` không tồn tại → lỗi SQL.

### c) `$fillable` — Mass Assignment Protection
```php
protected $fillable = ['ten', 'email', ...];
```
Chỉ những cột nằm trong danh sách này mới được gán hàng loạt qua
`Model::create($request->all())` hoặc `$model->fill(...)`.

**Tại sao cần?** Giả sử form đăng ký gửi lên `{ten, email, matkhau}`. Nếu không có `$fillable`,
kẻ tấn công thêm `phanquyen=1` vào body → tự phong mình làm admin. `$fillable` chặn điều đó.
Đây là câu hỏi phản biện rất phổ biến.

Ngược lại có `$guarded` (danh sách cấm). Dự án dùng `$fillable` — cách an toàn hơn vì mặc định là cấm.

### d) `$casts`
Ép kiểu khi đọc/ghi. Ví dụ `'specifications' => 'array'` khiến Eloquent tự `json_decode` khi đọc
và `json_encode` khi ghi → code PHP thao tác như mảng bình thường.
`'ngaysinh' => 'datetime'` biến chuỗi thành đối tượng Carbon → dùng được `->format()`, `->diffInDays()`.

### e) Quan hệ (Relationships)
| Loại | Ý nghĩa | Ví dụ trong dự án |
|---|---|---|
| `belongsTo` | Nhiều→Một, khóa ngoại nằm ở **bảng này** | `don_hang` belongsTo `Nguoi_dung` |
| `hasMany` | Một→Nhiều, khóa ngoại nằm ở **bảng kia** | `Nguoi_dung` hasMany `don_hang` |
| `hasOne` | Một→Một | `Nguoi_dung` hasOne `nhan_vien` |
| `hasManyThrough` | Một→Nhiều qua bảng trung gian | `san_pham` → `sanpham_serials` qua `ton_kho_cuc_bo` |

Cú pháp: `$this->belongsTo(Lớp::class, 'khóa_ngoại', 'khóa_chính_bảng_kia')`.
Vì dự án không đặt tên khóa theo quy ước, phải truyền đủ 3 tham số.

---

## 2.1. `Nguoi_dung.php` — Người dùng (quan trọng nhất)

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
class Nguoi_dung extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
```
- Kế thừa `Authenticatable` (không phải `Model`) → có sẵn khả năng đăng nhập,
  hash mật khẩu, remember token.
- `HasApiTokens` — trait của **Sanctum**, cấp method `createToken()`, `tokens()`, `currentAccessToken()`.
- `HasFactory` — dùng cho seed/test dữ liệu giả.
- `Notifiable` — cho phép `$user->notify(...)` (dự án chưa dùng).

```php
    protected $table      = 'nguoi_dung';
    protected $primaryKey = 'id_nguoidung';
    public $incrementing  = true;
```
- `$incrementing = true` là mặc định, khai báo lại cho tường minh (khóa chính tự tăng).

```php
    protected $fillable = [
        'ten', 'email', 'matkhau', 'sdt',
        'ngaysinh', 'gioitinh',
        'mancc', 'mancc_id', 'avatar', 'phanquyen',
        'email_verified_at',
    ];
```
- `mancc` / `mancc_id`: "mã nhà cung cấp" — lưu provider OAuth (`google`) và ID người dùng bên đó.
- ⚠️ **`phanquyen` nằm trong `$fillable`** — về lý thuyết đây là rủi ro mass-assignment
  (user tự gửi `phanquyen=1`). Trong dự án đã chặn ở tầng FormRequest (`RegisterRequest`
  không cho phép trường này), nhưng nếu thầy hỏi thì trả lời: "Em nhận thấy đây là điểm cần siết,
  an toàn hơn là bỏ `phanquyen` khỏi `$fillable` và set thủ công trong controller."

```php
    protected $hidden = ['matkhau', 'remember_token'];
```
- Khi model được `toJson()` (trả về API), 2 cột này bị loại bỏ. **Rất quan trọng** —
  không có nó thì hash mật khẩu sẽ lộ ra API response.

```php
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phanquyen'         => 'integer',
    ];
```

```php
    public function getAuthPasswordName(): string
    {
        return 'matkhau';
    }
```
- **Điểm kỹ thuật đáng nói**. Laravel mặc định tìm cột `password` khi xác thực.
  Dự án đặt tên tiếng Việt `matkhau`, nên override method này để báo cho hệ thống Auth biết.
  Nhờ vậy `Auth::attempt(['email'=>..., 'password'=>...])` vẫn hoạt động.

```php
    public function nhanVien()  { return $this->hasOne(nhan_vien::class, 'id_nguoidung', 'id_nguoidung'); }
    public function diaChi()    { return $this->hasMany(diachi_nguoidung::class, 'id_nguoidung', 'id_nguoidung'); }
    public function donHang()   { return $this->hasMany(don_hang::class, 'ma_nguoidung', 'id_nguoidung'); }
    public function lienHe()    { return $this->hasMany(lien_he::class, 'ma_nguoidung', 'id_nguoidung'); }
    public function danhGia()   { return $this->hasMany(danh_gia::class, 'ma_nguoidung', 'id_nguoidung'); }
```
- Thiết kế: 1 người dùng có thể là nhân viên (`hasOne`), có nhiều địa chỉ, nhiều đơn hàng,
  nhiều liên hệ, nhiều đánh giá.

---

## 2.2. `san_pham.php` — Sản phẩm

```php
    protected $fillable = [
        'ma_danhmuc', 'masp', 'tensp', 'gia',
        'thumbail', 'motasanpham', 'specifications', 'embedding',
    ];
```
- `masp` — mã sản phẩm dạng chuỗi (SKU), khác `id_sanpham` là khóa tự tăng.
- `thumbail` — chính tả sai của `thumbnail`, nhưng phải giữ vì trùng tên cột DB.
- `specifications` — JSON chứa thông số kỹ thuật (CPU, RAM, VGA…). Dùng JSON vì mỗi loại
  linh kiện có bộ thuộc tính khác nhau → schema động, không thể dựng cột cứng.
- `embedding` — vector nhúng cho tìm kiếm ngữ nghĩa (xem file 11).

```php
    protected $casts = [
        'specifications' => 'array',
        'gia'            => 'integer',
        'ma_danhmuc'     => 'integer',
    ];
```
- `gia` lưu **integer** (đơn vị VNĐ, không có phần thập phân) → tránh sai số dấu phẩy động
  của kiểu `float`. Đây là best-practice khi xử lý tiền tệ, đáng nêu ra khi phản biện.

```php
    public function serials()
    {
        return $this->hasManyThrough(
            sanpham_serials::class,   // model đích
            ton_kho_cuc_bo::class,    // model trung gian
            'ma_sanpham',             // FK trên bảng trung gian trỏ về san_pham
            'ma_tonkho',              // FK trên bảng đích trỏ về bảng trung gian
            'id_sanpham',             // khóa cục bộ trên san_pham
            'id_khoton'               // khóa cục bộ trên ton_kho_cuc_bo
        );
    }
```
- Lấy **tất cả serial của một sản phẩm ở mọi chi nhánh**, đi qua bảng tồn kho.
- SQL sinh ra tương đương:
  ```sql
  SELECT s.* FROM sanpham_serials s
  JOIN ton_kho_cuc_bo t ON s.ma_tonkho = t.id_khoton
  WHERE t.ma_sanpham = ?
  ```

```php
    public function danhMuc()        { return $this->belongsTo(danh_muc::class, 'ma_danhmuc', 'id_danhmuc'); }
    public function tonKho()         { return $this->hasMany(ton_kho_cuc_bo::class, 'ma_sanpham', 'id_sanpham'); }
    public function chiTietDonHang() { return $this->hasMany(chi_tiet_don_hang::class, 'ma_sanpham', 'id_sanpham'); }
    public function danhGia()        { return $this->hasMany(danh_gia::class, 'ma_sanpham', 'id_sanpham'); }
```

---

## 2.3. `danh_muc.php` — Danh mục (cây phân cấp)

```php
    protected $fillable = ['ten_danhmuc', 'slug', 'danhmuc_cha', 'hinhanh_icon', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'danhmuc_cha' => 'integer'];
```
- `slug` — chuỗi thân thiện URL (`card-do-hoa`), phục vụ SEO.
- `danhmuc_cha` — **self-referencing foreign key**, trỏ về chính bảng `danh_muc`.

```php
    public function danhMucCha() { return $this->belongsTo(danh_muc::class, 'danhmuc_cha', 'id_danhmuc'); }
    public function danhMucCon() { return $this->hasMany(danh_muc::class, 'danhmuc_cha', 'id_danhmuc'); }
```
- Cặp quan hệ đệ quy tạo nên **cấu trúc cây** (Adjacency List model).

```php
    public function conVaChau()
    {
        return $this->hasMany(danh_muc::class, 'danhmuc_cha', 'id_danhmuc')
                    ->with('conVaChau');
    }
```
- **Eager loading đệ quy**: nạp con, rồi tự động nạp cháu, chắt… đến hết.
- ⚠️ Nguy cơ: nếu cây quá sâu → N+1 query hoặc tràn bộ nhớ. Với danh mục thương mại điện tử
  (thường 2–3 cấp) thì chấp nhận được. Nếu thầy hỏi giải pháp tốt hơn: **Nested Set** hoặc
  **Closure Table** cho phép lấy toàn cây bằng 1 query.

```php
    public function laDanhMucGoc(): bool { return is_null($this->danhmuc_cha); }
    public function laDanhMucLa(): bool  { return $this->danhMucCon()->doesntExist(); }
```
- Helper kiểm tra nút gốc (không có cha) và nút lá (không có con).
- `doesntExist()` chạy `SELECT EXISTS(...)` — nhanh hơn `->count() === 0` vì không đếm hết.

---

## 2.4. `don_hang.php` — Đơn hàng

```php
    protected $fillable = [
        'ma_nguoidung', 'ma_chinhanh', 'ma_khuyenmai',
        'ma_diachinguoidung', 'tongtien',
        'phuong_thuc_tt', 'trang_thai_dh',
        'ghichu', 'thoigiandathang',
    ];
```
- `ma_chinhanh` — đơn hàng **được gán cho một chi nhánh cụ thể**. Đây là điểm cốt lõi của
  nghiệp vụ đa chi nhánh: hệ thống chọn chi nhánh có đủ tồn kho & gần khách nhất.
- `trang_thai_dh` — chuỗi trạng thái (`cho_xac_nhan`, `da_xac_nhan`, `dang_giao`, `hoan_thanh`, `da_huy`).
- `phuong_thuc_tt` — `COD` hoặc `VNPAY`.

```php
    protected $casts = ['thoigiandathang' => 'datetime', 'tongtien' => 'integer'];
```

Quan hệ: `belongsTo` người dùng / chi nhánh / khuyến mãi / địa chỉ,
`hasMany` chi tiết đơn hàng và thanh toán.

> **Vì sao `thanhToan` là hasMany chứ không hasOne?** Vì một đơn có thể có nhiều lần giao dịch
> (thanh toán thất bại rồi thử lại, hoặc hoàn tiền). Lưu lịch sử đầy đủ để đối soát.

---

## 2.5. `chi_tiet_don_hang.php`

```php
    protected $fillable = ['ma_donhang', 'ma_sanpham', 'soluong', 'don_gia', 'thanh_tien'];
```
- **`don_gia` được sao chép vào đây, không tham chiếu `san_pham.gia`.**
  Lý do: giá sản phẩm thay đổi theo thời gian. Nếu chỉ join sang `san_pham` thì hóa đơn cũ
  sẽ hiển thị giá mới → sai. Đây gọi là **snapshot dữ liệu tại thời điểm giao dịch**.
  Câu hỏi này thầy rất hay hỏi, nhớ kỹ.
- `thanh_tien = soluong × don_gia` — lưu sẵn (denormalize) để không phải tính lại khi thống kê.

---

## 2.6. `ton_kho_cuc_bo.php` — Tồn kho cục bộ (cốt lõi hệ thống)

```php
    protected $table      = 'ton_kho_cuc_bo';
    protected $primaryKey = 'id_khoton';
    protected $fillable = ['ma_sanpham', 'ma_chinhanh', 'soluongtonkho', 'soluongkhothap'];
```
- Bảng này là **giao của Sản phẩm × Chi nhánh**: mỗi dòng = "sản phẩm A ở chi nhánh B còn N cái".
- `soluongkhothap` — ngưỡng cảnh báo tồn thấp. Khi `soluongtonkho <= soluongkhothap`
  hệ thống bắn thông báo cần nhập thêm.

```php
    public function serials() { return $this->hasMany(sanpham_serials::class, 'ma_tonkho', 'id_khoton'); }
```
- Một dòng tồn kho có nhiều serial vật lý.

> **Câu hỏi phản biện kinh điển**: "Sao vừa lưu `soluongtonkho` vừa lưu bảng serial?
> Có bị dư thừa không?"
>
> Trả lời: Có dư thừa **có chủ đích** (denormalization). Đếm `COUNT(*)` bảng serial mỗi lần
> hiển thị danh sách sản phẩm sẽ rất nặng khi kho lớn. `soluongtonkho` là **cache** để đọc nhanh.
> Rủi ro là hai nguồn lệch nhau, nên mọi thao tác thay đổi serial đều được bọc trong
> `DB::transaction()` và cập nhật đồng thời cả hai. Có thể bổ sung job đối soát định kỳ.

---

## 2.7. `sanpham_serials.php` — Serial từng sản phẩm vật lý

```php
    protected $fillable = ['ma_tonkho', 'serial_code', 'tinhtrang', 'min_soluongkho', 'ngaycuthe'];
```
- `serial_code` — mã serial thật in trên máy (IMEI/SN).
- `tinhtrang` — trạng thái vòng đời: `con_hang`, `da_ban`, `dang_chuyen`, `bao_hanh`, `hong`.
- `ma_tonkho` trỏ về dòng tồn kho → **serial biết mình đang ở chi nhánh nào**.
  Khi điều chuyển kho, chỉ cần đổi `ma_tonkho` của serial đó.

> Đây là điểm khác biệt của đề tài so với web bán hàng thông thường: **quản lý đến từng
> đơn vị sản phẩm**, cho phép truy vết bảo hành theo serial.

---

## 2.8. `chi_nhanh.php` — Chi nhánh

```php
    use HasFactory, SoftDeletes;
```
- `SoftDeletes` — khi gọi `->delete()`, Laravel chỉ ghi timestamp vào cột `deleted_at`
  thay vì xóa thật. Bản ghi bị ẩn khỏi mọi query mặc định.
- **Vì sao chỉ chi nhánh dùng SoftDeletes?** Vì đơn hàng cũ tham chiếu tới chi nhánh.
  Xóa cứng sẽ làm hỏng dữ liệu lịch sử (orphan foreign key). SoftDelete cho phép
  "đóng cửa chi nhánh" mà vẫn giữ được báo cáo doanh thu quá khứ. Có route `restore` để khôi phục.

```php
    protected $fillable = ['ten_chinhanh','ma_chinhanh','sdt_chinhanh','email_chinhanh',
                           'diachi_chitiet','maso_phuong','maso_tp','maso_tinh','map_link'];
```
- `maso_phuong/tp/tinh` — mã hành chính chuẩn (dùng API địa giới Việt Nam), phục vụ tính
  khoảng cách để chọn chi nhánh giao hàng.

---

## 2.9. `nhan_vien.php`

```php
    protected $table      = 'nhanvien';
    protected $fillable = ['id_nguoidung', 'chucvu', 'machinhanh'];
```
- Bảng mở rộng của `nguoi_dung`. Một tài khoản có `phanquyen=2` sẽ có 1 dòng ở đây,
  gắn với `machinhanh` — **nhân viên thuộc chi nhánh nào**.
  (Quy ước: 1=admin, 2=nhân viên, 3=khách, -1=nhân viên đã gỡ.)
- Đây là cơ sở để các controller `Staff*` lọc dữ liệu: nhân viên chỉ thấy đơn/kho chi nhánh mình.
- Lưu ý tên bảng `nhanvien` (không gạch dưới) khác quy ước các bảng còn lại — không nhất quán nhưng vô hại.

---

## 2.10. `phieu_dieu_chuyen.php` + `chi_tiet_dieu_chuyen.php` + `dieu_chuyen_serials.php`

Bộ ba mô hình nghiệp vụ **điều chuyển hàng giữa chi nhánh**, theo cấu trúc 3 tầng:

```
phieu_dieu_chuyen (1 phiếu: kho xuất → kho nhập, trạng thái, người duyệt)
    └── chi_tiet_dieu_chuyen (mỗi sản phẩm + số lượng)
            └── dieu_chuyen_serials (serial cụ thể được chuyển)
```

```php
// phieu_dieu_chuyen
protected $fillable = ['ma_kho_xuat','ma_kho_nhap','nguoi_tao','nguoi_duyet',
                       'trang_thai','ly_do','ghi_chu'];

public function khoXuat()  { return $this->belongsTo(chi_nhanh::class, 'ma_kho_xuat', 'id_chinhanh'); }
public function khoNhap()  { return $this->belongsTo(chi_nhanh::class, 'ma_kho_nhap', 'id_chinhanh'); }
```
- Hai quan hệ **cùng trỏ về `chi_nhanh`** nhưng khác khóa ngoại — đây là lý do phải
  truyền tên khóa tường minh, Laravel không tự đoán được.

```php
public function nguoiTao()   { return $this->belongsTo(Nguoi_dung::class, 'nguoi_tao', 'id_nguoidung'); }
public function nguoiDuyet() { return $this->belongsTo(Nguoi_dung::class, 'nguoi_duyet', 'id_nguoidung'); }
```
- Ghi nhận **luồng phê duyệt 2 bước**: người tạo ≠ người duyệt → kiểm soát nội bộ,
  chống gian lận kho.

---

## 2.11. `khuyen_mai.php` — Voucher

```php
    protected $fillable = ['ma_voucher','tenkhuyenmai','loai_giamgia',
                           'gia_trigiam','don_toithieu','giam_toida',
                           'soluongma','dasudung',
                           'ngaybdchuongtrinh','ngayketthucchuongtrinh'];
```
| Cột | Ý nghĩa |
|---|---|
| `loai_giamgia` | `phan_tram` hoặc `tien_mat` |
| `gia_trigiam` | 10 (nếu %) hoặc 50000 (nếu tiền mặt) |
| `don_toithieu` | Đơn phải đạt tối thiểu bao nhiêu mới dùng được |
| `giam_toida` | Trần giảm giá — chặn voucher 50% áp lên đơn 100 triệu |
| `soluongma` / `dasudung` | Tổng mã phát hành / đã dùng, để giới hạn số lượt |

> **Câu hỏi**: "Làm sao chống việc 2 người cùng dùng mã cuối cùng?"
> → Trong `PurchaseController` việc tăng `dasudung` nằm trong transaction và
> dùng khóa bi quan (`lockForUpdate`) — xem file 07.

---

## 2.12. `thanh_toan.php`

```php
    protected $fillable = ['ma_donhang','soluong','phuong_thuc',
                           'ma_giaodich','sotien','trangthai','ngaythanhtoan'];
```
- `ma_giaodich` — mã giao dịch từ VNPay (`vnp_TransactionNo`), dùng đối soát khi tra cứu/khiếu nại.

---

## 2.13. `BaoHanh_HoTro.php` — Bảo hành & hỗ trợ

```php
    protected $fillable = ['ma_donhang','ma_nguoidung','ma_nhanvien','ma_chinhanh','ma_serial',
                           'loai_yeu_cau','mo_ta_loi','trang_thai','ket_qua_xu_ly',
                           'ngay_tiep_nhan','ngay_hoan_thanh'];
```
- Gắn với **cả đơn hàng lẫn serial** → truy vết chính xác máy nào đang bảo hành.
- `ma_nhanvien` — ai tiếp nhận, `ma_chinhanh` — tiếp nhận ở đâu.
- `loai_yeu_cau`: bảo hành / đổi trả / hỗ trợ kỹ thuật.

Có đủ 5 quan hệ `belongsTo` tương ứng 5 khóa ngoại.

---

## 2.14. `ChiTietDonHangSerial.php` — Bảng nối đơn hàng ↔ serial

```php
    protected $table = 'chi_tiet_don_hang__serial';   // chú ý 2 dấu gạch dưới
    protected $fillable = ['ma_chitietdh','ma_serial'];
```
- Bảng trung gian (pivot) giải bài toán: 1 dòng chi tiết đơn mua 3 cái → có 3 serial cụ thể.
- Nhờ bảng này mà khi khách mang máy đi bảo hành, tra serial ra được **đúng đơn hàng**,
  **đúng ngày mua**, tính được còn hạn bảo hành hay không.

> Đây là mắt xích quan trọng nhất nối 3 phân hệ: bán hàng ↔ kho ↔ bảo hành.

---

## 2.15. `OtpToken.php`

```php
    protected $fillable = ['identifier','token','expires_at'];
    protected $casts    = ['expires_at' => 'datetime'];

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }
```
- `identifier` — email người nhận OTP (dùng chung cho cả đăng ký lẫn quên mật khẩu).
- `isExpired()` — helper đọc dễ hơn `now() > $this->expires_at`. Nhờ `$casts`,
  `expires_at` là đối tượng Carbon nên so sánh được bằng `isAfter()`.

---

## 2.16. `ThongBao.php` — Thông báo nội bộ (admin)

```php
    protected $fillable = ['loai_thong_bao','tieu_de','noi_dung','nguoi_doc','link'];
    protected $casts    = ['nguoi_doc' => 'array'];  // JSON → array PHP
```
- **Thiết kế đáng nói**: `nguoi_doc` là **mảng JSON chứa danh sách ID admin đã đọc**,
  thay vì tạo bảng pivot `thongbao_nguoidoc`.
- Ưu: đơn giản, 1 thông báo = 1 dòng, không cần join.
- Nhược: không index được, khó truy vấn "thông báo nào user X chưa đọc" bằng SQL thuần
  (phải dùng `JSON_CONTAINS` hoặc lọc trong PHP). Chấp nhận được vì số admin ít.

---

## 2.17. `ThongBaoKhachHang.php` — Thông báo cho khách

```php
    protected $fillable = ['id_nguoidung','loai_thong_bao','tieu_de','noi_dung','da_doc','link'];

    public function user() { return $this->belongsTo(nguoi_dung::class, 'id_nguoidung', 'id_nguoidung'); }
```
- Khác `ThongBao`: mỗi bản ghi thuộc về **một khách cụ thể** (`id_nguoidung`),
  dùng cờ boolean `da_doc` đơn giản.
- ⚠️ `nguoi_dung::class` viết sai hoa/thường — class thật tên `Nguoi_dung`.
  Trên Windows/macOS (filesystem không phân biệt hoa thường) vẫn chạy;
  trên **Linux production sẽ lỗi "Class not found"**. Nếu thầy hỏi, đây là bug thật cần sửa.

---

## 2.18. Các model còn lại (cấu trúc đơn giản, cùng một mẫu)

| Model | Bảng | Vai trò |
|---|---|---|
| `diachi_nguoidung` | `diachi_nguoidung` | Sổ địa chỉ giao hàng; `matudien_diachi` (boolean) = địa chỉ mặc định |
| `danh_gia` | `danh_gia` | Đánh giá sao + bình luận sản phẩm |
| `lien_he` | `lien_he` | Form liên hệ; `trangthai` (int) = đã xử lý hay chưa |
| `SanPhamYeuThich` | `sanpham_yeuthich` | Wishlist, chỉ 2 cột `id_nguoidung` + `id_sanpham` |
| `chi_tiet_dieu_chuyen` | `chi_tiet_dieu_chuyen` | Dòng chi tiết phiếu điều chuyển |
| `dieu_chuyen_serials` | `dieu_chuyen_serials` | Serial cụ thể trong phiếu điều chuyển |

---

## 2.19. Nhận xét chung để trả lời thầy

**Ưu điểm thiết kế:**
1. Tên bảng/cột tiếng Việt → dễ đọc với người Việt, khớp tài liệu nghiệp vụ.
2. `$fillable` đầy đủ ở mọi model → chống mass assignment.
3. `$hidden` che mật khẩu.
4. Snapshot giá trong `chi_tiet_don_hang` → hóa đơn lịch sử chính xác.
5. Tách `ton_kho_cuc_bo` → hỗ trợ đa chi nhánh thật sự, không phải kho ảo.
6. Quản lý serial → truy vết bảo hành đến từng máy.

**Hạn chế tự nhận (nếu thầy hỏi, thừa nhận sẽ được điểm cao hơn là chối):**
1. Quy ước đặt tên class không nhất quán: có `snake_case` (`san_pham`), có `PascalCase` (`SanPhamYeuThich`),
   có lai (`BaoHanh_HoTro`). PSR-1 yêu cầu class dùng `StudlyCaps`.
2. `ThongBaoKhachHang::user()` tham chiếu sai tên class → lỗi trên Linux.
3. `nguoi_doc` dạng JSON khó mở rộng.
4. Không dùng `$appends` / Accessor để chuẩn hóa output → controller phải tự map dữ liệu.
5. Không có Model dành cho `WarehouseReceipt` (phiếu nhập) — controller thao tác trực tiếp qua
   `DB::table()` hoặc dùng lại các model kho.
