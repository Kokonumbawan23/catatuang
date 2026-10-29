<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_transaction_id')->constrained()->cascadeOnDelete();
            $table->date('due_date');
            $table->enum('status', ['pending', 'confirmed', 'skipped'])->default('pending');
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['recurring_transaction_id', 'due_date']);
            $table->index('status');
        });

        // Belum ada integrasi pembayaran, jadi recurring baru default menunggu konfirmasi user.
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->boolean('requires_confirmation')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->boolean('requires_confirmation')->default(false)->change();
        });

        Schema::dropIfExists('recurring_occurrences');
    }
};
