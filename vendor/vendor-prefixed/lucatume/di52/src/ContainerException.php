<?php
/**
 * An exception thrown while trying to build or resolve a binding in the container.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52;

use Exception;
use Pods\Prefixed\Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use Throwable;

/**
 * Class ContainerException
 *
 * @package lucatume\DI52
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * Extracts a property from an object.
     *
     * @param object $object The object to extract the property from.
     * @param string $property The property to extract.
     *
     * @return mixed The property value if found, `null` otherwise.
     */
    private static function getPropertyValue($object, $property)
    {
        $reflectionClass = new ReflectionClass($object);

        do {
            if ($reflectionClass->hasProperty($property)) {
                $traceProperty = $reflectionClass->getProperty($property);
                $traceProperty->setAccessible(true);
                return $traceProperty->getValue($object);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass instanceof ReflectionClass);

        return null;
    }

    /**
     * Sets a private or protected property on an object.
     *
     * @param object $object The object to set the property on.
     * @param string $property The property to set.
     * @param mixed $value The value to set.
     *
     * @return bool Whether the property was set or not.
     */
    private static function setPropertyValue($object, $property, $value)
    {
        $reflectionClass = new ReflectionClass($object);

        do {
            if ($reflectionClass->hasProperty($property)) {
                $traceProperty = $reflectionClass->getProperty($property);
                $traceProperty->setAccessible(true);
                $traceProperty->setValue($object, $value);
                return true;
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass instanceof ReflectionClass);

        return false;
    }

    /**
     * Formats an error message to provide a useful debug message.
     *
     * @param string|object $id The id of what is actually being built or the object that is being built.
     * @param Exception|Throwable $thrown The original exception thrown while trying to make the target.
     * @param array<string> $buildLine A set of consecutive items the resolver is currently trying to build.
     *
     * @return string The formatted make error message.
     */
    private static function makeBuildLineErrorMessage($id, $thrown, array $buildLine)
    {
        $idString = is_string($id) ? $id : gettype($id);
        if ($thrown instanceof NestedParseError) {
            $last = $thrown->getType() . ' $' . $thrown->getName();
        } else {
            $last = array_pop($buildLine) ?: $idString;
        }
        $lastEntry = "Error while making {$last}: " . lcfirst(
            rtrim(
                str_replace('"', '', $thrown->getMessage()),
                '.'
            )
        ) . '.';
        $frags = array_merge($buildLine, [$lastEntry]);

        return implode("\n\t=> ", $frags);
    }

    /**
     * Builds a container exception from a throwable.
     *
     * @param string|object $id The id of what is actually being built or the object that is being built.
     * @param Exception|Throwable $thrown The throwable to build the exception from.
     * @param int $maskThrowables The bitmask of throwable properties to mask.
     * @param array<string> $buildLine A set of consecutive items the resolver is currently trying to build.
     *
     * @return ContainerException The built Container exception.
     */
    public static function fromThrowable($id, $thrown, $maskThrowables, array $buildLine)
    {
        $message = ($maskThrowables & Container::EXCEPTION_MASK_MESSAGE) ?
            self::makeBuildLineErrorMessage($id, $thrown, $buildLine)
            : $thrown->getMessage();

        $exceptionClass = $thrown instanceof self ?
            get_class($thrown)
            : self::class;

        $built = new $exceptionClass($message, $thrown->getCode(), $thrown);

        if (($maskThrowables & Container::EXCEPTION_MASK_FILE_LINE)
            && ($thrownFile = self::getPropertyValue($thrown, 'file'))
            && ($thrownLine = self::getPropertyValue($thrown, 'line'))
        ) {
            self::setPropertyValue($built, 'file', $thrownFile);
            self::setPropertyValue($built, 'line', $thrownLine);
        }

        return $built;
    }
}
