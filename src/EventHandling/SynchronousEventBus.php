<?php

declare(strict_types=1);

namespace CQRS\EventHandling;

use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Domain\Message\GenericEventMessage;

class SynchronousEventBus implements EventBusInterface
{
    public function __construct(
        private readonly EventHandlerLocatorInterface $locator
    ) {
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function publish(EventMessageInterface $event): void
    {
        $eventType = $event->getPayloadType();
        $eventHandlers = $this->locator->get($eventType);

        foreach ($eventHandlers as $handler) {
            if (!is_callable($handler)) {
                throw new Exception\RuntimeException(sprintf(
                    'Event handler %s is not invokable',
                    get_debug_type($handler)
                ));
            }

            $this->invokeEventHandler($handler, $event);
        }
    }

    /**
     * @throws \Exception
     */
    private function invokeEventHandler(callable $handler, EventMessageInterface $event): void
    {
        try {
            if ($event instanceof DomainEventMessageInterface) {
                $handler(
                    $event->getPayload(),
                    $event->getMetadata(),
                    $event->getTimestamp(),
                    $event->getSequenceNumber(),
                    $event->getAggregateId()
                );
            } else {
                $handler(
                    $event->getPayload(),
                    $event->getMetadata(),
                    $event->getTimestamp()
                );
            }
        } catch (\Exception $e) {
            if ($event->getPayload() instanceof EventExecutionFailed) {
                return;
            }

            $this->publish(new GenericEventMessage(
                new EventExecutionFailed($event, $e),
                $event->getMetadata()
            ));

            throw $e;
        }
    }
}
