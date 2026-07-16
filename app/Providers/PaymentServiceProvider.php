<?php

namespace App\Providers;

use App\Services\Payment\FakeGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            $key = config('payment.default_gateway', 'fake');
            $map = config('payment.gateways', []);
            $class = $map[$key] ?? FakeGateway::class;

            return $app->make($class);
        });
    }
}
