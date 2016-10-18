<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin;

/**
 * Basic exception for issues arising in the client API.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DirectAdminException extends \RuntimeException
{
    /**
     * Construct the exception object.
     *
     * @param string $message The Exception message to throw
     * @param int $code The Exception code
     * @param \Exception|null $previous The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
