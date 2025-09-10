<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes_archive', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('context')->nullable();
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['subject_type','subject_id','created_at'], 'na_subject_created_idx');
            $table->index(['owner_type','owner_id','created_at'], 'na_owner_created_idx');
            $table->index(['context','created_at'], 'na_context_created_idx');
            $table->index(['created_by','created_at'], 'na_author_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes_archive');
    }
};
