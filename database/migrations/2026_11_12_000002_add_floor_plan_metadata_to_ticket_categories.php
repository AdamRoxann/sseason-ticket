<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->string('display_color', 20)->default('#D7BD72');
            $table->unsignedTinyInteger('map_x')->nullable();
            $table->unsignedTinyInteger('map_y')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn(['display_color', 'map_x', 'map_y']);
        });
    }
};
