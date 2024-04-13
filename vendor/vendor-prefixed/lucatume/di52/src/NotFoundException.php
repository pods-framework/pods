<?php
/**
 * An exception used to signal no binding was found for container ID.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 21-February-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52;

use Pods\Prefixed\Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package lucatume\DI52
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
