# GIẢI THÍCH CODE BACKEND — TOIYEUPC

Tài liệu giải thích chi tiết từng dòng code trong thư mục `app/` của backend Laravel,
phục vụ bảo vệ / phản biện luận văn.

## Mục lục

| File | Nội dung |
|------|----------|
| [01-tong-quan-kien-truc.md](01-tong-quan-kien-truc.md) | Laravel 11 skeleton, `bootstrap/app.php`, `routes/api.php`, Middleware, AppServiceProvider, Mail |
| [02-models.md](02-models.md) | 25 Model Eloquent — bảng, khóa chính, `$fillable`, `$casts`, quan hệ |
| [03-form-requests.md](03-form-requests.md) | 33 lớp FormRequest — tầng validation |
| [04-auth-bao-mat.md](04-auth-bao-mat.md) | AuthController, PasswordResetController, Sanctum, OTP, Google OAuth |
| [05-crud-danh-muc-san-pham.md](05-crud-danh-muc-san-pham.md) | CategoryController, ProductController (kể cả AI search) |
| [06-crud-con-lai.md](06-crud-con-lai.md) | User, Branch, Personnel, Voucher, Address, Wishlist |
| [07-dat-hang-thanh-toan.md](07-dat-hang-thanh-toan.md) | PurchaseController — 530 dòng, phần lõi nghiệp vụ |
| [08-kho-va-serial.md](08-kho-va-serial.md) | WarehouseController, WarehouseReceiptController, StockStaffController |
| [09-dieu-chuyen-kho.md](09-dieu-chuyen-kho.md) | AdminTransferController, StaffTransferController |
| [10-bao-hanh-thongbao-thongke.md](10-bao-hanh-thongbao-thongke.md) | SupportWarranty, Notification, ClientNotification, Statistical, StaffDashboard, OrderStaff |
| [11-ai-goi-y-vector.md](11-ai-goi-y-vector.md) | GenerateProductVectors + Qdrant + Python service |
| [12-cau-hoi-phan-bien.md](12-cau-hoi-phan-bien.md) | Bộ câu hỏi thầy hay hỏi + câu trả lời + điểm yếu tự nhận |

## Cách dùng khi đi phản biện

1. Đọc **01** và **02** để nắm bức tranh tổng thể — thầy thường hỏi "kiến trúc hệ thống của em thế nào?"
2. Học kỹ **07**, **08**, **09** — đây là phần nghiệp vụ đặc thù (serial, tồn kho đa chi nhánh), thầy sẽ xoáy vào.
3. Đọc **12** trước khi vào phòng — đó là phần tổng hợp nhanh nhất.
