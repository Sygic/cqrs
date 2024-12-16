<?php

declare(strict_types=1);

namespace CQRS\EventHandling\Publisher;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Domain\Message\Metadata;
use CQRS\EventHandling\EventBusInterface;
use CQRS\EventStore\EventStoreInterface;

class SimpleEventPublisher implements EventPublisherInterface
{
    private ?Metadata $additionalMetadata = null;

    public function __construct(
        private readonly EventBusInterface $eventBus,
        private readonly ?EventQueueInterface $queue = null,
        private readonly ?EventStoreInterface $eventStore = null,
        null|Metadata|array $additionalMetadata = null
    ) {
        if ($additionalMetadata !== null) {
            $this->additionalMetadata = Metadata::from($additionalMetadata);
        }
    }

    public function getEventBus(): EventBusInterface
    {
        return $this->eventBus;
    }

    public function setAdditionalMetadata(Metadata|array $additionalMetadata): void
    {
        $this->additionalMetadata = Metadata::from($additionalMetadata);
    }

    public function getAdditionalMetadata(): ?Metadata
    {
        return $this->additionalMetadata;
    }

    #[\Override]
    public function publishEvents(): void
    {
        $this->dispatchEvents($this->dequeueEvents());
    }

    /**
     * @return EventMessageInterface[]
     */
    protected function dequeueEvents(): array
    {
        if (!$this->queue) {
            return [];
        }

        $events = $this->queue->dequeueAllEvents();
        if ($this->additionalMetadata) {
            foreach ($events as &$event) {
                $event = $event->addMetadata($this->additionalMetadata);
            }
        }

        return $events;
    }

    /**
     * @param EventMessageInterface[] $events
     */
    protected function dispatchEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->eventStore?->store($event);

            $this->eventBus->publish($event);
        }
    }
}
