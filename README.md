Castor Message Bus
==================

A well-designed and flexible message bus.

```
composer require castor/message-bus
```

## Basic Usage

You must create a message bus and pass some middleware to it. By default, we
provide middleware that finds handlers from a service container using a naming
convention.

```php
<?php

use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(new SomeMiddleware());
$bus->add(new MessageBus\HandleMessage(
    new MessageBus\Handler\ClassSuffixInflector(),
    new MessageBus\Handler\ContainerLocator($aContainer)
));

$bus->handle(new SomeCommand());
```

By default, the middleware is run in the order of addition.

The booting defaults don't even need some registrations.
