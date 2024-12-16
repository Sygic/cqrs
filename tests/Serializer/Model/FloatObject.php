<?php

declare(strict_types=1);

namespace CQRSTest\Serializer\Model;

class FloatObject
{
    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    private function __construct(
        private readonly float $value
    ) {
    }
}
