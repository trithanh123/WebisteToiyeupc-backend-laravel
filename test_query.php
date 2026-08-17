<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = 'CPU';
$vnTerms = 'Vi xử lý';

$query = App\Models\san_pham::with('danhMuc')
    ->where(function($q) use ($type, $vnTerms) {
        $q->where('specifications->loai', $type)
          ->orWhere(function($subQ) use ($type, $vnTerms) {
              $subQ->where(function($q2) use ($type, $vnTerms) {
                  $q2->where('tensp', 'ILIKE', "%{$type}%");
                  if ($vnTerms) $q2->orWhere('tensp', 'ILIKE', "%{$vnTerms}%");
                  
                  $q2->orWhereHas('danhMuc', function($q3) use ($type, $vnTerms) {
                      $q3->where('ten_danhmuc', 'ILIKE', "%{$type}%");
                      if ($vnTerms) $q3->orWhere('ten_danhmuc', 'ILIKE', "%{$vnTerms}%");
                  });
              })
              ->where('tensp', 'NOT ILIKE', '%Laptop%')
              ->where('tensp', 'NOT ILIKE', 'PC %');
          });
    });

echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n\n";

$results = $query->take(20)->get();
foreach($results as $r) {
    echo $r->id_sanpham . " - " . $r->tensp . " - Category: " . ($r->danhMuc->ten_danhmuc ?? 'N/A') . "\n";
}
