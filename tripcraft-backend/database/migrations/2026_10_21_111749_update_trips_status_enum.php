<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE trips MODIFY COLUMN status ENUM('draft', 'planning', 'confirmed', 'completed', 'pending_approval', 'rejected') DEFAULT 'pending_approval'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trips MODIFY COLUMN status ENUM('draft', 'planning', 'confirmed', 'completed') DEFAULT 'draft'");
    }
};
