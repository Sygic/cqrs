<?php

declare(strict_types=1);

namespace CQRSTest\Serializer\Model;

class StringObject
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(
        private readonly string $value
    ) {
    }
}
