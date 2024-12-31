<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration for "movies" table
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('imdb_id')->nullable();
            $table->integer('year');
            $table->string('genre')->nullable();
            $table->string('director')->nullable();
            $table->text('plot')->nullable();
            $table->string('poster_url')->nullable();
            $table->unsignedBigInteger('eliminated_by')->nullable();
            $table->timestamp('eliminated_at')->nullable();
            $table->timestamps();

            $table->foreign('eliminated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
