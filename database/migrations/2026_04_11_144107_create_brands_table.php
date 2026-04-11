<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('website_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed with the existing hardcoded brands
        $brands = [
            ['name' => 'VOGUE',   'sort_order' => 1],
            ['name' => 'NoorLet', 'sort_order' => 2],
            ['name' => 'Milano',  'sort_order' => 3],
            ['name' => 'Élite',   'sort_order' => 4],
            ['name' => 'ATELIER', 'sort_order' => 5],
            ['name' => 'Maison',  'sort_order' => 6],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert(array_merge($brand, [
                'website_url' => null,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
