<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('level', ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug']);
            $table->string('source')->nullable();
            $table->string('facility')->nullable();
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->string('log_index')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['level', 'logged_at']);
            $table->index(['server_id', 'logged_at']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};