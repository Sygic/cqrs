<?php

namespace CQRSTest\Serializer;
use CQRS\Domain\Payload\AbstractEvent;
use Rhumsaa\Uuid\Uuid;
use DateTime;
use stdClass;

/**
 * @property-read string $foo
 * @property-read Uuid $id
 * @property-read DateTime $time
 * @property-read stdClass $object
 */
class SomeEvent extends AbstractEvent
{
    protected $foo;
    protected $id;
    protected $time;
    protected $object;
}

