<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->nullableMorphs('owner');
            $table->string('context')->nullable();
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subject_type','subject_id','created_at'], 'notes_subject_created_idx');
            $table->index(['owner_type','owner_id','created_at'], 'notes_owner_created_idx');
            $table->index(['context','created_at'], 'notes_context_created_idx');
            $table->index(['created_by','created_at'], 'notes_author_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
