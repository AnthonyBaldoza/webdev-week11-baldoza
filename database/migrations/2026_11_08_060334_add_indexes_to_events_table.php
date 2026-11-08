<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Full-text: GIN on to_tsvector('english', description)
        Schema::table('events', function (Blueprint $table) {
            $table->fullText('description', 'events_description_fts_idx');
        });

        // Raw: the schema builder cannot create expression or jsonb_path_ops indexes
        DB::statement("CREATE INDEX events_dress_code_idx ON events ((details->>'dress_code'))");
        DB::statement('CREATE INDEX events_details_gin_idx ON events USING GIN (details jsonb_path_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS events_description_fts_idx');
        DB::statement('DROP INDEX IF EXISTS events_dress_code_idx');
        DB::statement('DROP INDEX IF EXISTS events_details_gin_idx');
    }
};