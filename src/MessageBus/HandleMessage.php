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

namespace Castor\MessageBus;

use Castor\MessageBus\Handler\ClassSuffixInflector;
use Castor\MessageBus\Handler\ContainerLocator;
use Psr\Container\ContainerInterface;

/**
 * Class HandleMessage.
 */
final class HandleMessage implements Middleware
{
    private Handler\Inflector $inflector;
    private Handler\Locator $locator;

    /**
     * HandleMessage constructor.
     */
    public function __construct(Handler\Inflector $inflector, Handler\Locator $locator)
    {
        $this->inflector = $inflector;
        $this->locator = $locator;
    }

    public static function usingContainer(ContainerInterface $container, string $suffix = 'Handler'): HandleMessage
    {
        return new self(
            new ClassSuffixInflector($suffix),
            new ContainerLocator($container)
        );
    }

    /**
     * @throws Handler\HandlerNotFound
     * @throws Handler\InflectionError
     */
    public function process(object $message, Stack $stack): void
    {
        if ($message instanceof Envelope) {
            $message = $message->unwrap();
        }

        $handlerName = $this->inflector->inflect($message);
        $handler = $this->locator->locate($handlerName);
        $handler->handle($message);
    }
}
