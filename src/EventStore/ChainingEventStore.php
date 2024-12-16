<?php

declare(strict_types=1);

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;

class ChainingEventStore implements EventStoreInterface
{
    /**
     * @param EventStoreInterface[] $eventStores
     */
    public function __construct(
        private readonly array $eventStores
    ) {
    }

    public function store(EventMessageInterface $event): void
    {
        foreach ($this->eventStores as $eventStore) {
            $eventStore->store($event);
        }
    }
}
