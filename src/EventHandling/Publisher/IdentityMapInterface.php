<?php

namespace CQRS\EventHandling\Publisher;

use CQRS\Domain\Model\AggregateRootInterface;

interface IdentityMapInterface
{
    /**
     * @param mixed $id
     * @return AggregateRootInterface|null
     */
    public function get($id);

    /**
     * @return AggregateRootInterface[]
     */
    public function getAll();

    /**
     * @param AggregateRootInterface $aggregateRoot
     */
    public function add(AggregateRootInterface $aggregateRoot);

    /**
     * @param AggregateRootInterface $aggregateRoot
     */
    public function remove(AggregateRootInterface $aggregateRoot);

    public function clear();
}
