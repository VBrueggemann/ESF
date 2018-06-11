<?php

namespace ESFoundation\ServiceProviders;

use ESFoundation\CQRS\Contracts\CommandBus;
use ESFoundation\ES\Contracts\AggregateProjectionRepository;
use ESFoundation\ES\Contracts\EventBus;
use ESFoundation\ES\Contracts\EventStore;
use ESFoundation\ES\Contracts\QueryRepository;
use Illuminate\Support\Facades\Facade;


/**
 * @method static EventStore eventStore()
 * @method static EventBus eventBus()
 * @method static AggregateProjectionRepository aggregateProjectionRepository()
 * @method static QueryRepository queryRepository()
 * @method static CommandBus commandBus()
 *
 * @see \Illuminate\Log\Logger
 */
class ESFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ESF';
    }
}