<?php

namespace CryptoUnifier\Cryptocurrency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Client\RequestException;

class CoinGecko
{
    /**
     * Information cache ttl.
     */
    public const CACHE_TTL = 60 * 60 * 1;

    /**
     * CoinGecko API base URL.
     */
    public const API_URL = 'https://api.coingecko.com/api/v3';


    /**
     * Flush cache asset information (If available).
     */
    public static function flushAssetInformation(string $apiId): bool
    {
        return Cache::forget(self::generateCacheHash($apiId));
    }

    /**
     * Get asset information (From cache if available).
     */
    public static function getAssetInformation(string $apiId): ?object
    {
        return Cache::remember(
            key: self::generateCacheHash($apiId),
            ttl: self::CACHE_TTL,
            callback: fn () => self::makeApiRequest($apiId)
        );
    }

    /**
     * Make API request.
     */
    protected static function makeApiRequest(string $apiId): ?object
    {
        try {
            $result = Http::retry(3)->get(self::API_URL . "/coins/{$apiId}")->json();
        } catch (RequestException $e) {
            self::addErrorLog($apiId, $e->getMessage());

            return null;
        }

        if (isset($result['status']['error_message']) || ! isset($result['market_data'])) {
            self::addErrorLog($apiId, $result['status']['error_message'] ?? 'Unknown error and market data not available');

            return null;
        }

        return (object) $result['market_data'];
    }

    /**
     * Generate cache hash based on $apiId.
     */
    protected static function generateCacheHash(string $apiId): string
    {
        return md5("{$apiId}.cryptocurrency.coingecko");
    }

    /**
     * Append error log to storage.
     */
    protected static function addErrorLog(string $apiId, string $message): bool
    {
        return Storage::append(
            path: 'coingecko-error.log',
            data: '[' . now()->toDateTimeString() . '] API ID: ' . $apiId . ' - ' . $message . '.'
        );
    }
}
