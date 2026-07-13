<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trusted_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('trusted_contacts', 'status')) {
                $table->string('status')->default('pending')->after('active');
            }
            if (!Schema::hasColumn('trusted_contacts', 'token_hash')) {
                $table->string('token_hash', 64)->nullable()->after('verified');
            }
            if (!Schema::hasColumn('trusted_contacts', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable()->after('token_hash');
            }
            if (!Schema::hasColumn('trusted_contacts', 'resend_count')) {
                $table->unsignedTinyInteger('resend_count')->default(0)->after('token_expires_at');
            }
            if (!Schema::hasColumn('trusted_contacts', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('resend_count');
            }
            if (!Schema::hasColumn('trusted_contacts', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('trusted_contacts', 'verification_method')) {
                $table->string('verification_method')->default('link')->after('revoked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trusted_contacts', function (Blueprint $table) {
            $table->dropColumn(['status', 'token_hash', 'token_expires_at', 'resend_count', 'verified_at', 'revoked_at', 'verification_method']);
        });
    }
};
