<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add elimination fields to room_movie table
        Schema::table('room_movie', function (Blueprint $table) {
            $table->foreignId('eliminated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('eliminated_at')->nullable();
        });

        // Remove elimination fields from movies table
        Schema::table('movies', function (Blueprint $table) {
            $table->dropForeign(['eliminated_by']);
            $table->dropColumn(['eliminated_by', 'eliminated_at']);
        });
    }

    public function down(): void
    {
        // Add back elimination fields to movies table
        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('eliminated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('eliminated_at')->nullable();
        });

        // Remove elimination fields from room_movie table
        Schema::table('room_movie', function (Blueprint $table) {
            $table->dropForeign(['eliminated_by']);
            $table->dropColumn(['eliminated_by', 'eliminated_at']);
        });
    }
};
