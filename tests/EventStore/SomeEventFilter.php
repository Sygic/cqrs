<?php

namespace CQRSTest\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\EventStore\EventFilterInterface;

class SomeEventFilter implements EventFilterInterface
{
    #[\Override]
    public function isValid(EventMessageInterface $event): bool
    {
        $meta = $event->getMetadata();
        return (bool) ($meta['valid'] ?? false);
    }
}
