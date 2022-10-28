<?php

namespace CryptoUnifier\Cryptocurrency;

use Illuminate\Support\Collection;

use Illuminate\Contracts\Support\Arrayable;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;

abstract class AbstractModel implements Arrayable
{
    use HasAttributes;
    use HasRelationships;
    use HidesAttributes;

    protected $exists = true;

    /**
     * Get all of the models from the abstract storage method.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    abstract public static function all(): Collection;

    /**
     * Get abstract model instance by primary key.
     */
    abstract public static function find(string $key): static;

    /**
     * Create a new Abstract model instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return null !== $this->getAttribute($offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     */
    public function __unset($key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public function usesTimestamps()
    {
        return false;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
}
