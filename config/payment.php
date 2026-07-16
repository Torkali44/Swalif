<?php

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'fake'),
    'gateways' => [
        'fake' => App\Services\Payment\FakeGateway::class,
        'stripe' => App\Services\Payment\StripeGateway::class,
        'tap' => App\Services\Payment\TapGateway::class,
    ],
];
