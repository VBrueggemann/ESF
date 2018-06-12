<?php

namespace ESFoundation\ES\ValueObjects;

use ESFoundation\ValueObjects\Id;
use Ramsey\Uuid\Uuid;

class AggregateRootId extends Id
{
    public static function make()
    {
        return new self(Uuid::uuid4()->toString());
    }
}