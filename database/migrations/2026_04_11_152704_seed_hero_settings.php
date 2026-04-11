<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hero = [
            'badge_text'          => 'Spring / Summer 2025 Collection',
            'heading1'            => 'Redefine',
            'heading2'            => 'Your Style',
            'description'         => "Discover curated collections from the world's finest designers. Timeless elegance meets modern sophistication.",
            'btn_primary_label'   => 'Shop Now',
            'btn_primary_url'     => '/products',
            'btn_secondary_label' => 'Explore Categories',
            'btn_secondary_url'   => '/products',
            'stats' => [
                ['value' => '15K+', 'label' => 'Products'],
                ['value' => '200+', 'label' => 'Brands'],
                ['value' => '50K+', 'label' => 'Happy Customers'],
            ],
            'card_icon'     => 'fas fa-tshirt',
            'card_title'    => 'Featured Item',
            'card_subtitle' => 'Premium Collection',
            'card_badge'    => 'NEW',
            'gradient_from' => '#1e1b4b',
            'gradient_via'  => '#312e81',
            'gradient_to'   => '#4338ca',
        ];

        DB::table('settings')->upsert(
            ['key' => 'hero_settings', 'value' => json_encode($hero), 'created_at' => now(), 'updated_at' => now()],
            ['key'],
            ['value', 'updated_at']
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'hero_settings')->delete();
    }
};
