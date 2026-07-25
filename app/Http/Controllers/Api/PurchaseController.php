<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Requests\PurchaseCheckoutRequest;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\ThanhToan;       
use App\Models\ThongBao;
use App\Models\khuyen_mai;
use App\Models\ThongBaoKhachHang;
use Illuminate\Support\Facades\Auth;
class PurchaseController extends Controller
{
    public function checkout(PurchaseCheckoutRequest $request)
    {
        try {
            DB::beginTransaction();
            $diaChiId = $request->ma_diachinguoidung;
            $diaChiExists = DB::table('diachi_nguoidung')->where('id_diachinguoidung', $diaChiId)->exists();
            if (!$diaChiExists && $request->has('addressData')) {
                $diaChiId = DB::table('diachi_nguoidung')->insertGetId([
                    'id_nguoidung' => Auth::id(),
                    'ten_nguoinhan' => $request->addressData['ten_nguoinhan'],
                    'sdt_nguoinhan' => $request->addressData['sdt_nguoinhan'],
                    'diachi_chitiet' => $request->addressData['diachi_chitiet'],
                    'matudien_diachi' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ], 'id_diachinguoidung');
            }
            $donHangId = DB::table('don_hang')->insertGetId([
                'ma_nguoidung' => Auth::id(),
                'ma_chinhanh' => $request->ma_chinhanh,
                'ma_khuyenmai' => $request->ma_khuyenmai,
                'ma_diachinguoidung' => $diaChiId,
                'tongtien' => $request->tongtien,
                'phuong_thuc_tt' => $request->phuong_thuc_tt,
                'trang_thai_dh' => 'Chờ duyệt',
                'ghichu' => $request->ghichu,
                'thoigiandathang' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ], 'id_donhang');
            $chiTietData = [];
            foreach ($request->cart_items as $item) {
                $inventory = DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $request->ma_chinhanh)
                    ->where('ma_sanpham', $item['ma_sanpham'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->soluongtonkho < $item['soluong']) {
                    DB::rollBack(); 
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Sản phẩm ID ' . $item['ma_sanpham'] . ' không đủ số lượng trong kho tại chi nhánh này.'
                    ], 400);
                }

               
                DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $request->ma_chinhanh)
                    ->where('ma_sanpham', $item['ma_sanpham'])
                    ->decrement('soluongtonkho', $item['soluong']);

                
                $thanhTien = $item['soluong'] * $item['don_gia'];
                $chiTietData[] = [
                    'ma_donhang' => $donHangId,
                    'ma_sanpham' => $item['ma_sanpham'],
                    'soluong' => $item['soluong'],
                    'don_gia' => $item['don_gia'],
                    'thanh_tien' => $thanhTien,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            DB::table('chi_tiet_don_hang')->insert($chiTietData);
            $trangThaiThanhToan = ($request->phuong_thuc_tt === 'Tiền mặt') ? 'Chưa thanh toán (COD)' : 'Chờ thanh toán VNPay';
            DB::table('thanh_toan')->insert([
                'ma_donhang' => $donHangId,
                'soluong' => count($request->cart_items),
                'phuong_thuc' => $request->phuong_thuc_tt,
                'sotien' => $request->tongtien,
                'trangthai' => $trangThaiThanhToan,
                'ngaythanhtoan' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::commit();
            if ($request->phuong_thuc_tt === 'VNPay') {
                $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = url('/api/vnpay/callback'); 
                $vnp_TmnCode = env('VNPAY_TMN_CODE', 'Z4VUEGIE'); 
                $vnp_HashSecret = env('VNPAY_HASH_SECRET', '2CIKHYUZIVAJM06B4UYULGPA3KZZPLIU'); 
                $vnp_TxnRef = $donHangId . '_' . time(); 
                $vnp_OrderInfo = "Thanh toan don hang #" . $donHangId;
                $vnp_OrderType = 'billpayment';
                $vnp_Amount = $request->tongtien * 100;
                $vnp_Locale = 'vn';
                $vnp_IpAddr = $request->ip();
                $vnp_BankCode = "NCB";
                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $vnp_TxnRef,
                );
                ksort($inputData);
                $query = "";
                $i = 0;
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashdata .= urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }
                $vnp_Url = $vnp_Url . "?" . $query;
                if (isset($vnp_HashSecret)) {
                    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Vui lòng thanh toán',
                    'payment_url' => $vnp_Url
                ]);
            }
           ThongBao::create([
                'loai_thong_bao' => 'ORDER',
                'tieu_de' => 'Đơn hàng mới',
                'noi_dung' => 'Có đơn hàng mới #' . $donHangId . ' vừa được đặt thành công.',
                'link' => '/admin/don-hang'
            ]);
            if ($request->ma_khuyenmai) {
                $voucher = Khuyen_mai::find($request->ma_khuyenmai);
                if ($voucher) {
                    $voucher->increment('dasudung');
                   ThongBao::create([
                        'loai_thong_bao' => 'VOUCHER',
                        'tieu_de' => 'Voucher được sử dụng',
                        'noi_dung' => "Voucher {$voucher->ma_khuyenmai} vừa được dùng cho đơn hàng #{$donHangId}. Đã dùng: {$voucher->dasudung}/{$voucher->soluongma}",
                        'link' => '/admin/voucher' 
                    ]);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Đặt hàng thành công! Vui lòng chờ xác nhận.',
                'order_id' => $donHangId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function vnpayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET_HERE');
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $orderId = explode('_', $request->vnp_TxnRef)[0];
        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                DB::table('thanh_toan')->where('ma_donhang', $orderId)->update([
                    'trangthai' => 'Đã thanh toán',
                    'ma_giaodich' => $request->vnp_TransactionNo,
                    'ngaythanhtoan' => now()
                ]);
                DB::table('don_hang')->where('id_donhang', $orderId)->update([
                    'trang_thai_dh' => 'Đang chuẩn bị'
                ]);
                ThongBao::create([
                    'loai_thong_bao' => 'ORDER',
                    'tieu_de' => 'Đơn hàng VNPay',
                    'noi_dung' => 'Đơn hàng #' . $orderId . ' vừa được thanh toán qua VNPay thành công.',
                    'link' => '/admin/don-hang'
                ]);
                $donHang = DB::table('don_hang')->where('id_donhang', $orderId)->first();
                if ($donHang && $donHang->ma_khuyenmai) {
                    $voucher = khuyen_mai::find($donHang->ma_khuyenmai);
                    if ($voucher) {
                        $voucher->increment('dasudung');
                        ThongBao::create([
                            'loai_thong_bao' => 'VOUCHER',
                            'tieu_de' => 'Voucher được sử dụng',
                            'noi_dung' => "Voucher {$voucher->ma_khuyenmai} vừa được dùng cho đơn hàng #{$orderId} (VNPay). Đã dùng: {$voucher->dasudung}/{$voucher->soluongma}",
                            'link' => '/admin/voucher'
                        ]);
                    }
                }
                return redirect('http://localhost:5173/tai-khoan/don-hang?vnpay=success');
            } else {
                DB::table('thanh_toan')->where('ma_donhang', $orderId)->update([
                    'trangthai' => 'Thất bại'
                ]);
                return redirect('http://localhost:5173/tai-khoan/don-hang?vnpay=error&reason=failed');
            }
        } else {
            return redirect('http://localhost:5173/tai-khoan/don-hang?vnpay=error&reason=invalid_signature');
        }
    }
    public function index(Request $request)
    {
        $query = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->join('chi_nhanh', 'don_hang.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->leftJoin('thanh_toan', 'don_hang.id_donhang', '=', 'thanh_toan.ma_donhang')
            ->select(
                'don_hang.id_donhang',
                'don_hang.tongtien',
                'don_hang.phuong_thuc_tt',
                'don_hang.trang_thai_dh',
                'don_hang.thoigiandathang',
                'nguoi_dung.ten as tenkhachhang',
                'nguoi_dung.sdt',
                'chi_nhanh.ten_chinhanh',
                'thanh_toan.trangthai as trangthaithanhtoan',
                'thanh_toan.ma_giaodich'
            );
        if ($request->has('status')) {
            $query->where('don_hang.trang_thai_dh', $request->status);
        }
        $orders = $query->orderBy('don_hang.id_donhang', 'desc')->paginate(15);
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
    public function show(int $id)
    {
        $order = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->join('diachi_nguoidung', 'don_hang.ma_diachinguoidung', '=', 'diachi_nguoidung.id_diachinguoidung')
            ->join('chi_nhanh', 'don_hang.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->leftJoin('khuyen_mai', 'don_hang.ma_khuyenmai', '=', 'khuyen_mai.id_khuyenmai')
            ->select(
                'don_hang.*', 
                'nguoi_dung.ten as tenkhach', 
                'nguoi_dung.email', 
                'diachi_nguoidung.ten_nguoinhan', 
                'diachi_nguoidung.sdt_nguoinhan', 
                'diachi_nguoidung.diachi_chitiet',
                'chi_nhanh.ten_chinhanh',
                'khuyen_mai.ma_khuyenmai as magiamgia'
            )
            ->where('don_hang.id_donhang', $id)
            ->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }
        $details = DB::table('chi_tiet_don_hang')
            ->join('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->select('chi_tiet_don_hang.*', 'san_pham.tensp', 'san_pham.thumbail')
            ->where('chi_tiet_don_hang.ma_donhang', $id)
            ->get();
        $payment = DB::table('thanh_toan')->where('ma_donhang', $id)->first();
        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'items' => $details,
                'payment' => $payment
            ]
        ]);
    }
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'trang_thai_dh' => 'required|string'
        ]);
        $order = DB::table('don_hang')->where('id_donhang', $id)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }
        DB::table('don_hang')->where('id_donhang', $id)->update([
            'trang_thai_dh' => $request->trang_thai_dh,
            'updated_at' => now()
        ]);
        ThongBao::create([
            'loai_thong_bao' => 'ORDER',
            'tieu_de' => 'Cập nhật đơn hàng',
            'noi_dung' => 'Đơn hàng #' . $id . ' vừa được chuyển sang trạng thái: ' . $request->trang_thai_dh,
            'link' => '/admin/don-hang/' . $id
        ]);
        
        
        ThongBaoKhachHang::create([
            'id_nguoidung' => $order->ma_nguoidung,
            'loai_thong_bao' => 'don_hang',
            'tieu_de' => 'Cập nhật đơn hàng',
            'noi_dung' => 'Đơn hàng #' . $id . ' của bạn đã được cập nhật trạng thái thành: ' . $request->trang_thai_dh,
            'link' => '/tai-khoan/don-hang'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã cập nhật trạng thái đơn hàng thành: ' . $request->trang_thai_dh
        ]);
    }
    public function monitorOrders()
    {
        $orders = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->join('chi_nhanh', 'don_hang.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->leftJoin('thanh_toan', 'don_hang.id_donhang', '=', 'thanh_toan.ma_donhang')
            ->select(
                'don_hang.id_donhang',
                'don_hang.tongtien',
                'don_hang.phuong_thuc_tt',
                'don_hang.trang_thai_dh',
                'don_hang.thoigiandathang',
                'nguoi_dung.ten as tenkhachhang',
                'nguoi_dung.sdt',
                'chi_nhanh.ten_chinhanh',
                'thanh_toan.trangthai as trangthaithanhtoan',
                'thanh_toan.ma_giaodich'
            )
            ->orderBy('don_hang.thoigiandathang', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
    public function handleEmergency()
    {
        $twoHoursAgo = Carbon::now()->subHours(2);
        $emergencyOrders = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->leftJoin('thanh_toan', 'don_hang.id_donhang', '=', 'thanh_toan.ma_donhang')
            ->select(
                'don_hang.id_donhang',
                'don_hang.trang_thai_dh',
                'don_hang.thoigiandathang',
                'nguoi_dung.ten as tenkhachhang',
                'thanh_toan.trangthai as trangthaithanhtoan'
            )
            ->where('don_hang.thoigiandathang', '<=', $twoHoursAgo)
            ->whereIn('don_hang.trang_thai_dh', ['Chờ duyệt'])
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => count($emergencyOrders) > 0 ? 'Có đơn hàng cần xử lý gấp!' : 'Không có sự cố khẩn cấp.',
            'data' => $emergencyOrders
        ]);
    }
    public function printInvoice(int $id)
    {
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            return response()->json(['status' => 'error', 'message' => 'Chưa cài đặt thư viện PDF'], 500);
        }
        $order = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->join('diachi_nguoidung', 'don_hang.ma_diachinguoidung', '=', 'diachi_nguoidung.id_diachinguoidung')
            ->select('don_hang.*', 'nguoi_dung.ten as tenkhach', 'nguoi_dung.sdt', 'diachi_nguoidung.diachi_chitiet')
            ->where('id_donhang', $id)
            ->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }
        $details = DB::table('chi_tiet_don_hang')
            ->join('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->select('chi_tiet_don_hang.*', 'san_pham.tensp')
            ->where('ma_donhang', $id)
            ->get();
        $data = [
            'order' => $order,
            'details' => $details,
            'date' => date('d/m/Y')
        ];
        $html = '<div style="font-family: DejaVu Sans, sans-serif;">';
        $html .= '<h2 style="text-align:center">HÓA ĐƠN MUA HÀNG</h2>';
        $html .= '<p><strong>Mã ĐH:</strong> #' . $order->id_donhang . '</p>';
        $html .= '<p><strong>Khách hàng:</strong> ' . $order->tenkhach . ' - SĐT: ' . $order->sdt . '</p>';
        $html .= '<p><strong>Địa chỉ:</strong> ' . $order->diachi_chitiet . '</p>';
        $html .= '<p><strong>Ngày đặt:</strong> ' . $order->thoigiandathang . '</p>';
        $html .= '<hr>';
        $html .= '<table style="width:100%; border-collapse: collapse;" border="1">';
        $html .= '<tr><th>Tên SP</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>';
        foreach($details as $item) {
            $html .= '<tr>';
            $html .= '<td>' . $item->tensp . '</td>';
            $html .= '<td style="text-align:center">' . $item->soluong . '</td>';
            $html .= '<td style="text-align:right">' . number_format($item->don_gia) . 'đ</td>';
            $html .= '<td style="text-align:right">' . number_format($item->thanh_tien) . 'đ</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<h3 style="text-align:right">Tổng cộng: ' . number_format($order->tongtien) . ' VNĐ</h3>';
        $html .= '</div>';
        $pdf = Pdf::loadHTML($html);
        return $pdf->stream('hoadon_' . $order->id_donhang . '.pdf');
    }
    public function allocateOrder(Request $request, int $id)
    {
        $request->validate([
            'ma_chinhanhMoi' => 'required|integer'
        ]);
        $order = DB::table('don_hang')->where('id_donhang', $id)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }
        DB::table('don_hang')->where('id_donhang', $id)->update([
            'ma_chinhanh' => $request->ma_chinhanhMoi,
            'updated_at' => now()
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Đã điều chuyển đơn hàng sang chi nhánh mới thành công!'
        ]);
    }
    public function myOrders(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        $orders = DB::table('don_hang')
            ->leftJoin('chi_tiet_don_hang', 'don_hang.id_donhang', '=', 'chi_tiet_don_hang.ma_donhang')
            ->leftJoin('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->select(
                'don_hang.*',
                'san_pham.tensp',
                'san_pham.thumbail as hinhanh'
            )
            ->where('don_hang.ma_nguoidung', $user->id_nguoidung)
            ->orderBy('don_hang.thoigiandathang', 'desc')
            ->get();
        $grouped = [];
        foreach ($orders as $row) {
            if (!isset($grouped[$row->id_donhang])) {
                $grouped[$row->id_donhang] = [
                    'id_donhang' => $row->id_donhang,
                    'tongtien' => $row->tongtien,
                    'trang_thai_dh' => $row->trang_thai_dh,
                    'thoigiandathang' => $row->thoigiandathang,
                    'items' => []
                ];
            }
            if ($row->tensp) {
                $grouped[$row->id_donhang]['items'][] = [
                    'tensp' => $row->tensp,
                    'hinhanh' => $row->hinhanh
                ];
            }
        }
        return response()->json([
            'status' => 'success',
            'data' => array_values($grouped)
        ]);
    }
    public function cancelOrder(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        $order = DB::table('don_hang')
            ->where('id_donhang', $id)
            ->where('ma_nguoidung', $user->id_nguoidung)
            ->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng của bạn'], 404);
        }
        if ($order->trang_thai_dh !== 'Chờ duyệt' && $order->trang_thai_dh !== 'Chờ xác nhận') {
            return response()->json(['status' => 'error', 'message' => 'Chỉ có thể hủy đơn hàng khi đang chờ duyệt'], 400);
        }
        DB::table('don_hang')->where('id_donhang', $id)->update([
            'trang_thai_dh' => 'Đã hủy',
            'updated_at'    => now()
        ]);

        // ── Hoàn lại tồn kho khi hủy đơn ──────────────────────────────────
        $chiTietDonHang = DB::table('chi_tiet_don_hang')
            ->where('ma_donhang', $id)
            ->get();

        foreach ($chiTietDonHang as $item) {
            DB::table('ton_kho_cuc_bo')
                ->where('ma_chinhanh', $order->ma_chinhanh)
                ->where('ma_sanpham',  $item->ma_sanpham)
                ->increment('soluongtonkho', $item->soluong);
        }
        // ───────────────────────────────────────────────────────────────────

        ThongBao::create([
            'loai_thong_bao' => 'ORDER',
            'tieu_de'        => 'Khách hàng hủy đơn',
            'noi_dung'       => 'Khách hàng vừa hủy đơn hàng #' . $id . '. Tồn kho chi nhánh đã được hoàn lại.',
            'link'           => '/admin/don-hang/' . $id
        ]);
        return response()->json([
            'status'  => 'success',
            'message' => 'Đã hủy đơn hàng thành công. Tồn kho đã được hoàn lại.'
        ]);
    }
}
