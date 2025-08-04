<?php

return [
    'order_prepared' => [
        'subject' => 'Order #:order_id is being prepared',
        'greeting' => 'Hello!',
        'content' => 'Status update of Order #:order_id. 
                        Your food is being prepared right on! Please kindly wait.',
        'outro' => 'Thank you for your patience.'
    ],
    'order_delivered' => [
        'subject' => 'Order #order_id is delivered',
        'greeting' =>  'Hello!',
        'content' => 'The status of your Order #:order_id has been updated. 
                        The vendor is delivering your food. Please kindly wait.',
        'outro' => 'Thank you for your patience.'
    ],
    'order_arrived' => [
        'subject' => 'Order #:order_id has arrived', 
        'greeting' => 'Hello!',
        'content' => 'Status update of Order #:order_id. Enjoy your food while it\'s hot!',
        'outro' => 'Thank you for your patience. Eat Well!'
    ],
    'customer_subscribed' => [
        'subject' => 'A new order #:order_id has just been placed',
        'greeting' => 'Hello!',
        'order_placed' => 'Someone has just subscribed and placed an order #:order_id.',
        'check_order_invitation' => 'Quick! Check your orders now by clicking on the button below.',
        'view_order' => 'Check Orders',
        'outro' => 'Thank you for partnering with EatWell.'
    ],
    'one_time_password' => [
        'subject' => 'One Time Password Sent',
        'greeting' => 'Hello!',
        'content' => 'We have detected a login attempt onto your account. Please use this OTP to proceed.',
        'otp' => ':otp',
        'warning' => 'If this is not you, please ignore this email',
        'outro' => 'Thank you for using this service'
    ]

];