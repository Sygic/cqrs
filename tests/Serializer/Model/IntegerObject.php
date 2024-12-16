<?php

declare(strict_types=1);

namespace CQRSTest\Serializer\Model;

class IntegerObject
{
    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    private function __construct(
        private readonly int $value
    ) {
    }
}
