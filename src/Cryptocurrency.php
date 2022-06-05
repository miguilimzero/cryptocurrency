<?php

namespace CryptoUnifier\Cryptocurrency;

use InvalidArgumentException;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

abstract class Cryptocurrency extends AbstractModel
{
    /**
     * Fiat currency for conversion
     *
     * @var string
     */
    protected $currency = 'usd';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];


    /**
     * Get cryptocurrency list information in a collection of instances.
     *
     * @return Collection<int, static>
     */
    final public static function all(): Collection
    {
        return collect(config('custom.cryptocurrencies'))->map(
            fn ($attributes, $symbol) => new static(array_merge(['symbol' => $symbol], $attributes))
        )->sortKeys()->values();
    }

    /**
     * Get cryptocurrency instance.
     */
    final public static function find(string $key): static
    {
        $attributes = config("custom.cryptocurrencies.{$key}");

        if ($attributes === null || empty(array_filter($attributes))) {
            throw new InvalidArgumentException("Cryptocurrency symbol [{$key}] is not supported.");
        }

        return new static(array_merge(['symbol' => $key], $attributes));
    }

    /**
     * Set currency value.
     */
    final public function setCurrency(string $value): void
    {
        $this->currency = Str::lower($value);
    }

    /**
     * Flush CoinGecko cache data.
     */
    final public function flushCoingeckoData(): bool
    {
        return CoinGecko::flushAssetInformation($this->coingecko_id);
    }

    /**
     * Get CoinGecko attribute.
     */
    final public function getCoingeckoAttribute(): ?object
    {
        return CoinGecko::getAssetInformation($this->coingecko_id);
    }

    /**
     * Get CoinGecko Id attribute.
     */
    final public function getCoingeckoIdAttribute(): string
    {
        return $this->cg_id ?? $this->name;
    }

    /**
     * Get formatted name attribute.
     */
    final public function getFormattedNameAttribute(): string
    {
        return Str::headline($this->name);
    }

    /**
     * Get price attribute.
     */
    final public function getPriceAttribute(): float
    {
        return $this->coingecko->current_price[$this->currency] ?? 0;
    }

    /**
     * Get market cap attribute.
     */
    final public function geMarketCapAttribute(): float
    {
        return $this->coingecko->market_cap[$this->currency] ?? 0;
    }

    /**
     * Get percentage 30 days variation attribute.
     */
    final public function getPercentage30dVariationAttribute(): float
    {
        return $this->coingecko->price_change_percentage_30d ?? 0;
    }
}
