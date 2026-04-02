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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('description')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('description');       // e.g. fas fa-tshirt
            $table->string('icon_color')->nullable()->after('icon');        // e.g. text-purple-500
            $table->string('icon_bg')->nullable()->after('icon_color');     // e.g. bg-purple-100
            $table->boolean('is_featured')->default(false)->after('icon_bg');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'icon', 'icon_color', 'icon_bg', 'is_featured']);
        });
    }
};
