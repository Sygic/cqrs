<?php

declare(strict_types=1);

namespace CQRS\EventHandling\Publisher;

use CQRS\Domain\Message\DomainEventMessageInterface;

class DomainEventQueue implements EventQueueInterface
{
    public function __construct(
        private readonly IdentityMapInterface $identityMap
    ) {
    }

    /**
     * @return DomainEventMessageInterface[]
     */
    public function dequeueAllEvents(): array
    {
        $dequeueEvents = [];

        foreach ($this->identityMap->getAll() as $aggregateRoot) {
            foreach ($aggregateRoot->getUncommittedEvents() as $event) {
                $dequeueEvents[] = $event;
            }

            $aggregateRoot->commitEvents();
        }

        return $dequeueEvents;
    }
}
