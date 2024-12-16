<?php

declare(strict_types=1);

namespace CQRS\Plugin\Doctrine\CommandHandling;

class ExplicitOrmTransactionManager extends AbstractOrmTransactionManager
{
    #[\Override]
    public function begin(): void
    {
        $this->entityManager->beginTransaction();
    }

    #[\Override]
    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    #[\Override]
    public function rollback(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
    }
}
