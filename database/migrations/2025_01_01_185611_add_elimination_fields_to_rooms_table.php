<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('elimination_started')->default(false);
            $table->boolean('elimination_in_progress')->default(false);
            $table->timestamp('last_elimination_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('elimination_started');
            $table->dropColumn('elimination_in_progress');
            $table->dropColumn('last_elimination_at');
        });
    }
};
