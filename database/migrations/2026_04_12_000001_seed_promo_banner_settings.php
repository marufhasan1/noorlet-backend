<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $promo = [
            'flash_badge'       => 'FLASH SALE',
            'flash_heading'     => 'Up to',
            'flash_accent'      => '60% Off',
            'flash_description' => "Selected women's styles — limited time only. Don't miss out.",
            'flash_btn_label'   => 'Shop the Sale →',
            'flash_btn_url'     => '/products?badge=SALE',
            'flash_hours'       => 8,

            'promo_badge'       => 'EXCLUSIVE',
            'promo_heading'     => 'New Member',
            'promo_accent'      => '15% Off',
            'promo_subheading'  => 'First Order',
            'promo_description' => 'Sign up and use code NOORLET15 at checkout.',
            'promo_code'        => 'NOORLET15',
        ];

        DB::table('settings')->upsert(
            ['key' => 'promo_banner_settings', 'value' => json_encode($promo), 'created_at' => now(), 'updated_at' => now()],
            ['key'],
            ['value', 'updated_at']
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'promo_banner_settings')->delete();
    }
};
