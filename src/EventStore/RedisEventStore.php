<?php

declare(strict_types=1);

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Serializer\SerializerInterface;
use Redis;

class RedisEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Redis $redis,
        private readonly string $key = 'cqrs_event',
        private readonly int $size = 0
    ) {
    }

    #[\Override]
    public function store(EventMessageInterface $event): void
    {
        $record = RedisEventRecord::fromMessage($event, $this->serializer);
        $this->redis->lPush($this->key, (string) $record);

        if ($this->size > 0) {
            $this->redis->lTrim($this->key, 0, $this->size - 1);
        }
    }

    public function pop(int $timeout = 0): ?RedisEventRecord
    {
        $data = $this->redis->brPop($this->key, $timeout);

        if (!is_array($data) || !array_key_exists(1, $data) || !is_string($data[1])) {
            return null;
        }

        return new RedisEventRecord($data[1]);
    }
}
