<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname')->unique();
            $table->string('ip_address');
            $table->string('os_type')->nullable();
            $table->enum('status', ['online', 'offline', 'warning', 'unknown'])->default('unknown');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('zabbix_host_id')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cpu_usage', 5, 2)->default(0);
            $table->decimal('ram_usage', 5, 2)->default(0);
            $table->decimal('disk_usage', 5, 2)->default(0);
            $table->decimal('network_usage', 5, 2)->default(0);
            $table->integer('uptime_seconds')->default(0);
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};