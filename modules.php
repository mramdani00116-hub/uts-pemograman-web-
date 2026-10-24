<?php
return [
    'customers' => [
        'title' => 'Pelanggan', 'singular' => 'Pelanggan', 'icon' => '♙', 'table' => 'customers',
        'fields' => [
            'name' => ['label'=>'Nama lengkap','type'=>'text','required'=>true],
            'phone' => ['label'=>'Nomor telepon','type'=>'text','required'=>true],
            'email' => ['label'=>'Email','type'=>'email'],
            'address' => ['label'=>'Alamat','type'=>'textarea'],
        ],
        'list_sql' => 'SELECT id,name,phone,email,address,created_at FROM customers ORDER BY id DESC',
        'columns' => ['name'=>'Nama pelanggan','phone'=>'Telepon','email'=>'Email','address'=>'Alamat','created_at'=>'Terdaftar'],
        'search' => ['name','phone','email']
    ],
    'vehicles' => [
        'title' => 'Kendaraan', 'singular' => 'Kendaraan', 'icon' => '🚘', 'table' => 'vehicles',
        'fields' => [
            'customer_id' => ['label'=>'Pemilik','type'=>'select','required'=>true,'options_sql'=>'SELECT id,name FROM customers ORDER BY name','option_value'=>'id','option_label'=>'name'],
            'plate_number' => ['label'=>'Nomor polisi','type'=>'text','required'=>true],
            'brand' => ['label'=>'Merek','type'=>'text','required'=>true],
            'model' => ['label'=>'Model / tipe','type'=>'text','required'=>true],
            'year' => ['label'=>'Tahun','type'=>'number','min'=>1950,'max'=>2100],
            'color' => ['label'=>'Warna','type'=>'text'],
        ],
        'list_sql' => 'SELECT v.id,c.name AS customer_name,v.plate_number,v.brand,v.model,v.year,v.color FROM vehicles v LEFT JOIN customers c ON c.id=v.customer_id ORDER BY v.id DESC',
        'columns' => ['customer_name'=>'Pemilik','plate_number'=>'Nomor polisi','brand'=>'Merek','model'=>'Model','year'=>'Tahun','color'=>'Warna'],
        'search' => ['v.plate_number','v.brand','v.model','c.name']
    ],
    'services' => [
        'title' => 'Katalog servis', 'singular' => 'Servis', 'icon' => '🔧', 'table' => 'services',
        'fields' => [
            'name' => ['label'=>'Nama servis','type'=>'text','required'=>true],
            'description' => ['label'=>'Deskripsi','type'=>'textarea'],
            'price' => ['label'=>'Harga (Rp)','type'=>'number','required'=>true,'min'=>0,'step'=>'1000'],
            'duration_minutes' => ['label'=>'Estimasi durasi (menit)','type'=>'number','min'=>0],
            'is_active' => ['label'=>'Status','type'=>'select','options'=>['1'=>'Aktif','0'=>'Nonaktif'],'default'=>'1'],
        ],
        'list_sql' => 'SELECT id,name,description,price,duration_minutes,is_active FROM services ORDER BY id DESC',
        'columns' => ['name'=>'Nama servis','description'=>'Deskripsi','price'=>'Harga','duration_minutes'=>'Durasi (menit)','is_active'=>'Status'],
        'search' => ['name','description'], 'money' => ['price'], 'boolean' => ['is_active']
    ],
    'bookings' => [
        'title' => 'Booking servis', 'singular' => 'Booking', 'icon' => '▦', 'table' => 'bookings',
        'fields' => [
            'customer_id' => ['label'=>'Pelanggan','type'=>'select','required'=>true,'options_sql'=>'SELECT id,name FROM customers ORDER BY name','option_value'=>'id','option_label'=>'name'],
            'vehicle_id' => ['label'=>'Kendaraan','type'=>'select','required'=>true,'options_sql'=>"SELECT v.id,CONCAT(v.plate_number,' — ',v.brand,' ',v.model) AS label FROM vehicles v ORDER BY v.plate_number",'option_value'=>'id','option_label'=>'label'],
            'service_id' => ['label'=>'Jenis servis','type'=>'select','required'=>true,'options_sql'=>'SELECT id,name FROM services WHERE is_active=1 ORDER BY name','option_value'=>'id','option_label'=>'name'],
            'booking_date' => ['label'=>'Tanggal booking','type'=>'date','required'=>true],
            'booking_time' => ['label'=>'Jam booking','type'=>'time','required'=>true],
            'complaint' => ['label'=>'Keluhan pelanggan','type'=>'textarea','required'=>true],
            'status' => ['label'=>'Status','type'=>'select','required'=>true,'options'=>['Baru'=>'Baru','Dikonfirmasi'=>'Dikonfirmasi','Diproses'=>'Diproses','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'],'default'=>'Baru'],
            'notes' => ['label'=>'Catatan','type'=>'textarea'],
        ],
        'list_sql' => 'SELECT b.id,c.name AS customer_name,v.plate_number,s.name AS service_name,b.booking_date,b.booking_time,b.status,b.complaint FROM bookings b LEFT JOIN customers c ON c.id=b.customer_id LEFT JOIN vehicles v ON v.id=b.vehicle_id LEFT JOIN services s ON s.id=b.service_id ORDER BY b.booking_date DESC,b.booking_time DESC',
        'columns' => ['customer_name'=>'Pelanggan','plate_number'=>'Kendaraan','service_name'=>'Servis','booking_date'=>'Tanggal','booking_time'=>'Jam','status'=>'Status','complaint'=>'Keluhan'],
        'search' => ['c.name','v.plate_number','s.name','b.status','b.complaint']
    ],
    'spare_parts' => [
        'title' => 'Stok suku cadang', 'singular' => 'Suku cadang', 'icon' => '▤', 'table' => 'spare_parts',
        'fields' => [
            'sku' => ['label'=>'Kode / SKU','type'=>'text','required'=>true],
            'name' => ['label'=>'Nama suku cadang','type'=>'text','required'=>true],
            'category' => ['label'=>'Kategori','type'=>'text'],
            'stock_qty' => ['label'=>'Jumlah stok','type'=>'number','required'=>true,'min'=>0,'default'=>'0'],
            'unit_price' => ['label'=>'Harga satuan (Rp)','type'=>'number','required'=>true,'min'=>0,'step'=>'1000'],
            'min_stock' => ['label'=>'Batas minimum stok','type'=>'number','min'=>0,'default'=>'3'],
        ],
        'list_sql' => 'SELECT id,sku,name,category,stock_qty,unit_price,min_stock FROM spare_parts ORDER BY name',
        'columns' => ['sku'=>'SKU','name'=>'Nama barang','category'=>'Kategori','stock_qty'=>'Stok','unit_price'=>'Harga satuan','min_stock'=>'Min. stok'],
        'search' => ['sku','name','category'], 'money' => ['unit_price']
    ],
    'invoices' => [
        'title' => 'Tagihan & pembayaran', 'singular' => 'Tagihan', 'icon' => '▧', 'table' => 'invoices',
        'fields' => [
            'invoice_number' => ['label'=>'Nomor invoice','type'=>'text','required'=>true],
            'customer_id' => ['label'=>'Pelanggan','type'=>'select','required'=>true,'options_sql'=>'SELECT id,name FROM customers ORDER BY name','option_value'=>'id','option_label'=>'name'],
            'subtotal' => ['label'=>'Subtotal (Rp)','type'=>'number','required'=>true,'min'=>0,'default'=>'0'],
            'discount' => ['label'=>'Diskon (Rp)','type'=>'number','min'=>0,'default'=>'0'],
            'tax' => ['label'=>'Pajak (Rp)','type'=>'number','min'=>0,'default'=>'0'],
            'total' => ['label'=>'Total tagihan (Rp)','type'=>'number','required'=>true,'min'=>0,'default'=>'0'],
            'paid_amount' => ['label'=>'Sudah dibayar (Rp)','type'=>'number','min'=>0,'default'=>'0'],
            'status' => ['label'=>'Status pembayaran','type'=>'select','required'=>true,'options'=>['Belum Lunas'=>'Belum Lunas','Sebagian'=>'Sebagian','Lunas'=>'Lunas'],'default'=>'Belum Lunas'],
            'due_date' => ['label'=>'Jatuh tempo','type'=>'date'],
            'issued_at' => ['label'=>'Tanggal terbit','type'=>'date','default'=>date('Y-m-d')],
        ],
        'list_sql' => 'SELECT i.id,i.invoice_number,c.name AS customer_name,i.total,i.paid_amount,i.status,i.issued_at,i.due_date FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id ORDER BY i.id DESC',
        'columns' => ['invoice_number'=>'Nomor invoice','customer_name'=>'Pelanggan','total'=>'Total','paid_amount'=>'Dibayar','status'=>'Status','issued_at'=>'Tanggal terbit','due_date'=>'Jatuh tempo'],
        'search' => ['i.invoice_number','c.name','i.status'], 'money' => ['total','paid_amount']
    ],
];
