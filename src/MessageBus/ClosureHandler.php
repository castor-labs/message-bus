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

use Closure;

/**
 * Class CallableHandler.
 */
final class ClosureHandler implements Handler
{
    private Closure $closure;

    /**
     * CallableHandler constructor.
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public static function make(callable $callable): ClosureHandler
    {
        return new self(Closure::fromCallable($callable));
    }

    public function handle(object $message): void
    {
        ($this->closure)($message);
    }
}
