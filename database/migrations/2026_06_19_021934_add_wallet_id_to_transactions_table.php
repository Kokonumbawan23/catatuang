<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('wallet_id')
                ->after('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['wallet_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
            $table->dropColumn('wallet_id');
        });
    }
};
