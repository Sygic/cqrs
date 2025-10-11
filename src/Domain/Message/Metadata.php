<?php

declare(strict_types=1);

namespace CQRS\Domain\Message;

use ArrayAccess;
use ArrayIterator;
use Countable;
use CQRS\Exception\RuntimeException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<string, mixed>
 * @implements ArrayAccess<string, mixed>
 */
class Metadata implements IteratorAggregate, ArrayAccess, Countable, JsonSerializable
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param self|array<string, mixed> $metadata
     */
    public static function from(self|array $metadata = []): static
    {
        if ($metadata instanceof static) {
            return $metadata;
        }

        if ($metadata instanceof self) {
            return new static($metadata->toArray());
        }

        return new static($metadata);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function jsonDeserialize(array $data): static
    {
        return new static($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    final private function __construct(array $data)
    {
        ksort($data);
        $this->data = $data;
    }

    #[\Override]
    public function jsonSerialize(): object
    {
        return (object) $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return Traversable<string, mixed>
     */
    #[\Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @param string $offset
     */
    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @throws RuntimeException
     */
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Event metadata is immutable.');
    }

    /**
     * @param string $offset
     * @throws RuntimeException
     */
    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Event metadata is immutable.');
    }

    #[\Override]
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Returns a Metadata instance containing values of this, combined with the given additionalMetadata.
     * If any entries have identical keys, the values from the additionalMetadata will take precedence.
     */
    public function mergedWith(Metadata $additionalMetadata): static
    {
        $values = array_merge($this->data, $additionalMetadata->data);

        if ($values === $this->data) {
            return $this;
        }

        return new static($values);
    }

    /**
     * Returns a Metadata instance with the items with given keys removed. Keys for which there is no
     * assigned value are ignored.
     *
     * This Metadata instance is not influenced by this operation.
     *
     * @param string[] $keys
     */
    public function withoutKeys(array $keys): static
    {
        $values = array_diff_key($this->data, array_flip($keys));

        if ($values === $this->data) {
            return $this;
        }

        return new static($values);
    }
}
