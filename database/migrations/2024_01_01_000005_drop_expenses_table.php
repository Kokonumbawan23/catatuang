<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('expenses');
    }

    public function down(): void
    {
        // Tabel expenses lama sudah dihentikan penggunaannya dan digantikan
        // oleh tabel `transactions`. Skema lama tidak dibuat ulang pada
        // rollback karena data sudah dimigrasikan/dihapus.
    }
};
