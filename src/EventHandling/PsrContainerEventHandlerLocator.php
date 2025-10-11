<?php

declare(strict_types=1);

namespace CQRS\EventHandling;

use Psr\Container\ContainerInterface;

class PsrContainerEventHandlerLocator implements EventHandlerLocatorInterface
{
    /**
     * @var array<class-string, array<string, list<string>>>
     */
    protected array $handlers = [];

    /**
     * @param array<class-string, array<string|array{handler: string, priority?: int}>> $handlers
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(protected ContainerInterface $container, array $handlers = [])
    {
        foreach ($handlers as $eventType => $eventHandlers) {
            foreach ($eventHandlers as $handler) {
                $priority = 1;
                $handlerId = $handler;

                if (is_array($handler)) {
                    $priority = isset($handler['priority']) ? (int) $handler['priority'] : 1;
                    $handlerId = $handler['handler'];
                }

                if (!is_string($handlerId)) {
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Handler id for event %s must be string; got %s',
                        $eventType,
                        get_debug_type($handlerId)
                    ));
                }

                $this->add($eventType, $handlerId, $priority);
            }
        }
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function add(string $eventType, string $handler, int $priority = 1): void
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }

        $priorityKey = $priority . '.0';

        if (!isset($this->handlers[$eventType][$priorityKey])) {
            $this->handlers[$eventType][$priorityKey] = [];
        }

        if (in_array($handler, $this->handlers[$eventType][$priorityKey], true)) {
            return;
        }

        $this->handlers[$eventType][$priorityKey][] = $handler;
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function remove(string $handler, ?string $eventType = null): void
    {
        // If event type is not specified, we need to iterate through each event type
        if ($eventType === null) {
            foreach ($this->handlers as $type => $_) {
                $this->remove($handler, $type);
            }

            return;
        }

        if (!array_key_exists($eventType, $this->handlers)) {
            return;
        }

        foreach ($this->handlers[$eventType] as $priority => $handlers) {
            foreach ($handlers as $index => $evaluatedHandler) {
                if ($evaluatedHandler !== $handler) {
                    continue;
                }

                // Found the handler; remove it.
                unset($this->handlers[$eventType][$priority][$index]);
            }

            // If the queue for the given priority is empty, remove it.
            if (empty($this->handlers[$eventType][$priority])) {
                unset($this->handlers[$eventType][$priority]);
            }
        }

        // If the queue for the given event is empty, remove it.
        if (empty($this->handlers[$eventType])) {
            unset($this->handlers[$eventType]);
        }
    }

    /**
     * Returns an array of event handlers sorted by priority from highest to lowest
     *
     * @return callable[]
     */
    #[\Override]
    public function get(string $eventType): array
    {
        $handlers = array_merge_recursive(
            $this->handlers[$eventType] ?? [],
            $this->handlers['*'] ?? [],
        );

        krsort($handlers, SORT_NUMERIC);

        $eventHandlers = [];
        foreach ($handlers as $priority => $handlersByPriority) {
            foreach ($handlersByPriority as $handlerId) {
                $handler = $this->container->get($handlerId);

                if (!is_callable($handler)) {
                    throw new Exception\RuntimeException(sprintf(
                        'Event handler "%s" of type "%s" for event "%s" is not callable',
                        $handlerId,
                        get_debug_type($handler),
                        $eventType
                    ));
                }

                $eventHandlers[] = $handler;
            }
        }

        return $eventHandlers;
    }
}
