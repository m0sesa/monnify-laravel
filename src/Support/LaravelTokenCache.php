<?php

namespace Monnify\MonnifyLaravel\Support;

use Illuminate\Contracts\Cache\Repository;
use Monnify\Auth\TokenCacheInterface;

final class LaravelTokenCache implements TokenCacheInterface
{
    public function __construct(
        private Repository $cache,
        private string $key = 'monnify_access_token',
    ) {
    }

    public function get(): ?string
    {
        $token = $this->cache->get($this->key);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function put(string $token, int $expiresIn): void
    {
        $this->cache->put($this->key, $token, $expiresIn);
    }

    public function forget(): void
    {
        $this->cache->forget($this->key);
    }
}
