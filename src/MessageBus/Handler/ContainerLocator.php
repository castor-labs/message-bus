<?php

declare(strict_types=1);

/**
 * @project Castor Message Bus
 * @link https://github.com/castor-labs/message-bus
 * @package castor/message-bus
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 CastorLabs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\MessageBus\Handler;

use Castor\MessageBus\ClosureHandler;
use Castor\MessageBus\Handler;
use Castor\MessageBus\HandlingError;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ContainerLocator.
 */
final class ContainerLocator implements Locator
{
    private ContainerInterface $container;

    /**
     * ContainerLocator constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress InvalidCatch
     *
     * @throws HandlingError
     */
    public function locate(string $handlerName): Handler
    {
        try {
            $handler = $this->container->get($handlerName);
        } catch (NotFoundExceptionInterface $e) {
            throw new HandlerNotFound(sprintf(
                'Could not find handler "%s" in container',
                $handlerName
            ), 0, $e);
        }
        if (is_callable($handler)) {
            $handler = ClosureHandler::make($handler);
        }
        if ($handler instanceof Handler) {
            return $handler;
        }

        throw new HandlingError(sprintf(
            'Handler of class %s must be a callable or implement %s',
            $handlerName,
            Handler::class
        ));
    }
}
