<?php

namespace App\Services;

use App\Enums\TransactionType;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogger
{
    private function log(string $level, string $event, array $context): void
    {
        Log::channel('daily')->{$level}($event, $context);
    }

    public function authFailure(string $email, string $reason, ?string $ip = null): void
    {
        $this->log('warning', 'auth:failure', [
            'email' => $email,
            'reason' => $reason,
            'fail_message' => $reason === 'invalid_credentials'
                ? 'The provided credentials are incorrect.'
                : $reason,
            'ip' => $ip ?? request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function authSuccess(int $userId, string $email, string $deviceName): void
    {
        $this->log('info', 'auth:success', [
            'user_id' => $userId,
            'email' => $email,
            'device_name' => $deviceName,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function registrationSuccess(int $userId, string $email): void
    {
        $this->log('info', 'auth:registration', [
            'user_id' => $userId,
            'email' => $email,
            'ip' => request()->ip(),
        ]);
    }

    public function registrationFailure(string $email, string $reason): void
    {
        $this->log('warning', 'auth:registration_failure', [
            'email' => $email,
            'reason' => $reason,
            'ip' => request()->ip(),
        ]);
    }

    public function accountDeleted(int $userId, string $email): void
    {
        $this->log('info', 'account:deleted', [
            'user_id' => $userId,
            'email' => $email,
            'ip' => request()->ip(),
        ]);
    }

    public function transactionCreated(int $userId, int $transactionId, TransactionType $type, float $amount, int $walletId): void
    {
        $this->log('info', 'transaction:created', [
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'type' => $type->value,
            'amount' => $amount,
            'wallet_id' => $walletId,
        ]);
    }

    public function transactionUpdated(int $userId, int $transactionId): void
    {
        $this->log('info', 'transaction:updated', [
            'user_id' => $userId,
            'transaction_id' => $transactionId,
        ]);
    }

    public function transactionDeleted(int $userId, int $transactionId): void
    {
        $this->log('info', 'transaction:deleted', [
            'user_id' => $userId,
            'transaction_id' => $transactionId,
        ]);
    }

    public function transactionFailed(int $userId, string $reason, array $context = []): void
    {
        $this->log('error', 'transaction:failed', array_merge([
            'user_id' => $userId,
            'reason' => $reason,
            'ip' => request()->ip(),
        ], $context));
    }

    public function walletCreated(int $userId, int $walletId, string $walletName): void
    {
        $this->log('info', 'wallet:created', [
            'user_id' => $userId,
            'wallet_id' => $walletId,
            'wallet_name' => $walletName,
        ]);
    }

    public function walletUpdated(int $userId, int $walletId): void
    {
        $this->log('info', 'wallet:updated', [
            'user_id' => $userId,
            'wallet_id' => $walletId,
        ]);
    }

    public function walletDeleted(int $userId, int $walletId): void
    {
        $this->log('info', 'wallet:deleted', [
            'user_id' => $userId,
            'wallet_id' => $walletId,
        ]);
    }

    public function recurringTransactionCreated(int $userId, int $recurringId, TransactionType $type): void
    {
        $this->log('info', 'recurring:created', [
            'user_id' => $userId,
            'recurring_id' => $recurringId,
            'type' => $type->value,
        ]);
    }

    public function recurringTransactionUpdated(int $userId, int $recurringId): void
    {
        $this->log('info', 'recurring:updated', [
            'user_id' => $userId,
            'recurring_id' => $recurringId,
        ]);
    }

    public function recurringTransactionDeleted(int $userId, int $recurringId): void
    {
        $this->log('info', 'recurring:deleted', [
            'user_id' => $userId,
            'recurring_id' => $recurringId,
        ]);
    }

    public function recurringTransactionToggled(int $userId, int $recurringId, bool $isActive): void
    {
        $this->log('info', 'recurring:toggled', [
            'user_id' => $userId,
            'recurring_id' => $recurringId,
            'is_active' => $isActive,
        ]);
    }

    public function profileUpdated(int $userId, array $changedFields): void
    {
        $this->log('info', 'profile:updated', [
            'user_id' => $userId,
            'changed_fields' => $changedFields,
            'ip' => request()->ip(),
        ]);
    }

    public function passwordChanged(int $userId): void
    {
        $this->log('info', 'password:changed', [
            'user_id' => $userId,
            'ip' => request()->ip(),
        ]);
    }

    public function unauthorizedAccess(int $userId, string $resource, string $action): void
    {
        $this->log('warning', 'access:unauthorized', [
            'user_id' => $userId,
            'resource' => $resource,
            'action' => $action,
            'ip' => request()->ip(),
        ]);
    }

    public function validationFailure(int $userId, string $context, array $errors): void
    {
        $this->log('warning', 'validation:failure', [
            'user_id' => $userId,
            'context' => $context,
            'errors' => $errors,
            'ip' => request()->ip(),
        ]);
    }

    public function serverError(Throwable $e, array $context = []): void
    {
        $this->log('error', 'server:error', array_merge([
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ], $context));
    }
}
