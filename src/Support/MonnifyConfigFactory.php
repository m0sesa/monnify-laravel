<?php

namespace Monnify\MonnifyLaravel\Support;

use Monnify\MonnifyConfig;

final class MonnifyConfigFactory
{
    public static function make(
        string $fallbackContractCode = 'not-required',
        string $fallbackApiKey = 'not-required',
        string $fallbackSecretKey = 'not-required',
    ): MonnifyConfig
    {
        return MonnifyConfig::fromArray([
            'api_key' => self::credential('api_key', $fallbackApiKey),
            'secret_key' => self::credential('secret_key', $fallbackSecretKey),
            'contract_code' => self::contractCode($fallbackContractCode),
            'environment' => (string) config('monnify.environment'),
            'api_url' => self::baseUrl(),
        ]);
    }

    private static function credential(string $key, string $fallback): string
    {
        $value = config('monnify.' . $key);

        return is_string($value) && $value !== ''
            ? $value
            : $fallback;
    }

    private static function contractCode(string $fallbackContractCode): string
    {
        $contractCode = config('monnify.contract_code');

        return is_string($contractCode) && $contractCode !== ''
            ? $contractCode
            : $fallbackContractCode;
    }

    private static function baseUrl(): string
    {
        $environment = strtoupper((string) config('monnify.environment'));

        return match ($environment) {
            'SANDBOX' => (string) config('monnify.sandbox_url'),
            'LIVE' => (string) config('monnify.live_url'),
            default => '',
        };
    }
}
