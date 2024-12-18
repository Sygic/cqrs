<?php

declare(strict_types=1);

namespace CQRSTest\EventStore;

use CQRS\Domain\Message\Metadata;
use CQRS\Serializer\SerializerInterface;

final class SomeSerializer implements SerializerInterface
{
    #[\Override]
    public function serialize(object $data): string
    {
        return '{}';
    }

    #[\Override]
    public function deserialize(string $data, string $type): object
    {
        return match ($type) {
            SomeEvent::class => new SomeEvent(),
            Metadata::class => Metadata::from([]),
            'array' => [],
            default => null,
        };
    }
}
