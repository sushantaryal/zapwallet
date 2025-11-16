<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class BalanceCacheService
{
    protected string $prefix = 'user_balance_';
    protected int $ttl = 3600; // 1 hour

    public function get(User $user): ?float
    {
        return Cache::get($this->key($user->id));
    }

    public function set(User $user, float $balance): void
    {
        Cache::put($this->key($user->id), $balance, $this->ttl);
    }

    public function forget(User $user): void
    {
        Cache::forget($this->key($user->id));
    }

    protected function key(int $userId): string
    {
        return "{$this->prefix}{$userId}";
    }
}
