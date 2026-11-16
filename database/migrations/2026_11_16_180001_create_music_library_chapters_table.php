<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('music_library_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('music_library_book_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sort_key')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_library_chapters');
    }
};
