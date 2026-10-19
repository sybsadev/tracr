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
        Schema::create('errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')
                ->references('id')
                ->on('apps')
                ->cascadeOnDelete();
            $table->enum('env', ['LOCAL', 'DEV', 'PROD']);
            $table->string('exception');
            $table->string('file');
            $table->integer('line');
            $table->timestamps();

            $table->string('fingerprint');

            $table->unique(['app_id', 'fingerprint']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('errors');
    }
};
