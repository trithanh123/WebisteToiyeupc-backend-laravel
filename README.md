# ToiYeuPC - Backend Service (Laravel)

<div align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
</div>

## 📌 Giới thiệu dự án
Đây là hệ thống Backend API được xây dựng bằng Laravel cho **ToiYeuPC** - nền tảng thương mại điện tử chuyên cung cấp linh kiện máy tính và dịch vụ Build PC. 

Hệ thống backend này đóng vai trò xử lý các nghiệp vụ cốt lõi, quản lý cơ sở dữ liệu và cung cấp RESTful API cho ứng dụng Frontend (React). Điểm nổi bật của dự án là khả năng giao tiếp với các microservice AI (Python, FastAPI, Qdrant) để mang lại trải nghiệm tìm kiếm ngữ nghĩa thông minh và gợi ý cấu hình tối ưu cho người dùng.

## 🚀 Tính năng nổi bật
* **Quản lý sản phẩm & danh mục:** CRUD các linh kiện PC (Mainboard, CPU, VGA, RAM,...).
* **Gợi ý cấu hình PC:** Xử lý logic kiểm tra độ tương thích giữa các linh kiện máy tính.
* **Tích hợp AI Search:** Kết nối với Backend AI (SBERT/Qdrant) để thực hiện tìm kiếm ngữ nghĩa (Semantic Search).
* **Quản lý đơn hàng & Giỏ hàng:** Xử lý quy trình đặt hàng, thanh toán và theo dõi trạng thái đơn.
* **Xác thực & Phân quyền:** Đăng nhập, đăng ký và quản lý phiên người dùng/admin bảo mật.

## 🛠️ Công nghệ sử dụng
* **Framework:** [Laravel](https://laravel.com/)
* **Ngôn ngữ:** PHP
* **Cơ sở dữ liệu: PostgreSQL
* **Giao tiếp service khác:** RESTful API (kết nối với service Python FastAPI)

## ⚙️ Yêu cầu hệ thống
* PHP >= 8.1
* Composer
* MySQL hoặc PostgreSQL
* (Tùy chọn) Python & FastAPI service đang chạy cho tính năng AI Search.

## 💻 Hướng dẫn cài đặt

1. **Clone repository về máy:**
   ```bash
   git clone [https://github.com/trithanh123/WebisteToiyeupc-backend-laravel.git](https://github.com/trithanh123/WebisteToiyeupc-backend-laravel.git)
   cd WebisteToiyeupc-backend-laravel
