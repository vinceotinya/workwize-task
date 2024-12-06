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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('users');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_time');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'delivered', 'canceled'])->default('waiting')->after('price');
            $table->timestamp('delivery_time')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'delivery_time']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivery_time')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
