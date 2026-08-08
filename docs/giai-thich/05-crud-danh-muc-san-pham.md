# 05 — DANH MỤC & SẢN PHẨM

`CategoryController` (148 dòng) và `ProductController` (406 dòng).
Phần này chứa 2 kỹ thuật đáng nói: **duyệt cây danh mục** và **tìm kiếm ngữ nghĩa AI**.

---

## 5.1. `CategoryController::index()` — cây danh mục cho menu

```php
$categories = danh_muc::with(['danhMucCon.danhMucCon'])
    ->whereNull('danhmuc_cha')
    ->where('is_active', true)
    ->orderBy('id_danhmuc', 'asc')
    ->get();
```

Đọc từng mệnh đề:
- `with(['danhMucCon.danhMucCon'])` — **eager loading 2 cấp**. Dấu chấm nghĩa là
  "nạp con, rồi nạp con của con". Kết quả: cây 3 tầng (gốc → con → cháu).
- `whereNull('danhmuc_cha')` — chỉ lấy **danh mục gốc**. Các cấp dưới đã nằm trong
  quan hệ nên không cần lấy riêng.
- `where('is_active', true)` — ẩn danh mục đã tắt.

> **Câu hỏi kinh điển: N+1 query là gì và code này chống thế nào?**
>
> Nếu **không** dùng `with()`:
> ```php
> $roots = danh_muc::whereNull('danhmuc_cha')->get();   // 1 query
> foreach ($roots as $r) { $r->danhMucCon; }            // N query nữa
> ```
> 10 danh mục gốc → 11 query. Có cháu nữa → hàng chục query.
>
> Với `with()`, Laravel gom lại thành **3 query cố định**:
> ```sql
> SELECT * FROM danh_muc WHERE danhmuc_cha IS NULL AND is_active = 1;
> SELECT * FROM danh_muc WHERE danhmuc_cha IN (1,2,3,...);      -- con
> SELECT * FROM danh_muc WHERE danhmuc_cha IN (11,12,13,...);   -- cháu
> ```
> Đây là điểm nên chủ động trình bày.

- ⚠️ Chỉ nạp **đúng 2 cấp**. Nếu cây sâu 4 tầng thì tầng 4 bị mất.
  Model có sẵn quan hệ đệ quy `conVaChau()` (xem file 02) nhưng ở đây không dùng —
  có lẽ để giới hạn độ sâu cho menu. Nếu thầy hỏi, giải thích là **chủ đích**.
- ⚠️ Lọc `is_active` chỉ áp cho **danh mục gốc**. Danh mục con đã tắt vẫn hiện ra
  vì `with()` không kèm điều kiện. Sửa bằng:
  `with(['danhMucCon' => fn($q) => $q->where('is_active', true)])`. **Bug thật.**

### `all()` — danh sách phẳng cho trang admin

```php
$categories = danh_muc::orderBy('id_danhmuc','asc')->get([
    'id_danhmuc','ten_danhmuc','slug','danhmuc_cha','hinhanh_icon','is_active',
]);
```
- Truyền mảng cột vào `get()` = `SELECT` chỉ 6 cột thay vì `SELECT *`.
  Giảm băng thông, đặc biệt khi bảng có cột lớn.
- Không lọc `is_active` vì admin cần thấy cả danh mục đã ẩn để bật lại.
- Trả **phẳng** (không lồng) — frontend admin tự dựng cây từ `danhmuc_cha`.

### `show($id)`

```php
$category = danh_muc::with(['danhMucCha','danhMucCon','sanPham'])->find($id);
if (!$category) { return response()->json([...], 404); }
```
- Nạp cả cha, con và sản phẩm thuộc danh mục.
- Trả **404 Not Found** đúng chuẩn REST khi không tìm thấy.
- ⚠️ `sanPham` nạp **toàn bộ** sản phẩm không phân trang. Danh mục 5000 sản phẩm
  sẽ làm response nặng hàng chục MB. Nên phân trang hoặc chỉ trả `count`.

---

## 5.2. `store()` và `update()`

```php
public function store(StoreCategoryRequest $request)
{
    $category = danh_muc::create([
        'ten_danhmuc'  => $request->ten_danhmuc,
        'slug'         => $request->slug,
        'danhmuc_cha'  => $request->danhmuc_cha  ?? null,
        'hinhanh_icon' => $request->hinhanh_icon ?? null,
        'is_active'    => $request->is_active    ?? true,
    ]);
    return response()->json([...], 201);
}
```
- `slug` đã được `prepareForValidation()` của FormRequest tự sinh (xem file 03).
- Liệt kê **tường minh từng trường** → an toàn mass assignment.
- `?? true` — mặc định danh mục mới ở trạng thái hiển thị.
- **201 Created** — mã HTTP chuẩn khi tạo tài nguyên mới (khác 200 OK của các thao tác khác).

```php
public function update(UpdateCategoryRequest $request, $id)
{
    $category = danh_muc::find($id);
    if (!$category) { return response()->json([...], 404); }
    $category->update($request->validated());
    return response()->json([...], 200);
}
```
- `$request->validated()` trả về **chỉ những trường đã khai trong `rules()`**
  và đã pass validate. Client gửi thêm trường lạ → bị loại.
  Đây là cách gọi `update()` an toàn, khác hẳn `$request->all()`.

> **Vì sao `find()` + kiểm tra `null` thay vì Route Model Binding?**
> Laravel hỗ trợ `public function update(Request $r, danh_muc $category)` — tự tìm và
> tự trả 404. Nhưng vì khóa chính không tên `id`, phải cấu hình thêm `getRouteKeyName()`.
> Dự án chọn cách thủ công để kiểm soát thông báo lỗi tiếng Việt. Đánh đổi: code lặp lại.

---

## 5.3. `destroy()` — xóa có kiểm tra ràng buộc

```php
$countCon = danh_muc::where('danhmuc_cha', $id)->count();
if ($countCon > 0) {
    return response()->json([
        'status'  => 'error',
        'message' => 'Không thể xóa! Danh mục này đang có '.$countCon.' danh mục con...',
    ], 409);
}
$countSanPham = $category->sanPham()->count();
if ($countSanPham > 0) {
    return response()->json([... 'đang chứa '.$countSanPham.' sản phẩm...'], 409);
}
$category->delete();
```
- **Hai lớp bảo vệ** trước khi xóa:
  1. Còn danh mục con → xóa sẽ tạo **orphan** (danh mục con trỏ tới cha không tồn tại,
     biến mất khỏi cây, không bao giờ hiển thị lại được).
  2. Còn sản phẩm → sản phẩm mất danh mục, `ma_danhmuc` trỏ vào hư không.
- **409 Conflict** — mã HTTP đúng cho "yêu cầu hợp lệ nhưng xung đột với trạng thái
  hiện tại của tài nguyên". Phân biệt với 400 (sai cú pháp) và 422 (sai validate).
  Chọn đúng mã này là điểm cộng, nên nêu ra.
- Thông báo lỗi **có số lượng cụ thể** và **gợi ý cách xử lý** → UX tốt.
- `$category->sanPham()` có `()` = lấy query builder rồi `->count()` chạy
  `SELECT COUNT(*)`. Nếu viết `$category->sanPham->count()` (không ngoặc) thì
  Laravel **nạp hết bản ghi về PHP rồi mới đếm** → chậm hơn nhiều.
  **Đây là chi tiết nhỏ nhưng thầy có thể hỏi.**

```php
$ten = $category->ten_danhmuc;
$category->delete();
return response()->json([... 'Đã xóa danh mục "'.$ten.'" thành công!'], 200);
```
- Lưu tên **trước** khi xóa để còn dùng trong thông báo.

### `toggle()` — bật/tắt hiển thị

```php
$category->is_active = !$category->is_active;
$category->save();
return response()->json([
    'message' => 'Đã '.($category->is_active ? 'kích hoạt' : 'ẩn').' danh mục...',
    'data'    => ['id_danhmuc'=>..., 'is_active'=>...],
], 200);
```
- Đảo giá trị boolean. Nhờ `$casts = ['is_active'=>'boolean']` ở model,
  `!` hoạt động đúng kể cả khi DB lưu `0/1`.
- Trả về trạng thái mới để frontend đồng bộ UI mà không cần gọi lại API.
- **Ý nghĩa nghiệp vụ**: thay vì xóa danh mục (mất dữ liệu, vướng ràng buộc),
  chỉ cần ẩn đi. Giống SoftDelete nhưng do người dùng chủ động điều khiển.

---

## 5.4. `ProductController::index()` — lọc theo chi nhánh

```php
$branchId = $request->query('branch_id');
$query = san_pham::with(['danhMuc:id_danhmuc,ten_danhmuc,slug']);
```
- `$request->query()` đọc tham số **query string** (`?branch_id=1`),
  phân biệt với `$request->input()` (đọc cả body).
- `with(['danhMuc:cột1,cột2'])` — cú pháp **chọn cột trong eager loading**.
  Lưu ý: **bắt buộc phải có khóa chính** (`id_danhmuc`) trong danh sách,
  nếu không Laravel không ghép được quan hệ và trả `null`. Chi tiết dễ sai.

```php
if ($branchId) {
    $query->whereHas('tonKho', function($q) use ($branchId) {
        $q->where('ma_chinhanh', $branchId)
          ->where('soluongtonkho', '>', 0);
    });
}
```
- `whereHas` sinh **subquery EXISTS**:
  ```sql
  SELECT * FROM san_pham WHERE EXISTS (
      SELECT 1 FROM ton_kho_cuc_bo
      WHERE ton_kho_cuc_bo.ma_sanpham = san_pham.id_sanpham
        AND ma_chinhanh = ? AND soluongtonkho > 0
  )
  ```
- **Ý nghĩa nghiệp vụ cốt lõi**: khách chọn chi nhánh Cần Thơ thì chỉ thấy sản phẩm
  **thực sự còn hàng ở Cần Thơ**. Đây là điểm phân biệt hệ thống đa chi nhánh
  với web bán hàng kho ảo thông thường. **Nên nhấn mạnh khi phản biện.**
- `use ($branchId)` — closure trong PHP không tự thấy biến bên ngoài, phải import.

```php
$products = $query->orderBy('id_sanpham','desc')->get()
    ->map(function ($product) {
        $product->ten_danhmuc = $product->danhMuc ? $product->danhMuc->ten_danhmuc : 'Chưa phân loại';
        return $product;
    });
```
- `orderBy('id_sanpham','desc')` — sắp mới nhất trước (thay cho `created_at`).
- `->map()` chạy trên **Collection** (đã lấy về PHP), thêm thuộc tính ảo `ten_danhmuc`
  để frontend truy cập phẳng thay vì `product.danhMuc.ten_danhmuc`.
- Toán tử 3 ngôi chống lỗi khi sản phẩm chưa gán danh mục.
- ⚠️ Cách chuẩn Laravel là dùng **Accessor + `$appends`** trong model, hoặc **API Resource**.
  Làm ở controller khiến logic bị lặp nếu có endpoint khác cần.
- ⚠️ **Không phân trang** — `get()` lấy toàn bộ sản phẩm. Với 10.000 sản phẩm sẽ
  rất nặng. Hàm `byCategory()` bên dưới có phân trang, không nhất quán.

---

## 5.5. `byCategory()` — hàm phức tạp nhất của controller này

Xử lý trang danh sách sản phẩm: lọc danh mục (bao gồm con cháu), lọc chi nhánh,
lọc giá, sắp xếp, phân trang, và dựng breadcrumb.

```php
$slugOrId = $request->query('danh-muc');
$sort     = $request->query('sort', 'newest');
$minPrice = $request->query('min_price', 0);
$maxPrice = $request->query('max_price', 999999999);
$perPage  = (int) $request->query('per_page', 16);
```
- Tham số thứ 2 của `query()` là **giá trị mặc định**.
- `(int)` ép kiểu `per_page` vì query string luôn là chuỗi.
- ⚠️ `per_page` **không giới hạn trên**. Kẻ tấn công gọi `?per_page=999999`
  → server phải load toàn bộ DB → **DoS**. Nên `min($perPage, 100)`.
  Đây là lỗ hổng đáng nêu.

```php
$category = danh_muc::where('slug', $slugOrId)
    ->orWhere('id_danhmuc', is_numeric($slugOrId) ? $slugOrId : 0)
    ->first();
```
- Hỗ trợ tra bằng **slug** (`/san-pham?danh-muc=card-do-hoa`) hoặc **id** (`?danh-muc=5`).
- `is_numeric(...) ? ... : 0` — nếu là chuỗi thì so với `0` (không bao giờ khớp),
  tránh lỗi MySQL khi so sánh chuỗi với cột INT.

### Gom ID danh mục con & cháu

```php
$categoryIds[] = $category->id_danhmuc;

$children = danh_muc::where('danhmuc_cha', $category->id_danhmuc)
                    ->pluck('id_danhmuc')->toArray();
$categoryIds = array_merge($categoryIds, $children);

if (!empty($children)) {
    $grandChildren = danh_muc::whereIn('danhmuc_cha', $children)
                             ->pluck('id_danhmuc')->toArray();
    $categoryIds = array_merge($categoryIds, $grandChildren);
}
```
- **Nghiệp vụ**: chọn "Linh kiện máy tính" phải thấy cả sản phẩm trong
  "Linh kiện > Card đồ họa > RTX". Nếu chỉ lọc `ma_danhmuc = 1` thì trang trống.
- `pluck('cột')` — lấy 1 cột thành mảng phẳng `[2,3,4]` thay vì mảng object.
  Nhẹ hơn `get()` rất nhiều.
- Duyệt **2 cấp bằng 2 query cố định** (không lặp trong vòng for) — tránh N+1.
- ⚠️ Cứng ở 2 cấp. Cây 4 tầng sẽ sót. Giải pháp tổng quát: đệ quy, hoặc
  **Nested Set** (1 query lấy toàn bộ nhánh bằng `WHERE lft BETWEEN ... AND ...`).
  Nên chuẩn bị câu trả lời này.

### Dựng breadcrumb bằng vòng lặp lên cha

```php
$ancestor = $category;
while ($ancestor->danhmuc_cha) {
    $parent = danh_muc::find($ancestor->danhmuc_cha);
    if ($parent) {
        $categoryIds[] = $parent->id_danhmuc;
        $breadcrumb[]  = [...];
        $ancestor = $parent;
    } else {
        break;
    }
}
$categoryIds = array_unique($categoryIds);
$breadcrumb  = array_reverse($breadcrumb);
```
- Leo ngược lên gốc để dựng đường dẫn: `Trang chủ > Linh kiện > Card đồ họa > RTX`.
- `array_reverse` vì thu thập từ dưới lên, hiển thị phải từ trên xuống.
- `array_unique` vì cha có thể đã nằm trong danh sách con cháu (dữ liệu bất thường).
- `break` khi không tìm thấy cha → chống vòng lặp vô hạn khi khóa ngoại hỏng.
- ⚠️ **Đây là N+1 thật sự**: mỗi cấp 1 query `find()`. Cây 3 tầng = 3 query.
  Chấp nhận được vì cây nông, nhưng đúng ra nên nạp toàn bộ danh mục vào bộ nhớ
  một lần rồi duyệt (bảng danh mục nhỏ, cache được).
- ⚠️ **Không có bộ đếm chống vòng lặp**: nếu dữ liệu có chu trình
  (A là cha B, B là cha A) thì `while` chạy **vô hạn** → treo server.
  Đây chính là hệ quả của việc `UpdateCategoryRequest` không kiểm tra chu trình (file 03).
  → **Liên kết 2 lỗ hổng lại là câu trả lời rất thuyết phục nếu thầy hỏi sâu.**

### Áp bộ lọc và phân trang

```php
$query->whereBetween('gia', [(int)$minPrice, (int)$maxPrice]);
switch ($sort) {
    case 'price_asc':  $query->orderBy('gia','asc');  break;
    case 'price_desc': $query->orderBy('gia','desc'); break;
    case 'oldest':     $query->orderBy('id_sanpham','asc');  break;
    case 'newest':
    default:           $query->orderBy('id_sanpham','desc'); break;
}
$products = $query->paginate($perPage);
```
- Dùng `switch` với **whitelist** thay vì `orderBy($request->sort)` —
  **chống SQL Injection qua tên cột**. Tên cột không thể tham số hóa bằng prepared
  statement, nên bắt buộc phải whitelist. **Đây là điểm bảo mật quan trọng, nhớ nêu.**
- `default` trong `switch` đảm bảo giá trị lạ vẫn có hành vi an toàn.
- `paginate()` chạy 2 query: `COUNT(*)` để tính tổng trang, và `SELECT ... LIMIT ? OFFSET ?`.

```php
return response()->json([
    'category'     => $category ? [...] : null,
    'breadcrumb'   => $breadcrumb,
    'total'        => $products->total(),
    'per_page'     => $products->perPage(),
    'current_page' => $products->currentPage(),
    'last_page'    => $products->lastPage(),
    'data'         => $products->items(),
]);
```
- Tự bóc metadata phân trang thay vì trả thẳng object paginator
  (mặc định Laravel trả kèm `first_page_url`, `links`… không cần cho SPA).

---

## 5.6. `show($id)` — chi tiết sản phẩm

```php
$product = san_pham::with(['danhMuc', 'tonKho.chiNhanh'])->find($id);
```
- `tonKho.chiNhanh` — nạp lồng: tồn kho của sản phẩm, và với mỗi dòng tồn kho
  nạp luôn thông tin chi nhánh. Nhờ vậy frontend hiển thị được
  "Còn 3 cái tại Cần Thơ, 5 cái tại Ninh Kiều".
- Phần dựng breadcrumb giống hệt `byCategory()` — **code trùng lặp**.
  Nên tách thành method riêng `buildBreadcrumb($category)`. Nếu thầy hỏi về
  nguyên tắc DRY thì đây là ví dụ vi phạm rõ nhất trong file.

---

## 5.7. `store()` / `update()` / `destroy()` — có tích hợp AI

```php
public function store(StorePRoductRequest $request)
{
    $product = san_pham::create($request->validated());
    try {
        $this->updateProductEmbedding($product);
    } catch (\Exception $e) {
        Log::warning('[Embedding] Không thể tạo embedding...: '.$e->getMessage());
    }
    $product->load('danhMuc:id_danhmuc,ten_danhmuc');
    return response()->json([...], 201);
}
```
- Sau khi lưu DB, **đẩy sản phẩm sang Python service** để sinh vector tìm kiếm.
- **`try/catch` bọc riêng phần AI** — thiết kế rất đúng:
  nếu Python service chết, việc thêm sản phẩm **vẫn thành công**.
  Chức năng phụ không được làm hỏng chức năng chính.
  → Đây gọi là **graceful degradation**, nên nêu chủ động.
- `Log::warning` thay vì `Log::error` — vì hệ thống vẫn hoạt động, chỉ là
  sản phẩm này tạm thời không xuất hiện trong tìm kiếm AI.
  Có lệnh `php artisan qdrant:index --all` để đồng bộ lại (xem file 11).
- `$product->load(...)` — nạp quan hệ **sau khi** đã có model (khác `with()` dùng lúc query).

```php
public function destroy(Request $request, $id)
{
    $product = san_pham::find($id);
    if (!$product) { return ...404; }
    $productId = $product->id_sanpham;
    $product->delete();
    try {
        $pythonServiceUrl = env('PYTHON_SEARCH_URL', 'http://localhost:8001');
        Http::timeout(5)->delete("{$pythonServiceUrl}/delete/{$productId}");
    } catch (\Exception $e) { Log::warning(...); }
}
```
- Xóa DB rồi xóa vector tương ứng → giữ 2 nguồn dữ liệu đồng bộ.
- `timeout(5)` — không chờ quá 5 giây, tránh treo request.
- ⚠️ **Xóa cứng sản phẩm** trong khi `chi_tiet_don_hang` có khóa ngoại `ma_sanpham`.
  Nếu DB có ràng buộc `FOREIGN KEY` thì lệnh này **ném lỗi 500**; nếu không có
  ràng buộc thì đơn hàng cũ trỏ vào sản phẩm không tồn tại → hỏng lịch sử.
  → Sản phẩm **nên dùng SoftDeletes** như `chi_nhanh`. **Đây là điểm yếu đáng kể,
  và cũng là câu hỏi hay: "xóa sản phẩm đã bán rồi thì đơn hàng cũ thế nào?"**
  May mắn là `chi_tiet_don_hang` đã snapshot `don_gia`, nhưng tên sản phẩm thì mất.
- ⚠️ Nếu xóa vector thất bại → **vector mồ côi** trong Qdrant. Tìm kiếm AI sẽ
  trả về sản phẩm đã xóa, rồi bị lọc mất ở bước join DB (xem `aiSearch` bên dưới) —
  may là có lớp bảo vệ đó.

---

## 5.8. `uploadImage()` — upload ảnh lên Cloudinary

```php
if (!$request->hasFile('image')) {
    return response()->json(['message'=>'No image file provided'], 400);
}
$file         = $request->file('image');
$cloudName    = env('CLOUDINARY_CLOUD_NAME');
$uploadPreset = env('CLOUDINARY_UPLOAD_PRESET');
if (!$cloudName || !$uploadPreset) {
    return response()->json(['message'=>'Cloudinary is not configured...'], 500);
}
```
- Kiểm tra cấu hình trước, báo lỗi rõ ràng cho lập trình viên.
- ⚠️ Gọi `env()` **trực tiếp trong controller**. Khi chạy `php artisan config:cache`
  ở production, `env()` sẽ trả **null** vì Laravel không đọc file `.env` nữa.
  Chuẩn phải là `config('services.cloudinary.cloud_name')`.
  **Đây là lỗi thực tế sẽ làm sập chức năng khi deploy.** Cùng vấn đề với
  `env('PYTHON_SEARCH_URL')` ở các hàm khác.

```php
$response = Http::attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
    ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
        'upload_preset' => $uploadPreset,
    ]);
```
- `Http::attach()` gửi multipart/form-data.
- **Unsigned upload preset** — không cần API secret, Cloudinary tin tưởng preset.
  Đơn giản nhưng ai biết `cloud_name` + `preset` cũng upload được vào tài khoản.

```php
if ($response->successful()) {
    $data = $response->json();
    return response()->json(['status'=>'success','url'=>$data['secure_url']], 200);
}
return response()->json(['status'=>'error','error'=>$response->json()], 500);
```
- Trả về `secure_url` (HTTPS) để frontend lưu vào cột `thumbail`.
- **Vì sao dùng CDN ngoài thay vì lưu server?** Cloudinary tự tối ưu ảnh,
  resize theo thiết bị, phân phối qua CDN toàn cầu → tải nhanh, không tốn ổ cứng server.

> ⚠️ **Điểm yếu bảo mật**: hàm này **không validate file**. Không kiểm tra
> `mimes:jpeg,png,webp`, không giới hạn `max:2048` (KB). Kẻ tấn công upload
> file 500MB hoặc file `.php` (Cloudinary sẽ từ chối, nhưng server đã phải
> đọc `file_get_contents` toàn bộ vào RAM trước). Nên thêm:
> `$request->validate(['image' => 'required|image|mimes:jpeg,png,webp|max:2048']);`
> **Câu hỏi "upload file có an toàn không?" rất hay được hỏi.**

---

## 5.9. `aiSearch()` — tìm kiếm ngữ nghĩa (điểm nhấn của đề tài)

### Kiến trúc luồng

```
Người dùng: "máy tính chơi game giá dưới 20 triệu"
     │
     ▼  POST /api/products/ai-search
Laravel ──HTTP──► Python FastAPI :8001 /search
                       │
                       ├─ LLM tách câu: semantic="máy tính chơi game"
                       │                filters={max_price: 20000000}
                       ├─ Nhúng "máy tính chơi game" thành vector 768 chiều
                       └─ Qdrant tìm 5 vector gần nhất (cosine similarity)
                       │
     ◄──────────────── [{id: 12, score: 0.89}, {id: 5, score: 0.81}, ...]
     │
Laravel: lấy chi tiết sản phẩm từ MySQL, lọc tồn kho, giữ nguyên thứ tự
     ▼
Trả JSON cho frontend
```

### Đọc code

```php
$queryText = $request->input('query');
$branchId  = $request->input('branch_id');
if (!$queryText) { return response()->json(['data' => []]); }
```
- Câu rỗng → trả mảng rỗng ngay, không gọi service (tiết kiệm tài nguyên).

```php
try {
    $pythonServiceUrl = env('PYTHON_SEARCH_URL', 'http://localhost:8001');
    $searchResponse = Http::timeout(15)->post("{$pythonServiceUrl}/search", [
        'query' => $queryText, 'branch_id' => $branchId, 'top_k' => 5,
    ]);
    if (!$searchResponse->successful()) {
        Log::error('[Qdrant] Python service trả về lỗi: '.$searchResponse->body());
        return response()->json(['error'=>'Lỗi kết nối Search Service'], 500);
    }
} catch (\Exception $e) {
    Log::error('[Qdrant] Không kết nối được Python service: '.$e->getMessage());
    return response()->json(['error'=>'Search Service không khả dụng...'], 503);
}
```
- `timeout(15)` dài hơn các call khác (5-10s) vì phải chạy LLM + nhúng vector.
- **Phân biệt 2 loại lỗi** — rất đúng chuẩn:
  - Service **trả lời** nhưng lỗi → **500 Internal Server Error**.
  - Service **không kết nối được** (chết, timeout) → **503 Service Unavailable**.
  503 báo cho client biết "thử lại sau", còn 500 là lỗi logic.
- Thông báo lỗi ra ngoài **không chứa chi tiết exception** (chi tiết chỉ vào log)
  → không rò rỉ đường dẫn nội bộ, phiên bản thư viện. Đúng nguyên tắc bảo mật.
- `top_k = 5` — chỉ lấy 5 kết quả gần nhất.

```php
$searchData = $searchResponse->json();
$results    = $searchData['results'] ?? [];
if (empty($results)) {
    return response()->json([
        'query'    => $queryText,
        'semantic' => $searchData['semantic'] ?? $queryText,
        'filters'  => $searchData['filters']  ?? [],
        'data'     => [],
    ]);
}
```
- Vẫn trả `semantic` và `filters` để frontend hiển thị "Hệ thống hiểu bạn đang tìm:
  *máy tính chơi game*, giá dưới 20 triệu" → **giải thích được cho người dùng**,
  tăng độ tin cậy của tính năng AI.

### Giữ nguyên thứ tự xếp hạng — kỹ thuật đáng chú ý

```php
$orderedIds = array_column($results, 'id');            // [12, 5, 33, 8, 1]
$scoreMap   = array_column($results, 'score', 'id');   // [12=>0.89, 5=>0.81, ...]
```
- `array_column($arr, 'id')` — trích cột `id` thành mảng phẳng.
- `array_column($arr, 'score', 'id')` — dạng 3 tham số tạo **mảng kết hợp**
  lấy `id` làm khóa. Rất gọn.

```php
$dbQuery = san_pham::with(['tonKho' => function ($q) use ($branchId) {
        if ($branchId) { $q->where('ma_chinhanh', $branchId); }
    }])
    ->select('id_sanpham','masp','tensp','gia','thumbail','motasanpham','specifications')
    ->whereIn('id_sanpham', $orderedIds);

if ($branchId) {
    $dbQuery->whereHas('tonKho', function ($q) use ($branchId) {
        $q->where('ma_chinhanh', $branchId)->where('soluongtonkho','>',0);
    });
}
$productsById = $dbQuery->get()->keyBy('id_sanpham');
```
- **`with()` có điều kiện** — chỉ nạp dòng tồn kho của chi nhánh đang xem.
- `whereHas` lọc bỏ sản phẩm hết hàng ở chi nhánh đó.
  → **Đây cũng là lớp bảo vệ trước vector mồ côi**: nếu Qdrant còn vector của
  sản phẩm đã xóa khỏi MySQL thì `whereIn` không tìm thấy → tự loại.
- `keyBy('id_sanpham')` — biến Collection thành mảng có khóa là ID,
  để tra cứu O(1) thay vì duyệt tuyến tính.

```php
$products = collect($orderedIds)
    ->filter(fn($id) => isset($productsById[$id]))
    ->map(function ($id) use ($productsById, $scoreMap) {
        $p = $productsById[$id]->toArray();
        $p['ai_score'] = $scoreMap[$id] ?? 0;
        return $p;
    })
    ->values();
```
- **Bài toán**: `whereIn` trả kết quả theo thứ tự MySQL (thường theo khóa chính),
  **phá vỡ thứ tự xếp hạng độ liên quan** của Qdrant. Sản phẩm khớp nhất
  có thể rơi xuống cuối danh sách.
- **Giải pháp**: duyệt theo `$orderedIds` (thứ tự AI) và tra ngược vào `$productsById`.
  → Kết quả giữ đúng thứ tự độ tương đồng.
  **Đây là chi tiết kỹ thuật hay, nên chủ động trình bày.**
  (Cách khác: dùng `ORDER BY FIELD(id_sanpham, 12,5,33)` trong SQL, nhưng
  không portable và khó đọc hơn.)
- `filter()` loại ID không tìm thấy trong DB.
- `ai_score` — trả điểm tương đồng (0→1) cho frontend hiển thị "độ phù hợp 89%".
- `values()` — đánh lại chỉ số mảng 0,1,2… để `json_encode` ra **mảng JSON**
  chứ không phải object `{"2":..., "5":...}`. Chi tiết nhỏ nhưng quan trọng với frontend.

### `updateProductEmbedding()` — hàm private gửi dữ liệu sang AI

```php
private function updateProductEmbedding(san_pham $product): void
{
    $pythonServiceUrl = env('PYTHON_SEARCH_URL', 'http://localhost:8001');
    $product->load('danhMuc:id_danhmuc,ten_danhmuc');
    Http::timeout(10)->post("{$pythonServiceUrl}/upsert", [
        'id'             => $product->id_sanpham,
        'masp'           => $product->masp,
        'tensp'          => $product->tensp,
        'gia'            => $product->gia,
        'motasanpham'    => $product->motasanpham,
        'ten_danhmuc'    => $product->danhMuc?->ten_danhmuc,
        'specifications' => $product->specifications,
    ]);
}
```
- `private` — chỉ dùng nội bộ, không lộ ra thành endpoint.
- Type hint `san_pham $product` và `: void` — code tường minh.
- `?->` **nullsafe operator** (PHP 8): nếu `danhMuc` là null thì trả null
  thay vì fatal error. Thay cho `$product->danhMuc ? $product->danhMuc->ten_danhmuc : null`.
- Gửi **cả tên danh mục và thông số kỹ thuật** → vector nhúng giàu ngữ cảnh hơn,
  tìm "card đồ họa 8GB" khớp tốt hơn.
- `/upsert` — update nếu đã có, insert nếu chưa. Dùng chung cho cả `store` và `update`.

---

## 5.10. `checkStock($id)` — kiểm tra tồn kho nhanh

```php
if ($id === 'undefined' || !$id) {
    return response()->json(['is_available'=>false,'stock'=>0,'message'=>'ID không hợp lệ'], 200);
}
```
- ⚠️ Kiểm tra chuỗi `'undefined'` — dấu vết của **bug frontend** (JavaScript gửi
  biến undefined thành chuỗi). Đây là "vá tạm ở backend cho lỗi frontend".
  Nếu thầy hỏi, thừa nhận nên sửa ở frontend, hoặc dùng `where('id','[0-9]+')` ở route.

```php
$product = san_pham::with('tonKho')->find($id);
if (!$product) { return response()->json([...], 200); }
$totalStock = $product->tonKho->sum('soluongtonkho');
return response()->json(['is_available'=>$totalStock>0, 'stock'=>$totalStock], 200);
```
- `->sum()` trên Collection — cộng tồn kho **mọi chi nhánh**.
- ⚠️ Trả **200** cho cả trường hợp không tìm thấy sản phẩm (đúng ra 404).
  Có thể là chủ đích để frontend xử lý thống nhất, nhưng không chuẩn REST.
- ⚠️ Không lọc theo `branch_id` như các hàm khác → con số này là tổng toàn hệ thống,
  có thể gây hiểu nhầm "còn hàng" trong khi chi nhánh khách chọn đã hết.

---

## 5.11. Tổng kết & câu hỏi dự kiến

| Câu hỏi | Trả lời |
|---|---|
| N+1 query là gì, em xử lý thế nào? | Dùng `with()` eager loading, `pluck()` thay vòng lặp, `whereIn` gom truy vấn |
| Chống SQL Injection ở phần sắp xếp? | Whitelist bằng `switch`, không nhận trực tiếp tên cột từ client |
| Sao lọc sản phẩm theo chi nhánh? | `whereHas('tonKho')` sinh subquery EXISTS trên bảng `ton_kho_cuc_bo` |
| AI service chết thì sao? | `try/catch` bọc riêng, thao tác CRUD chính vẫn thành công, ghi log để đồng bộ lại sau |
| Sao giữ được thứ tự xếp hạng AI? | Duyệt theo mảng ID do Qdrant trả, tra ngược vào Collection đã `keyBy()` |
| Xóa sản phẩm đã bán thì đơn cũ ra sao? | Hiện là xóa cứng — đây là điểm cần sửa, nên dùng SoftDeletes |

**Điểm yếu tự nhận:**
1. `index()` **không phân trang**, `per_page` ở `byCategory()` **không giới hạn trên** → nguy cơ DoS.
2. Xóa sản phẩm là **xóa cứng**, chưa dùng SoftDeletes → hỏng dữ liệu lịch sử.
3. `uploadImage()` **không validate** kiểu file và dung lượng.
4. Gọi `env()` trực tiếp trong controller → **hỏng khi `config:cache`** ở production.
5. Vòng `while` dựng breadcrumb **không chống chu trình** → nguy cơ treo server.
6. Logic breadcrumb **lặp lại** ở 2 hàm (vi phạm DRY).
7. `index()` chỉ lọc `is_active` cho danh mục gốc, danh mục con đã ẩn vẫn hiện.
8. `checkStock()` không phân biệt chi nhánh và trả 200 cho lỗi 404.
9. Cây danh mục cứng ở **2-3 cấp**, không tổng quát.
