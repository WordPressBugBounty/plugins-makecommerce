<?php
/**
 * @license MIT
 *
 * Modified by makecommerce on 03-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MakeCommercePrefix\GuzzleHttp\Psr7\Exception;

use InvalidArgumentException;

/**
 * Exception thrown if a URI cannot be parsed because it's malformed.
 */
class MalformedUriException extends InvalidArgumentException
{
}
