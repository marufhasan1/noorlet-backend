<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->enum('seller_status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending')->after('seller_id');
        });
    }
    public function down(): void {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'seller_id');
            $table->dropColumn(['seller_id', 'seller_status']);
        });
    }
};
