<?php

return [
    'order_prepared' => [
        'subject' => 'Pesanan #:order_id sedang dibuat',
        'greeting' => 'Halo!',
        'content' => 'Perubahan status Pesanan #:order_id. 
                        Makanan anda sedang dipersiapkan. Mohon kesediaannya untuk menunggu.',
        'outro' => 'Terima kasih karena telah menunggu.'
    ],
    'order_delivered' => [
        'subject' => 'Pesanan #:order_id sedang dikirim',
        'greeting' =>  'Halo!',
        'content' => 'Perubahan status Pesanan #:order_id. 
                        Vendor sedang mengirim makanan anda. Mohon kesediannya untuk menunggu',
        'outro' => 'Terima kasih karena telah menunggu.'
    ],
    'order_arrived' => [
        'subject' => 'Pesanan #:order_id telah sampai', 
        'greeting' => 'Halo!',
        'content' => 'Perubahan status Pesanan #:order_id. Selamat menikmati makanan anda!',
        'outro' => 'Terima kasih karena telah menunggu.'
    ],
    'customer_subscribed' => [
        'subject' => 'Pesanan #:order_id baru muncul',
        'greeting' => 'Halo!',
        'order_placed' => 'Seseorang baru saja membeli Pesanan #:order_id.',
        'check_order_invitation' => 'Segera periksa pesanan kamu sekarang dengan memencet tombol dibawah ini!',
        'view_order' => 'Periksa Pesanan',
        'outro' => 'Terima kasih atas kerja samanya dengan EatWell.'
    ]
];