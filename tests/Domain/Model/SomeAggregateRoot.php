<?php

declare(strict_types=1);

namespace CQRSTest\Domain\Model;

use CQRS\Domain\Model\AbstractAggregateRoot;

/**
 * @phpstan-template Id
 */
class SomeAggregateRoot extends AbstractAggregateRoot
{
    /**
     * @phpstan-param Id $id
     */
    public function __construct(
        /**
         * @phpstan-var Id
         */
        private readonly mixed $id
    ) {
    }

    /**
     * @phpstan-return Id
     * @return mixed
     */
    #[\Override]
    public function getId(): mixed
    {
        return $this->id;
    }

    public function raise(object $event): void
    {
        $this->registerEvent($event);
    }
}
