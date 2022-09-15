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
        $cache = Cache::get(self::generateCacheHash($apiId));

        // Cache not available || Cache expired
        if (! is_array($cache)) {
            $result = self::makeApiRequest($apiId);

            self::saveAssetInformationOnCache(
                apiId: $apiId,
                result: $result
            );
        } elseif ($cache[1] < time()) {
            $result = self::makeApiRequest($apiId);

            self::saveAssetInformationOnCache(
                apiId: $apiId,
                result: (is_object($result)) ? $result : $cache[0]
            );
        } else {
            $result = $cache[0];
        }

        return $result;
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
        return md5("{$apiId}.asset-information.coingecko");
    }

    /**
     * Save/update asset information on cache.
     */
    protected static function saveAssetInformationOnCache(string $apiId, ?object $result): bool
    {
        return Cache::forever(
            key: self::generateCacheHash($apiId),
            value: [$result, time() + self::CACHE_TTL]
        );
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
