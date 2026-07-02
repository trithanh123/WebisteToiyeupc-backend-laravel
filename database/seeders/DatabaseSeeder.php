<?php
namespace Database\Seeders;
use App\Models\Nguoi_dung;
use App\Models\chi_nhanh;
use App\Models\khuyen_mai;
use App\Models\san_pham;
use App\Models\nhan_vien;
use App\Models\diachi_nguoidung;
use App\Models\danh_gia;
use App\Models\lien_he;
use App\Models\thong_bao;
use App\Models\chi_tiet_don_hang;
use App\Models\thanh_toan;
use App\Models\don_hang;
use App\Models\ThongBao;
use App\Models\ton_kho_cuc_bo;
use App\Models\sanpham_serials;
use App\Models\danh_muc;
use Database\Factories\DanhMucFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        DanhMucFactory::taoFullTree();
        $chinhanhList = chi_nhanh::factory(5)->create();
        Nguoi_dung::factory()->laBoss()->create([  
            'email' => 'admin@toiyeupc.com',
            'matkhau' => Hash::make('Thanh0941061704@'),
            'phanquyen' =>1,
            'sdt'=>'0941061704',
            'ten'=>'Phạm Trí Thành'
        ]);
        Nguoi_dung::factory(5)->laNhanVien()->create();     
        Nguoi_dung::factory(5)->laNhaCungCap()->create();   
        Nguoi_dung::factory(20)->create();                  
        $chinhanhList->each(function ($chiNhanh) {
            nhan_vien::factory(2)->taiBranch($chiNhanh->id_chinhanh)->create();
        });
        khuyen_mai::factory(10)->create();                         
        khuyen_mai::factory(3)->giamPhanTram(15, 300000)->create(); 
        khuyen_mai::factory(2)->vip()->create();                   
        $dmLaptop    = danh_muc::where('slug', 'like', '%laptop%')->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmPC        = danh_muc::where('slug', 'like', '%pc-toiyeupc%')->orWhere('slug', 'like', '%pc%')->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmManHinh   = danh_muc::where('slug', 'like', '%man-hinh%')->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmLinhKien  = danh_muc::where('slug', 'like', '%o-cung%')->orWhere('slug', 'like', '%ram%')->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmPhuKien   = danh_muc::where('slug', 'like', '%chuot%')->orWhere('slug', 'like', '%ban-phim%')->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        if ($dmPC->isNotEmpty()) {
            san_pham::factory(50)->laPC()->state(function () use ($dmPC) {
                return ['ma_danhmuc' => $dmPC->random()];
            })->create();
        }
        if ($dmLaptop->isNotEmpty()) {
            san_pham::factory(20)->state(function () use ($dmLaptop) {
                return ['ma_danhmuc' => $dmLaptop->random()];
            })->create();
            san_pham::factory(10)->laLaptopGaming()->state(function () use ($dmLaptop) {
                return ['ma_danhmuc' => $dmLaptop->random()];
            })->create();
        }
        if ($dmManHinh->isNotEmpty()) {
            san_pham::factory(20)->laManHinh()->state(function () use ($dmManHinh) {
                return ['ma_danhmuc' => $dmManHinh->random()];
            })->create();
        }
        if ($dmLinhKien->isNotEmpty()) {
            san_pham::factory(30)->laLinhKien()->state(function () use ($dmLinhKien) {
                return ['ma_danhmuc' => $dmLinhKien->random()];
            })->create();
        }
        if ($dmPhuKien->isNotEmpty()) {
            san_pham::factory(30)->laPhuKien()->state(function () use ($dmPhuKien) {
                return ['ma_danhmuc' => $dmPhuKien->random()];
            })->create();
        }
        $khachHangs = Nguoi_dung::where('phanquyen', 0)->get();
        $khachHangs->each(function ($user) {
            diachi_nguoidung::factory(2)->create([
                'id_nguoidung' => $user->id_nguoidung
            ]);
        });
        $sanPhams = san_pham::all();
        $chiNhanhs = chi_nhanh::all();
        $sanPhams->random(15)->each(function ($sp) use ($chiNhanhs) {
            $chiNhanhs->each(function ($cn) use ($sp) {
                $tonKho = ton_kho_cuc_bo::factory()->create([
                    'ma_sanpham' => $sp->id_sanpham,
                    'ma_chinhanh' => $cn->id_chinhanh,
                ]);
                if ($tonKho->soluongtonkho > 0) {
                    sanpham_serials::factory($tonKho->soluongtonkho)->create([
                        'ma_tonkho' => $tonKho->id_khoton
                    ]);
                }
            });
        });
        $khuyenMais = khuyen_mai::all();
        $khachHangs->random(10)->each(function ($user) use ($chiNhanhs, $khuyenMais, $sanPhams) {
            $diaChi = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)->first();
            $chiNhanh = $chiNhanhs->random();
            $voucher = rand(0, 1) ? $khuyenMais->random() : null;
            $donHang = don_hang::factory()->create([
                'ma_nguoidung' => $user->id_nguoidung,
                'ma_chinhanh' => $chiNhanh->id_chinhanh,
                'ma_khuyenmai' => $voucher ? $voucher->id_khuyenmai : null,
                'ma_diachinguoidung' => $diaChi ? $diaChi->id_diachinguoidung : null,
            ]);
            $spMua = $sanPhams->random(rand(1, 3));
            $spMua->each(function ($sp) use ($donHang) {
               chi_tiet_don_hang::factory()->create([
                    'ma_donhang' => $donHang->id_donhang,
                    'ma_sanpham' => $sp->id_sanpham,
                ]);
            });
            thanh_toan::factory()->create([
                'ma_donhang' => $donHang->id_donhang,
                'sotien' => $donHang->tongtien, 
            ]);
        });
        $sanPhams->random(10)->each(function ($sp) use ($khachHangs) {
            danh_gia::factory(rand(1, 3))->create([
                'ma_sanpham' => $sp->id_sanpham,
                'ma_nguoidung' => $khachHangs->random()->id_nguoidung,
            ]);
        });
       lien_he::factory(5)->create([
            'ma_nguoidung' => $khachHangs->random()->id_nguoidung,
        ]);
        ThongBao::factory(10)->create();
    }
}
