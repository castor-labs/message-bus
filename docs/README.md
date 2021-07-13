# Introduction

`castor/message-bus` provides a command bus object capable of processing and
handle messages. Messages are simply data classes (DTO). 

This message bus is designed to be extensible by leveraging the middleware pattern.

# Basic Usage

Simply, instantiate the bus and provide some middleware.

You must create a message bus and pass some middleware to it. By default, we
provide middleware that finds handlers from a service container using a naming
convention.

```php
<?php

use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(new SomeMiddleware());
$bus->add(new SomeOtherMiddleware);

$bus->add(new MessageBus\HandleMessage(
    new MessageBus\Handler\ClassSuffixInflector(),
    new MessageBus\Handler\ContainerLocator($aContainer)
));

$bus->handle(new SomeCommand());
```

By default, the middleware is run in the order of addition.

# Included Middleware

## `Castor\MessageBus\HandleMessage` middleware.

This middleware takes a command, finds a handler for it and executes it. In order
to do this it depends on two abstractions: `Castor\MessageBus\Handler\Inflector` and 
`Castor\MessageBus\Handler\Locator`. The first returns the handler name for the
given message object. The second returns the actual handler instance using the handler
name.

There is only one implementation provided for each abstraction.

The `Castor\MessageBus\Handler\ClassSuffixInflector` derives the handler name from
the message FQNC by adding a suffix to it. By default, the suffix is `Handler`. So,
if you pass a message named `Foo` then the derived handler name for that message
will be `FooHandler`. This is a convention you can easily bypass by implementing
a custom `Castor\MessageBus\Handler\Inflector` that resolves from, for example, an
associative PHP array.

The `Castor\MessageBus\Handler\ContainerLocator` obtains the handler from a 
`Psr\Container\ContainerInterface` instance. The objects returned from the container
must be either an invokable class that takes the raw message as the first argument,
or an instance of `Castor\MessageBus\Handler`.

Instantiating this middleware with the defaults its very easy:

```php
use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(MessageBus\HandleMessage::usingContainer($container));
```

If you pass custom implementations, you can do it using the constructor:

```php
<?php

use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(new MessageBus\HandleMessage(
    new MyCustomArrayInflector(),
    new MyCustomLocator()
));
```

> NOTE: We highly recommend the use of the Container Locator as it makes fetching
> handlers very easily and efficient, specially if your container makes use of
> reflection. Also, some sort of convention between a message and its handler
> would be beneficial to avoid maintaining maps of messages to handlers, reducing
> application maintenance costs.

## `Castor\MessageBus\HandleMultiple` middleware.

It's common practice that message buses execute messages inside a database transaction, which
is usually handled in a transactional middleware. In some cases though, you might
want to execute multiple messages in a single transaction.

This message bus provides this middleware for that reason. You can wrap your messages
in the `Castor\MessgeBus\Multi` class and pass that class as the message. Then,
inject the `Castor\MessageBus\ExecuteMultiple` middleware whenever you want the
commands to be processed individually. They will be passed one by one to the same
stack point.

So, in this code example:

```php
<?php

use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(new TransactionalMiddleware());
$bus->add(new Castor\MessageBus\HandleMultiple());
$bus->add(new HandleMessage());

// All these three messages will be executed in the same database transaction,
// since they are past the TransactionalMiddleware.
$bus->handle(MessageBus\Multi::wrap([new MessageOne(), new MessageTwo(), new MessageThree()]));

// HandleMessage will never receive the Multi class, but each of the wrapped messages
// in order of insertion.
```

Keep in mind that any middleware you have in between the handling of a message and
the `HandleMultiple` middleware will be executed one time for every wrapped message
in the `Castor\MessgeBus\Multi` class.

## `Castor\MessageBus\ClosureMiddleware` middleware.

This middleware is designed to avoid instantiating a full object graph in a
middleware chain by deferring the instantiation of it until it has been reached
in the execution stack. This is what is known as lazy-loading.

It is best used with a `Psr\Container\ContainerInterface`, but you can also pass
a custom factory as a `Closure` in the constructor.

```php
<?php

use Castor\MessageBus;

$bus = new MessageBus();
$bus->add(MessageBus\ClosureMiddleware::lazy('service-name', $container));
$bus->add(new MessageBus\ClosureMiddleware(function () {
    return new CustomMiddleware();
}));
```

> NOTE: Bear in mind that the closure is executed every time the middleware is
> used, potentially returning a new instance every time. It does not save any
> kind of internal state or performs any memoization for you; you must do that
> in userland.
> 
> If you use a container, make sure you cache resolutions if you want to get the
> same instance every time.

# On Middleware and Envelopes

Envelopes are designed to wrap original messages into other classes that can be
used by third party middleware to handle that message in a particular way.
For instance, the `castor/async-message-bus` package implements the 
`Castor\MessageBus\Async` envelope. This envelope wraps a message and keeps some
state in it, like the queue where is going to be executed and the number of times it
has been put on the queue. The middleware uses the `Castor\MessageBus\Envelope` public
api to find the envelope and obtain information about it.

Every envelope must extend the base `Castor\MessageBus\Envelope` class because it
contains the base methods other middleware can rely upon to extract the original
message or find the envelope class corresponding to their middleware.