<?php

declare(strict_types=1);

namespace CQRSTest\Serializer\Model;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidObject
{
    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public function __construct(
        private readonly UuidInterface $uuid
    ) {
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
