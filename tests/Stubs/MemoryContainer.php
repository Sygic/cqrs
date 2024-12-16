<?php

declare(strict_types=1);

namespace CQRSTest\Stubs;

use Psr\Container\ContainerInterface;

final class MemoryContainer implements ContainerInterface
{
    public function __construct(private array $services)
    {
    }

    public function get(string $id): mixed
    {
        return $this->services[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
