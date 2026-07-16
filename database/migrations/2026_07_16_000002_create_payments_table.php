<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('gateway')->default('fake');
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('status')->default('paid');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->nullable()->after('plan_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
        });

        Schema::dropIfExists('payments');
    }
};
