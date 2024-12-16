<?php

declare(strict_types=1);

namespace CQRS\CommandHandling\TransactionManager;

/**
 * @codeCoverageIgnore
 */
class NoTransactionManager implements TransactionManagerInterface
{
    #[\Override]
    public function begin(): void
    {
    }

    #[\Override]
    public function commit(): void
    {
    }

    #[\Override]
    public function rollback(): void
    {
    }
}
