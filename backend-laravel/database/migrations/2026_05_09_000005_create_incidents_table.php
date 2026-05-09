<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['critical', 'major', 'minor', 'low']);
            $table->enum('status', ['open', 'investigating', 'identified', 'monitoring', 'resolved']);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->json('affected_servers')->nullable();
            $table->json('timeline')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['severity', 'status']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};