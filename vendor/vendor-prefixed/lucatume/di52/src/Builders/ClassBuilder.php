<?php
/**
 * Builds and returns object instances.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52\Builders;

use Pods\Prefixed\lucatume\DI52\ContainerException;
use Pods\Prefixed\lucatume\DI52\NotFoundException;
use ReflectionMethod;

/**
 * Class ClassBuilder
 *
 * @package Pods\Prefixed\lucatume\DI52\Builders
 */
class ClassBuilder implements BuilderInterface, ReinitializableBuilderInterface
{
    /**
     * An array cache of resolved constructor parameters, shared across all instances of the builder.
     * @var array<string,array<Parameter>>
     */
    protected static $constructorParametersCache = [];
    /**
     * A set of arguments that will be passed to the class constructor.
     *
     * @var array<mixed>
     */
    protected $buildArgs;
    /**
     * The id associated with the builder by the resolver.
     * @var string
     */
    protected $id;
    /**
     * The fully-qualified class name the builder should build instances of.
     *
     * @var string
     */
    protected $className;
    /**
     * A set of methods to call on the built object.
     *
     * @var array<string>|null
     */
    protected $afterBuildMethods;

    /**
     * A reference to the resolver currently using the builder.
     *
     * @var Resolver
     */
    protected $resolver;

    /**
     * Whether the $className is an implementation of $id
     * and $id is an interface.
     *
     * @var bool
     */
    protected $isInterface = false;

    /**
     * ClassBuilder constructor.
     *
     * @param string             $id                The identifier associated with this builder.
     * @param Resolver           $resolver          A reference to the resolver currently using the builder.
     * @param string             $className         The fully-qualified class name to build instances for.
     * @param array<string>|null $afterBuildMethods An optional set of methods to call on the built object.
     * @param mixed              ...$buildArgs      An optional set of build arguments that should be provided to the
     *                                              class constructor method.
     *
     * @throws NotFoundException If the class does not exist.
     */
    public function __construct($id, Resolver $resolver, $className, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (!class_exists($className)) {
            throw new NotFoundException(
                "nothing is bound to the '{$className}' id and it's not an existing or instantiable class."
            );
        }

        $interfaces = class_implements($className);

        if ($interfaces && isset($interfaces[$id])) {
            $this->isInterface = true;
        }

        $this->id = $id;
        $this->className = $className;
        $this->afterBuildMethods = $afterBuildMethods;
        $this->resolver = $resolver;
        $this->buildArgs = $buildArgs;
    }

    /**
     * Builds and returns an instance of the class.
     *
     * @return object An instance of the class.
     */
    public function build()
    {
        $constructorArgs = $this->resolveConstructorParameters();
        $built = new $this->className(...$constructorArgs);
        foreach ((array)$this->afterBuildMethods as $afterBuildMethod) {
            $built->{$afterBuildMethod}();
        }
        return $built;
    }

    /**
     * Resolves the constructor arguments to concrete implementations or values.
     *
     * @return array<mixed> A set of resolved constructor arguments.
     *
     * @throws ContainerException If a constructor argument resolution raises issues.
     */
    protected function resolveConstructorParameters()
    {
        $constructorArgs = [];

        /** @var Parameter $parameter */
        foreach ($this->getResolvedConstructorParameters($this->className) as $i => $parameter) {
            $this->resolver->addToBuildLine((string)$parameter->getType(), $parameter->getName());
            if (isset($this->buildArgs[$i])) {
                $arg = $this->buildArgs[$i];
                if ($arg instanceof BuilderInterface) {
                    $constructorArgs[] = $arg->build();
                    continue;
                }

                $constructorArgs[] = $this->resolveBuildArg($this->buildArgs[$i]);
                continue;
            }

            $constructorArgs [] = $this->resolveParameter($parameter);
            $this->resolver->buildLinePop();
        }

        return $constructorArgs;
    }

    /**
     * Returns a set of resolved constructor parameters.
     *
     * @param string $className The fully-qualified class name to get the resolved constructor parameters yet.
     * @return array<Parameter> A set of resolved constructor parameters.
     *
     * @throws ContainerException If the resolution of any constructor parameters is problematic.
     */
    protected function getResolvedConstructorParameters($className)
    {
        if (isset(self::$constructorParametersCache[$className])) {
            return self::$constructorParametersCache[$className];
        }

        try {
            $constructorReflection = new ReflectionMethod($className, '__construct');
        } catch (\ReflectionException $e) {
            static::$constructorParametersCache[$className] = [];
            // No constructor method, no args.
            return [];
        }

        if (!$constructorReflection->isPublic()) {
            throw new ContainerException("constructor method is not public.");
        }

        $parameters = [];

        foreach ($constructorReflection->getParameters() as $i => $reflectionParameter) {
            $parameters[] = new Parameter($i, $reflectionParameter);
        }

        self::$constructorParametersCache[$className] = $parameters;

        return $parameters;
    }

    /**
     * Resolves a build argument to a concrete implementation.
     *
     * @param mixed $arg The argument id or value to resolve.
     *
     * @return mixed The resolved build argument.
     */
    protected function resolveBuildArg($arg)
    {
        if (is_string($arg) && ($this->resolver->isBound($arg) || class_exists($arg))) {
            return $this->resolver->resolve($arg);
        }
        return $arg;
    }

    /**
     * Resolves a parameter to a concrete implementation or value.
     *
     * @param Parameter $parameter The parameter to resolve.
     *
     * @return mixed The resolved parameter.
     *
     * @throws ContainerException If the parameter resolution fails.
     */
    protected function resolveParameter(Parameter $parameter)
    {
        $paramClass = $parameter->getClass();

        if ($paramClass) {
            $parameterImplementation = $this->resolver->whenNeedsGive($this->id, $paramClass);
        } elseif ($this->isInterface) {
            $name = $parameter->getName();
            // If an interface was requested, resolve the underlying concrete class instead.
            $parameterImplementation = $this->resolver->whenNeedsGive($this->className, "\$$name");
        } else {
            $name = $parameter->getName();
            $parameterImplementation = $this->resolver->whenNeedsGive($this->id, "\$$name");
        }

        try {
            return $parameterImplementation instanceof BuilderInterface ?
                $parameterImplementation->build()
                : $this->resolver->resolve($parameterImplementation);
        } catch (NotFoundException $e) {
            return $parameter->getDefaultValueOrFail();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reinit(array $afterBuildMethods = null, ...$buildArgs)
    {
        $this->afterBuildMethods = $afterBuildMethods;
        $this->buildArgs = $buildArgs;
    }
}
