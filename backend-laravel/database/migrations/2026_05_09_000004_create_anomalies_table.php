<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['cpu', 'ram', 'network', 'disk', 'behavior', 'security']);
            $table->decimal('score', 5, 2);
            $table->enum('severity', ['critical', 'warning', 'low']);
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->json('data_points')->nullable();
            $table->json('model_info')->nullable();
            $table->boolean('is_false_positive')->default(false);
            $table->timestamp('detected_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['server_id', 'type', 'detected_at']);
            $table->index(['severity', 'is_false_positive']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomalies');
    }
};