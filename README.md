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
If no rules are defined any payload is acceped. 
If keys in the payload are not defined in the rules they are thrown out.
If values in the payload are not passing validation an Exception is thrown.

Best is to define a named array as payload:
```
new COMMAND([
  'foo' => 'bar
]);
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

















