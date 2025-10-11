<?php

declare(strict_types=1);

namespace CQRS\EventStore;

use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Domain\Message\GenericDomainEventMessage;
use CQRS\Domain\Message\GenericEventMessage;
use CQRS\Serializer\SerializerInterface;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class RedisEventRecord implements \Stringable
{
    public static function fromMessage(EventMessageInterface $event, SerializerInterface $serializer): self
    {
        $metadata = $event->getMetadata()->toArray();
        $metadataTypes = [];

        foreach ($metadata as $key => $value) {
            if (is_object($value)) {
                $metadataTypes[$key] = $value::class;
                $metadata[$key] = $serializer->serialize($value);
            }
        }

        $data = [
            'id' => $event->getId(),
            'timestamp' => $event->getTimestamp()->format('Y-m-d\TH:i:s.uP'),
            'payload' => [
                'data' => $serializer->serialize($event->getPayload()),
                'type' => $event->getPayloadType(),
            ],
            'metadata' => [
                'data' => $metadata,
                'types' => $metadataTypes,
            ],
        ];

        if ($event instanceof DomainEventMessageInterface) {
            $data['aggregate'] = [
                'type' => $event->getAggregateType(),
                'id' => $event->getAggregateId(),
                'seq' => $event->getSequenceNumber(),
            ];
        }

        return new self(json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function __construct(
        private readonly string $data
    ) {
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->data;
    }

    /**
     * @return array{
     *     id: string,
     *     timestamp: string,
     *     payload: array{
     *         data: string,
     *         type: string,
     *     },
     *     metadata: array{
     *         data: array<string, mixed>,
     *         types: array<string, string>,
     *     },
     *     aggregate?: array{
     *         type: string,
     *         id: mixed,
     *         seq: int,
     *     },
     * }
     */
    public function toArray(): array
    {
        return json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
    }

    public function toMessage(SerializerInterface $serializer): GenericEventMessage|GenericDomainEventMessage
    {
        $data = $this->toArray();

        $id = Uuid::fromString($data['id']);
        $timestamp = new DateTimeImmutable($data['timestamp']);
        $payload = $serializer->deserialize($data['payload']['data'], $data['payload']['type']);

        $metadata = $data['metadata']['data'];
        foreach ($data['metadata']['types'] as $key => $type) {
            if (!is_string($type) || !array_key_exists($key, $metadata) || !is_string($metadata[$key])) {
                continue;
            }
            $metadata[$key] = $serializer->deserialize($metadata[$key], $type);
        }

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
