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
        Schema::create('music_library_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('music_library_chapter_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sort_key')->index();
            $table->text('description')->nullable();
            $table->string('tradition')->nullable();
            $table->unsignedTinyInteger('tone')->nullable();
            $table->unsignedTinyInteger('parts')->default(2);
            $table->string('icon_path')->nullable();
            $table->string('sheet_music_pdf_path')->nullable();
            $table->string('note_playback_path')->nullable();
            $table->string('note_playback_s_path')->nullable();
            $table->string('note_playback_a_path')->nullable();
            $table->string('note_playback_t_path')->nullable();
            $table->string('note_playback_b_path')->nullable();
            $table->string('example_mp3_path')->nullable();
            $table->string('example_youtube_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_library_entries');
    }
};
