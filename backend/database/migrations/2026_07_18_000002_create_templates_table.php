<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('system');
            $table->text('description')->nullable();
            $table->json('channels'); // {"sms": "...", "email": "...", "whatsapp": "...", "push": "..."}
            $table->json('variables')->nullable(); // ["first_name", "amount"]
            $table->string('status')->default('draft');
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
