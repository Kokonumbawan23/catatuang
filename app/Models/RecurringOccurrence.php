<?php

namespace App\Models;

use App\Enums\RecurringOccurrenceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringOccurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'recurring_transaction_id',
        'due_date',
        'status',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'status' => RecurringOccurrenceStatus::class,
        ];
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', RecurringOccurrenceStatus::Pending->value);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereRelation('recurringTransaction', 'user_id', $userId);
    }
}
