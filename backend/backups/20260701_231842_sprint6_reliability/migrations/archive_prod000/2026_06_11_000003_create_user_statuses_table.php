<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'suspended', 'banned', 'inactive'])->default('active');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('suspended_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('reactivated_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamp('reactivated_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
