<?php

declare(strict_types=1);

namespace CQRSTest\CommandHandling\Stubs;

use CQRS\CommandHandling\CommandHandlerLocatorInterface;

class DummyCommandHandlerLocator implements CommandHandlerLocatorInterface
{
    public array $handlers;

    #[\Override]
    public function get(string $id): callable
    {
        return $this->handlers[$id];
    }
}
