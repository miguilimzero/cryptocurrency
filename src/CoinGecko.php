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
        $result = Cache::remember(
            key: self::generateCacheHash($apiId),
            ttl: self::CACHE_TTL,
            callback: fn () => self::makeApiRequest($apiId)
        );

        if ($result === null) {
            Storage::append('coingecko-error.log', '[' . now()->toDateTimeString() . '] Null cached information for the following Api ID: ' . $apiId);
        }

        return $result;
    }

    /**
     * Make API request.
     */
    protected static function makeApiRequest(string $apiId): ?object
    {
        try {
            $result = Http::get(self::API_URL . "/coins/{$apiId}")->json();
        } catch (RequestException) {
            return null;
        }

        if (isset($result['error']) || ! isset($result['market_data'])) {
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
}
