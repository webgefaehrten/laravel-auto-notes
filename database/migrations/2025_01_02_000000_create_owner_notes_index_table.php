<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('owner_notes_index', function (Blueprint $table) {
            $table->unsignedBigInteger('note_id')->primary();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('context')->nullable();
            $table->timestamp('created_at')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();

            $table->index(['owner_type','owner_id','created_at'], 'oni_owner_created_idx');
            $table->index(['context','created_at'], 'oni_context_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_notes_index');
    }
};
