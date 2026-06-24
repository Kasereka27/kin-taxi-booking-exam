<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->string('currency', 5)->default('CDF')->after('amount');
            $table->text('callback_payload')->nullable()->after('receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_order_number_unique');
            $table->dropColumn(['order_number', 'currency', 'callback_payload']);
        });
    }
};
