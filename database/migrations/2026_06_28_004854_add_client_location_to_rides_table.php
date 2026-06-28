<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->decimal('client_lat', 10, 7)->nullable()->after('dropoff_lng');
            $table->decimal('client_lng', 10, 7)->nullable()->after('client_lat');
            $table->timestamp('client_location_updated_at')->nullable()->after('client_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['client_lat', 'client_lng', 'client_location_updated_at']);
        });
    }
};
