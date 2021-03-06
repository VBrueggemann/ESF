<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\ValueObjects\AggregateRootId;

interface EventStore
{
    public function push(DomainEventStream $domainEventStream, $meta = null);

    public function get(AggregateRootId $aggregateRootId, int $playhead = 0): DomainEventStream;

    public function getAll(int $start = 0): DomainEventStream;
}