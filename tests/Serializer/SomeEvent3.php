<?php

declare(strict_types=1);

namespace CQRSTest\Serializer;

use CQRSTest\Serializer\Model\IntegerObject;
use CQRSTest\Serializer\Model\UuidObject;
use CQRSTest\Serializer\Model\StringObject;

class SomeEvent3
{
    public function __construct(
        private readonly UuidObject $uuid,
        private readonly IntegerObject $int,
        private readonly StringObject $string1,
        private readonly ?StringObject $string2 = null
    ) {
    }
}
