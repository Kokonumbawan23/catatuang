<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    private const CACHE_KEY = 'categories';

    private const CACHE_TTL_SECONDS = 3600;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense->value);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income->value);
    }

    // Kategori jarang berubah, jadi di-cache dan dipakai bersama oleh semua endpoint.
    // Disimpan sebagai array mentah (bukan koleksi model Eloquent) supaya cache lama
    // dari deploy sebelumnya tidak pernah gagal di-unserialize gara-gara struktur
    // class model yang berubah antar versi kode.
    public static function cached(): Collection
    {
        $rows = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => static::all()->toArray());

        return collect($rows);
    }
}
