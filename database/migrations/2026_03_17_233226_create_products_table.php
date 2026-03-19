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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('subcategory');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('badge')->nullable();
            $table->text('description');
            $table->json('details');
            $table->string('care');
            $table->string('fit');
            $table->json('colors');
            $table->json('sizes');
            $table->json('tags');
            $table->json('related_ids')->nullable();
            $table->string('color1');
            $table->string('color2');
            $table->string('icon_class');
            $table->string('icon_color');
            $table->boolean('in_stock')->default(true);
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
