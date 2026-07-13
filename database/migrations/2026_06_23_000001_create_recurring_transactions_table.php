<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->json('schedule_config');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_confirmation')->default(false);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
