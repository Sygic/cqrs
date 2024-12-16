<?php

declare(strict_types=1);

namespace CQRS\Domain\Message;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class GenericDomainEventMessage extends GenericEventMessage implements DomainEventMessageInterface
{
    public function __construct(
        private readonly string $aggregateType,
        private readonly mixed $aggregateId,
        private readonly int $sequenceNumber,
        object $payload,
        Metadata|array $metadata = [],
        ?UuidInterface $id = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        parent::__construct($payload, $metadata, $id, $timestamp);
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['aggregateType'] = $this->aggregateType;
        $data['aggregateId'] = $this->aggregateId;

        return $data;
    }

    #[\Override]
    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }

    #[\Override]
    public function getAggregateId(): mixed
    {
        return $this->aggregateId;
    }

    #[\Override]
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }
}
