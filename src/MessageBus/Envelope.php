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

/**
 * An Envelope wraps a message.
 *
 * This allows to decorate the original message with extra data the middlewares
 * in a MessageBus can use to behave differently.
 */
abstract class Envelope
{
    private object $message;

    /**
     * Envelope constructor.
     */
    public function __construct(object $message)
    {
        $this->message = $message;
    }

    /**
     * Open returns a wrapped message instance of the passed class.
     *
     * If the instance is not found then $this is returned.
     *
     * @psalm-param class-string $instance;
     */
    public function open(string $instance): object
    {
        $message = $this;
        while ($message instanceof self) {
            if ($message instanceof $instance) {
                return $message;
            }
            $message = $message->getInnerMessage();
        }

        return $this;
    }

    /**
     * Returns the message in the bottom of the wrapping chain.
     *
     * It will recursively enter into multiple envelopes.
     */
    public function unwrap(): object
    {
        $message = $this;
        while ($message instanceof self) {
            $message = $message->getInnerMessage();
        }

        return $message;
    }

    /**
     * Returns the inner wrapped message.
     */
    public function getInnerMessage(): object
    {
        return $this->message;
    }
}
