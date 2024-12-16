<?php

declare(strict_types=1);

namespace CQRSTest\Serializer;

use CQRS\Domain\Model\AbstractAggregateRoot;

class SomeAggregate extends AbstractAggregateRoot
{
    #[\Override]
    public function getId(): int
    {
        return 4;
    }
}
