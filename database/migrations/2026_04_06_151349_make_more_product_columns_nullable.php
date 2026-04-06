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
            $table->string('subcategory')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->json('colors')->nullable()->change();
            $table->json('sizes')->nullable()->change();
            $table->json('tags')->nullable()->change();
            $table->string('color1')->nullable()->change();
            $table->string('color2')->nullable()->change();
            $table->string('icon_class')->nullable()->change();
            $table->string('icon_color')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('subcategory')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->json('colors')->nullable(false)->change();
            $table->json('sizes')->nullable(false)->change();
            $table->json('tags')->nullable(false)->change();
            $table->string('color1')->nullable(false)->change();
            $table->string('color2')->nullable(false)->change();
            $table->string('icon_class')->nullable(false)->change();
            $table->string('icon_color')->nullable(false)->change();
        });
    }
};
