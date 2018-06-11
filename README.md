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

# .env

The QueryRepository, EventStore, AggregateProjectionRepository and EventBus have both in memory and redis implementations.
Shown below is the default:

```
QUERY_REPOSITORY=memory
EVENT_STORE=memory
AGGREGATE_REPOSITORY=memory
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

