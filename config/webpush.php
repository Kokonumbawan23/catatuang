<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Push VAPID Keys
    |--------------------------------------------------------------------------
    |
    | Generate your VAPID keys once with:
    | php -r "require 'vendor/autoload.php'; \$v = Minishlink\\WebPush\\VAPID::createVapidKeys(); echo 'VAPID_PUBLIC_KEY=' . \$v['publicKey'] . PHP_EOL . 'VAPID_PRIVATE_KEY=' . \$v['privateKey'] . PHP_EOL;"
    |
    */

    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT'),
    ],

];
