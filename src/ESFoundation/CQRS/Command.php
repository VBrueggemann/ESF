<?php

namespace ESFoundation\CQRS;

use ESFoundation\CQRS\Errors\InvalidCommandPayloadException;
use ESFoundation\Traits\Payloadable;
use ESFoundation\Traits\PayloadableContract;

abstract class Command implements PayloadableContract
{
    use Payloadable;

    /**
     * Command constructor.
     * @param $payload
     */
    public function __construct($payload = null)
    {
        $this->setPayload($payload, InvalidCommandPayloadException::class);
    }

    public static function with($payload = null)
    {
        $self = get_called_class();
        return new $self($payload);
    }
}