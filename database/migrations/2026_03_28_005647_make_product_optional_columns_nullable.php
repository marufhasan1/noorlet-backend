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
            $table->json('details')->nullable()->change();
            $table->string('care')->nullable()->change();
            $table->string('fit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('details')->nullable(false)->change();
            $table->string('care')->nullable(false)->change();
            $table->string('fit')->nullable(false)->change();
        });
    }
};
