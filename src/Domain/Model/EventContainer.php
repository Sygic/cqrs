<?php

declare(strict_types=1);

namespace CQRS\Domain\Model;

use Countable;
use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\Domain\Message\GenericDomainEventMessage;
use CQRS\Domain\Message\Metadata;
use CQRS\Exception\InvalidArgumentException;
use CQRS\Exception\RuntimeException;

/**
 * Container for events related to a single aggregate. The container will wrap registered event (payload) and metadata
 * in an GenericDomainEventMessage and automatically assign the aggregate identifier and the next sequence number.
 */
class EventContainer implements Countable
{
    /**
     * @var DomainEventMessageInterface[]
     */
    private array $events = [];

    private ?int $lastSequenceNumber = null;

    private ?int $lastCommittedSequenceNumber = null;

    /**
     * Initialize an EventContainer for an aggregate with the given aggregateIdentifier. This identifier will be
     * attached to all incoming events.
     */
    public function __construct(
        private readonly string $aggregateType,
        private readonly mixed $aggregateId
    ) {
    }

    /**
     * Add an event to this container.
     */
    public function addEvent(object $payload, Metadata|array $metadata = []): GenericDomainEventMessage
    {
        $domainEventMessage = new GenericDomainEventMessage(
            $this->aggregateType,
            $this->aggregateId,
            $this->newSequenceNumber(),
            $payload,
            $metadata
        );

        $this->addEventMessage($domainEventMessage);

        return $domainEventMessage;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addEventMessage(DomainEventMessageInterface $domainEventMessage): DomainEventMessageInterface
    {
        if ($domainEventMessage->getAggregateType() !== $this->aggregateType) {
            throw new InvalidArgumentException(sprintf(
                'Trying to add an event message of aggregate %s to the event container of aggregate %s',
                $domainEventMessage->getAggregateType(),
                $this->aggregateType
            ));
        }

        if ($domainEventMessage->getAggregateId() === null) {
            $domainEventMessage = new GenericDomainEventMessage(
                $domainEventMessage->getAggregateType(),
                $this->aggregateId,
                $domainEventMessage->getSequenceNumber(),
                $domainEventMessage->getPayload(),
                $domainEventMessage->getMetadata(),
                $domainEventMessage->getId(),
                $domainEventMessage->getTimestamp()
            );
        }

        $this->lastSequenceNumber = $domainEventMessage->getSequenceNumber();
        $this->events[] = $domainEventMessage;

        return $domainEventMessage;
    }

    /**
     * @return DomainEventMessageInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Clears the events in this container. The sequence number is not modified by this call.
     */
    public function commit(): void
    {
        $this->lastCommittedSequenceNumber = $this->getLastSequenceNumber();
        $this->events = [];
    }

    /**
     * Returns the number of events currently inside this container.
     */
    #[\Override]
    public function count(): int
    {
        return count($this->events);
    }

    /**
     * Sets the first sequence number that should be assigned to an incoming event.
     *
     * @throws RuntimeException
     */
    public function initializeSequenceNumber(?int $lastKnownSequenceNumber): void
    {
        if (count($this) !== 0) {
            throw new RuntimeException('Cannot set first sequence number if events have already been added');
        }

        $this->lastCommittedSequenceNumber = $lastKnownSequenceNumber;
    }

    /**
     * Returns the sequence number of the last committed event, or null if no events have been committed.
     */
    public function getLastSequenceNumber(): ?int
    {
        if (count($this->events) === 0) {
            return $this->lastCommittedSequenceNumber;
        }

        if ($this->lastSequenceNumber === null) {
            $event = end($this->events);
            $this->lastSequenceNumber = $event->getSequenceNumber();
        }

        return $this->lastSequenceNumber;
    }

    private function newSequenceNumber(): int
    {
        $currentSequenceNumber = $this->getLastSequenceNumber();

        return $currentSequenceNumber !== null
            ? $currentSequenceNumber + 1
            : 0;
    }
}
