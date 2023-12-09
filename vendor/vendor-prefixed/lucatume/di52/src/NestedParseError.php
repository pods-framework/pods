<?php
/**
 * Class used to handle the special case of a parse error in a nested dependency
 * and bubble its nature up.
 *
 * @package Pods\Prefixed\lucatume\DI52;
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52;

use Throwable;

/**
 * Class NestedParseError.
 *
 * @package Pods\Prefixed\lucatume\DI52;
 */
class NestedParseError extends \Exception
{
    /**
     * The type of the entity being loaded.
     *
     * @var string
     */
    private $type;

    /**
     * The name of the entity being loaded.
     *
     * @var string
     */
    private $name;

    /**
     * NestedParseError constructor.
     *
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param Throwable|null $previous The previous exception used for the exception chaining.
     * @param string $type The type of the entity being loaded.
     * @param string $name The name of the entity being loaded.
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $type = '', $name = '')
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Returns the type of the entity being loaded.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the name of the entity being loaded.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
