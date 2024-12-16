<?php

declare(strict_types=1);

namespace CQRS\Plugin\Doctrine\CommandHandling;

class ImplicitOrmTransactionManager extends AbstractOrmTransactionManager
{
    #[\Override]
    public function begin(): void
    {
    }

    #[\Override]
    public function commit(): void
    {
        $this->entityManager->flush();
    }

    #[\Override]
    public function rollback(): void
    {
    }
}
