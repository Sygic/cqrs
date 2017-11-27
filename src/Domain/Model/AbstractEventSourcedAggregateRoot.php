<?php
declare(strict_types=1);

namespace CQRS\Domain\Model;

abstract class AbstractEventSourcedAggregateRoot extends AbstractAggregateRoot
{
    use EventSourcedAggregateRootTrait;
}
