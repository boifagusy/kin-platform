<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('platform')->default('all');
            $table->integer('min_version_code')->nullable();
            $table->integer('max_version_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audience_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audience_id')->constrained('audiences')->cascadeOnDelete();
            $table->morphs('targetable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_targets');
        Schema::dropIfExists('audiences');
    }
};
