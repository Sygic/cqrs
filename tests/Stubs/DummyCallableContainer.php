<?php

declare(strict_types=1);

namespace CQRSTest\Stubs;

use Psr\Container\ContainerInterface;

final class DummyCallableContainer implements ContainerInterface
{
    /**
     * @throws void
     */
    #[\Override]
    public function get(string $id): callable
    {
        return fn () => $id;
    }

    #[\Override]
    public function has(string $id): bool
    {
        return true;
    }
}
