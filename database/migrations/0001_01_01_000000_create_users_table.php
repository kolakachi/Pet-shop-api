<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('autoincrement');
            $table->string('uuid')->unique()->comment('UUID to allow easy migration between environments without breaking FK in the logic');
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('is_admin')->default(false)->comment('default (0)');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable()->comment('nullable');
            $table->string('password');
            $table->string('avatar')->nullable()->comment('nullable, UUID of the image stored in the the files table');
            $table->string('address');
            $table->string('phone_number');
            $table->boolean('is_marketing')->default(false)->comment('Enable marketing preferences: default (0)');
            $table->timestamps();
            $table->timestamp('last_login_at')->nullable()->comment('nullable');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
