<?php

declare(strict_types=1);

namespace CQRSTest\Domain\Message;

use CQRS\Domain\Message\GenericDomainEventMessage;
use CQRS\Domain\Message\Metadata;
use Pauci\DateTime\DateTime;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GenericDomainEventMessageTest extends TestCase
{
    public function testCreateFromDomainEvent(): void
    {
        $event = new SomePayload();

        $message = new GenericDomainEventMessage('SomeAggregate', 1234, 5, $event);

        $this->assertEquals('SomeAggregate', $message->getAggregateType());
        $this->assertEquals(1234, $message->getAggregateId());
        $this->assertEquals(5, $message->getSequenceNumber());
        $this->assertSame($event, $message->getPayload());
    }

    public function testReconstructUsingExistingData(): void
    {
        $id = Uuid::uuid4();
        $timestamp = DateTime::microsecondsNow();
        $metadata = Metadata::from(['foo' => 'bar']);

        $message = new GenericDomainEventMessage(
            'SomeAggregate',
            1234,
            5,
            new SomePayload(),
            $metadata,
            $id,
            $timestamp
        );

        $this->assertSame($metadata, $message->getMetadata());
        $this->assertSame($id, $message->getId());
        $this->assertSame($timestamp, $message->getTimestamp());
    }
}
