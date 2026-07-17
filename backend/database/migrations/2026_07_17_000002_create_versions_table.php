<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->integer('version_code')->unique();
            $table->string('version_name');
            $table->text('release_notes')->nullable();
            $table->boolean('force_update')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamp('release_date')->nullable();
            $table->integer('min_version_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('version_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('versions')->cascadeOnDelete();
            $table->string('platform');
            $table->string('channel');
            $table->string('download_url');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_channels');
        Schema::dropIfExists('versions');
    }
};
