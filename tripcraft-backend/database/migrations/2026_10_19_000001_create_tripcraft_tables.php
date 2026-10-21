<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location');
            $t->string('category');
            $t->text('description');
            $t->decimal('avg_daily_cost', 10, 2)->default(0);
            $t->json('tags')->nullable();
            $t->text('image_url')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('trips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $t->string('title');
            $t->date('start_date');
            $t->date('end_date');
            $t->decimal('budget', 12, 2)->default(0);
            $t->json('preferences')->nullable();
            $t->enum('status', ['draft', 'planning', 'confirmed', 'completed', 'pending_approval', 'rejected'])->default('pending_approval');
            $t->timestamps();
        });

        Schema::create('trip_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->enum('member_role', ['owner', 'editor', 'viewer'])->default('editor');
            $t->enum('invite_status', ['pending', 'accepted', 'declined'])->default('pending');
            $t->timestamps();
            $t->unique(['trip_id', 'email']);
        });

        Schema::create('itinerary_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('day_number');
            $t->string('title');
            $t->string('location')->nullable();
            $t->time('start_time')->nullable();
            $t->time('end_time')->nullable();
            $t->decimal('cost', 10, 2)->default(0);
            $t->text('notes')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('trip_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->nullable()->constrained('itinerary_items')->nullOnDelete();
            $t->text('comment');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_comments');
        Schema::dropIfExists('itinerary_items');
        Schema::dropIfExists('trip_members');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('destinations');
    }
};
