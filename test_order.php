<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $id = 39;
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
            'khuyen_mai.ma_voucher as magiamgia'
        )
        ->where('don_hang.id_donhang', $id)
        ->first();

    if (!$order) {
        echo "Order not found\n";
    } else {
        echo "Order found\n";
    }

    $details = DB::table('chi_tiet_don_hang')
        ->join('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
        ->select('chi_tiet_don_hang.*', 'san_pham.tensp', 'san_pham.thumbail')
        ->where('chi_tiet_don_hang.ma_donhang', $id)
        ->get();

    foreach ($details as $item) {
        $khoton = DB::table('ton_kho_cuc_bo')
            ->where('ma_sanpham', $item->ma_sanpham)
            ->where('ma_chinhanh', $order->ma_chinhanh)
            ->first();
            
        if ($khoton) {
            $item->available_serials = DB::table('sanpham_serials')
                ->where('ma_tonkho', $khoton->id_khoton)
                ->whereIn('tinhtrang', ['nằm trong kho', 'trong kho'])
                ->select('id_serial', 'serial_code')
                ->get();
        } else {
            $item->available_serials = [];
        }
    }
    echo "Success\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
