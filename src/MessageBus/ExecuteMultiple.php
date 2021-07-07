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
 * Class ExecuteMultiple allows you to execute multiple commands at the same
 * stack frame.
 *
 * This is useful, for instance, if you want to execute multiple commands in
 * the same database transaction. You just need to place this middleware after
 * the transactional one.
 */
final class ExecuteMultiple implements Middleware
{
    public function process(object $message, Stack $stack): void
    {
        $multi = $message;
        if ($message instanceof Envelope) {
            $multi = $message->unwrap();
        }

        if (!$multi instanceof Multi) {
            $stack->next()->handle($message);

            return;
        }
        foreach ($multi as $msg) {
            $stack->next()->handle($msg);
        }
    }
}
