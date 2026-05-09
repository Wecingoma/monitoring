<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['critical', 'warning', 'info'])->default('info');
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
            $table->enum('source', ['zabbix', 'elastic', 'ia', 'manual'])->default('manual');
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('alertable_type')->nullable();
            $table->unsignedBigInteger('alertable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['severity', 'status']);
            $table->index(['source', 'created_at']);
            $table->index('alertable_type', 'alertable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};