<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Domain\Message\GenericDomainEventMessage;
use CQRS\Domain\Message\GenericEventMessage;
use CQRS\Domain\Message\Metadata;
use CQRS\Domain\Message\Timestamp;
use CQRS\Serializer\SerializerInterface;
use Ramsey\Uuid\Uuid;

class RedisEventRecord
{
    const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s.uO';

    /**
     * @var string
     */
    private $data;

    /**
     * @param EventMessageInterface $event
     * @param SerializerInterface $serializer
     * @return RedisEventRecord
     */
    public static function fromMessage(EventMessageInterface $event, SerializerInterface $serializer)
    {
        $data = [
            'id' => (string) $event->getId(),
            'timestamp' => $event->getTimestamp()
                ->format(self::TIMESTAMP_FORMAT),
            'payload_type' => $event->getPayloadType(),
            'payload' => $serializer->serialize($event->getPayload()),
            'metadata' => $serializer->serialize($event->getMetadata()),
        ];

        if ($event instanceof DomainEventMessageInterface) {
            $data['aggregate'] = [
                'type' => $event->getAggregateType(),
                'id'   => $event->getAggregateId(),
                'seq'  => $event->getSequenceNumber(),
            ];
        }

        return new self(json_encode($data));
    }

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return json_decode($this->data, true);
    }

    /**
     * @param SerializerInterface $serializer
     * @return GenericDomainEventMessage|GenericEventMessage
     */
    public function toMessage(SerializerInterface $serializer)
    {
        $data = $this->toArray();

        $id = Uuid::fromString($data['id']);
        $timestamp = new Timestamp($data['timestamp']);
        $payload = $serializer->deserialize($data['payload'], $data['payload_type']);
        $metadata = $serializer->deserialize($data['metadata'], Metadata::class);

        if (array_key_exists('aggregate', $data)) {
            return new GenericDomainEventMessage(
                $data['aggregate']['type'],
                $data['aggregate']['id'],
                $data['aggregate']['seq'],
                $payload,
                $metadata,
                $id,
                $timestamp
            );
        }

        return new GenericEventMessage($payload, $metadata, $id, $timestamp);
    }
}
