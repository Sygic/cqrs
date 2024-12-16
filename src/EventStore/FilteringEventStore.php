<?php

declare(strict_types=1);

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;

class FilteringEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly EventFilterInterface $filter
    ) {
    }

    #[\Override]
    public function store(EventMessageInterface $event): void
    {
        if ($this->filter->isValid($event)) {
            $this->eventStore->store($event);
        }
    }
}
