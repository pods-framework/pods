<?php
/**
 * Callable-based builder.
 *
 * @package Pods\Prefixed\lucatume\DI52\Builders
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52\Builders;

use Pods\Prefixed\lucatume\DI52\Container;

/**
 * Class CallableBuilder
 *
 * @package Pods\Prefixed\lucatume\DI52\Builders
 */
class CallableBuilder implements BuilderInterface, ReinitializableBuilderInterface
{
    /**
     * An instance of the DI Container.
     *
     * @var Container The
     */
    protected $container;

    /**
     * The callable this builder will use.
     *
     * @var callable
     */
    protected $callable;
    /**
     * An array of method that will be called on the built object.
     *
     * @var array<string>|null
     */
    protected $afterBuildMethods;
    /**
     * An array of arguments that will be passed as input to the callable method.
     *
     * @var array<mixed>
     */
    protected $buildArgs;

    /**
     * CallableBuilder constructor.
     *
     * @param Container          $container         An instance of the DI Container.
     * @param callable           $callable          The builder callable.
     * @param array<string>|null $afterBuildMethods A set of methods to call on the built instance.
     * @param mixed              ...$buildArgs      A set of optional arguments for the callable method.
     */
    public function __construct(
        Container $container,
        callable $callable,
        array $afterBuildMethods = null,
        ...$buildArgs
    ) {
        $this->container = $container;
        $this->callable = $callable;
        $this->afterBuildMethods = $afterBuildMethods ?: [];
        $this->buildArgs = $buildArgs;
    }

    /**
     * Calls the callable for the builder and returns its value.
     *
     * @return mixed The built implementation.
     */
    public function build()
    {
        $built = call_user_func($this->callable, ...$this->buildArgs);

        foreach ((array)$this->afterBuildMethods as $afterBuildMethod) {
            $built->{$afterBuildMethod}();
        }

        return $built;
    }

    /**
     * Reinitialize the builder setting the after build methods and build args.
     *
     * @param array<string>|null $afterBuildMethods A set of methods to call on the object after it's built.
     * @param mixed              ...$buildArgs      A set of build arguments that will be passed to the constructor.
     *
     * @return void This method does not return any value.
     */
    public function reinit(array $afterBuildMethods = null, ...$buildArgs)
    {
        $this->afterBuildMethods = $afterBuildMethods ?: [];
        $this->buildArgs = $buildArgs;
    }
}
