<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);           // google, github, facebook, microsoft
            $table->string('provider_user_id');        // the provider's user id — stable across sessions
            $table->string('email', 255)->nullable();  // provider's email at time of link (for audit)
            $table->string('name', 255)->nullable();   // provider's displayed name
            $table->string('avatar', 500)->nullable(); // provider's avatar URL (best-effort)
            $table->timestamp('linked_at')->useCurrent();
            $table->timestamp('last_login_at')->nullable();

            $table->unique(['provider', 'provider_user_id']);
            $table->index(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_logins');
    }
};
