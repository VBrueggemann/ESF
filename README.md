# EventSourcingFoundation

# Installing :

Using Composer - execute
```
composer require  brueggemann/esf
```

# Setup :
Register the ESFoundationServiceProvider in the bootstrap/app.php

```
$app->register(ESFoundation\ServiceProviders\ESFoundationServiceProvider::class);
```

For the ESF Facade uncomment:
```
$app->withFacades();
```

For Redis the RedisServiceProvider is needed:
```
$app->register(\Illuminate\Redis\RedisServiceProvider::class);
```
and the correct configuration has to be loaded:
```
$app->configure('database');
```

In config/database.php a configuration similar to this is needed:
```
'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '10.0.2.2'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
        'events' => [
            'host' => env('REDIS_HOST', '10.0.2.2'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ],
        'aggregates' => [
            'host' => env('REDIS_HOST', '10.0.2.2'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 2,
        ],
        'queries' => [
            'host' => env('REDIS_HOST', '10.0.2.2'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 3,
        ],
    ],
```

# .env

The QueryRepository, EventStore, AggregateProjectionRepository and EventBus have both in memory and redis implementations.

```
QUERY_REPOSITORY=redis# 
EVENT_STORE=redis
AGGREGATE_REPOSITORY=redis
EVENT_BUS=memory
COMMAND_BUS=memory
```

# Artisan :

There are a few Artisan Commands to help create the basic classes needed.

```
$ php artisan make:aggregateRoot
$ php artisan make:aggregateRootProjection
$ php artisan make:aggregateRootValidator
$ php artisan make:command
$ php artisan make:commandHandler
$ php artisan make:event
```

# Usage :

## Commands :

The entrypoint to the Event Sourced part of your application should be a CommandHandler.

```
$ php artisan make:commandHandler -name NAME -command COMMANDNAME -command COMMANDNAME -eventBus
```
If an AggregateProjectionRepository is needed for the CommandHandler add
```
$ [...] -aggregateProjectionRepository
```

If a CommandBus is in use, the CommandHandler has to be registered in the AppServiceProvider:
```    
public function register()
    {
        $commandBus = ESF::commandBus();
        $commandBus->subscribe(app(ShippingCommandHandler::class));
        
        [...]
    }
```

In the handleCOMMAND methods of the created CommandHandler an AggregateProjection can be loaded either by:
```
AGGREGATEROOT::load(AggregateRootId::new($AGGREGATEROOTID));
```
or if the aggregateProjectionRepository is injected in the constructor
```
$this->aggregateProjectionRepository->load(new AggregateRootId($AGGREGATEROOTID), AGGREGATEROOT::class);
```

Events can be either stored with:
```
ESF::eventBus()->dispatch($DOMAINEVENTSTREAM);
```
or if the eventStore is injected in the constructor
```
$this->eventBus->dispatch($DOMAINEVENTSTREAM);
```

In the created Command classes rules may be defined in the standart laravel validation way:
```
public function rules()
{
    return [
        'foo' => 'required|string'
    ];
}
```

A Commands constructor takes any form of payload and validates it against the requirements of the defined rules() method.
- If no rules are defined any payload is acceped. 
- If keys in the payload are not defined in the rules they are thrown out.
- If values in the payload are not passing validation an Exception is thrown.

Best is to define a named array as payload:
```
new COMMAND([
  'foo' => 'bar
]);
```

It is possible to either retrieve the payload as a whole:
```
$COMMAND->getPayload();
```
or by key:
```
$COMMAND->foo;
```

A CommandHandler is either called direcly:
```
$commandHandler = app(COMMANDHANDLER::class);
$commandHandler->handle(new COMMAND(['foo => 'bar]));
```
or using the COMMANDBUS:
```
ESF::commandBus()->dispatch(new COMMAND(['foo' => 'bar']));
```

## Aggregates :

Aggregates are split into three parts:

- logic: AggregateRoot
- representation: AggregateRootProjection
- validation: AggregateRootValidator

```
$ php artisan make:aggregateRoot -name NAME -event EVENTNAME -event EVENTNAME -projection
```

If a Validator is needed for the AggregateRoot add:
```
$ [...] -validator
```

In the applyThatEVENT methods of the created AggregateRoot class the data from the $EVENT can be applied to the $AGGREGATEROOTPROJECTION
```
$AGGREGATEROOTPROJECTION->foo = $EVENT->bar;
return true;
```

The created Event class works similar to the Command class. Rules may be defined, that determine the events payload validation.

If similar Rules are valid both for a Command and an Event it is recommended to use a ValueObject to represent both the rules and a value per instance.

```
$ php artisan make:valueObject -name NAME
```

These rules then can be imported into the COMMAND or/and EVENT:
```
    public function rules()
    {
        return [
            'foo' => VALUEOBJECT::rules(),
        ];
    }
```

If created the AggregateRootValidator class can prevent an Event on being applied to an AggregateRootProjection by returning false when a given requirement of the AggregateProjection is not fulfilled.
When the validation fails a FailedValidation exception is thrown.

The created AggregateRootProjection represents a state of an Aggregate. It contains multiple instantiated ValueObjects.
```
    public static function valueObjects(): Collection
    {
        return collect([
            'foo' => Foo::class,
            'foo2'=> Foo::class
        ]);
    }
```

To apply an Event to an AggregateRootProjection using the AggregateRoots logic there are multiple options:
```
AGGREGATEROOT::applyOn($AGGREGATEPROJECTION)->that($EVENT); // 1

AGGREGATEROOT::applyThat($EVENT, $AGGREGATEROOTPROJECTION); // 2

$AGGREGATEROOTPROJECTION->applyThat($EVENT);                // 3
```

In 1 and 2 it is possible to choose the AggregateRoots logic, where in 3 the default logic is used.


Every Event that is applied to an AggregateRootProjection is saved and can be retrieved
```
$events = $AGGREGATEROOTPROJECTION->popUncommittedEvents();
```
These Events then are commitable via the EventBus or if needed directly via the EventStore
```
ESF::eventBus()->dispatch($events);
ESF::eventStore()->push($events);
```

## Queries :
Since there are no means of retrieving Events or even Aggregates based on a specific pattern, like the where clause in SQL the accumulation of data is done via Queries


A Query should represent a page or view. To manage Queries the QueryRepository is needed.
```
$queryRepository = ESF::queryReporitory();
```

The add method saves a key-value pair. If the value is updated, a new entry under the same key is inserted.
```
$queryRepository->add('foo', 'bar');
```
To retrieve a query the get method is used.
If only the key is provided, the most recent query is returned.
If additionally an index is provided, the corresponding older query is returned.

```
$queryRepository->get('foo'); //bar
$queryRepository->get('foo', 0); //bar
```

The QueryRepository can be used to save data of long lasting calculations, crawlers or only indirectly persisted data.


