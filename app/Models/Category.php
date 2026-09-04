<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    public static function cached(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => static::all());
    }
}
