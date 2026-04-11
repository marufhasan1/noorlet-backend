<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            'announcement_enabled'  => '1',
            'announcement_bg'       => '#1e1b4b',
            'announcement_text'     => '#ffffff',
            'announcement_messages' => json_encode([
                '✦ FREE SHIPPING on orders over $75 ✦',
                '✦ NEW ARRIVALS every Friday ✦',
                '✦ USE CODE NOORLET15 FOR 15% OFF ✦',
            ]),
        ];

        foreach ($rows as $key => $value) {
            DB::table('settings')->upsert(
                ['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()],
                ['key'],
                ['value', 'updated_at']
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'announcement_enabled',
            'announcement_bg',
            'announcement_text',
            'announcement_messages',
        ])->delete();
    }
};
