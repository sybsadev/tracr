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
        Schema::create('app_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')
                ->references('id')
                ->on('apps')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('public_id')->unique();
            $table->string('secret_hash');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_keys');
    }
};
