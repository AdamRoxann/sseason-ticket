<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('description')->nullable(); $table->dateTime('event_starts_at')->nullable(); $table->string('venue')->nullable();
            $table->dateTime('registration_opens_at')->nullable(); $table->dateTime('registration_closes_at')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->unsignedInteger('quota'); $table->unsignedInteger('sold')->default(0); $table->unsignedInteger('max_per_order')->default(2); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['event_id', 'name']);
        });
        Schema::create('waiting_room_entries', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->uuid('queue_token')->unique();
            $table->string('status')->default('waiting')->index(); $table->dateTime('joined_at'); $table->dateTime('admitted_at')->nullable(); $table->dateTime('expires_at')->nullable(); $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });
        Schema::create('tickets', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete(); $table->foreignId('ticket_category_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waiting_room_entry_id')->constrained()->cascadeOnDelete(); $table->uuid('public_code')->unique(); $table->string('qr_path')->nullable(); $table->string('status')->default('issued')->index(); $table->dateTime('checked_in_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); Schema::dropIfExists('waiting_room_entries'); Schema::dropIfExists('ticket_categories'); Schema::dropIfExists('events'); }
};
