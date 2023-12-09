<?php
/**
 * Resolves ids (string, class names or mixed values) to values with auto-wiring.
 *
 * @package Pods\Prefixed\lucatume\DI52\Builders
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52\Builders;

use Pods\Prefixed\lucatume\DI52\NotFoundException;

/**
 * Class Resolver
 *
 * @package Pods\Prefixed\lucatume\DI52\Builders
 */
class Resolver
{
    /**
     * A map from ids bound in the container to their builder or resolved value.
     *
     * @var array<string,BuilderInterface|mixed>
     */
    protected $bindings = [];

    /**
     * A flag property to indicate whether implicit bindings, those discovered during auto-wiring resolution, should
     * be bound as prototype or singleton bindings.
     *
     * @var bool
     */
    protected $resolveUnboundAsSingletons = false;

    /**
     * A map from ids bound in the container to their singleton nature.
     *
     * @var array<string,bool>
     */
    protected $singletons = [];

    /**
     * A map of when-needs-give specifications.
     * @var array<string,array<string,BuilderInterface>>
     */
    protected $whenNeedsGive = [];
    /**
     * The current build line, a list from the trunk to the leaf of the current resolution.
     *
     * @var array<string>
     */
    protected $buildLine = [];

    /**
     * Resolver constructor.
     *
     * @param false $resolveUnboundAsSingletons Whether implicit bindings, those discovered during auto-wiring
     *                                          resolution, should be bound as prototype or singleton bindings.
     */
    public function __construct($resolveUnboundAsSingletons = false)
    {
        $this->resolveUnboundAsSingletons = $resolveUnboundAsSingletons;
    }

    /**
     * Binds an implementation for an id, or class name, as prototype (build new each time).
     *
     * @param string           $id             The id to register the implementation for.
     * @param BuilderInterface $implementation The builder that will provide the implementation for the id.
     *
     * @return void This method does not return any value.
     */
    public function bind($id, BuilderInterface $implementation)
    {
        unset($this->singletons[$id]);
        $this->bindings[$id] = $implementation;
    }

    /**
     * Registers an implementation for an id, or class name, as singleton (build at most once).
     *
     * @param string           $id             The id to register the implementation for.
     * @param BuilderInterface $implementation The builder that will provide the implementation for
     *                                         the id.
     *
     * @return void This method does not return any value.
     */
    public function singleton($id, BuilderInterface $implementation)
    {
        $this->singletons[$id] = true;
        $this->bindings[$id] = $implementation;
    }

    /**
     * Returns whether an implementation was registered for the id in the resolver or not.
     *
     * @param string $id The id to check the implementation for.
     *
     * @return bool Whether an implementation was registered for the id in the resolver or not.
     */
    public function isBound($id)
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Removes the relation between an id and a bound implementation from the resolver.
     *
     * @param string $id The id to unregister the implementation for.
     *
     * @return void This method does not return any value.
     */
    public function unbind($id)
    {
        unset($this->bindings[$id]);
    }

    /**
     * Returns whether a specific id is bound as singleton (build at most once), or not.
     *
     * @param string $id The id to check.
     *
     * @return bool Whether a specific id is bound as singleton (build at most once), or not.
     */
    public function isSingleton($id)
    {
        return isset($this->singletons[$id]);
    }

    /**
     * Transform the canonical class to the class part of a when-needs-give specification, if required.
     *
     * @param string $id         The ID to resolve the when-needs-give case for.
     * @param string $paramClass The class of the parameter to solve the when-needs-give case for.
     *
     * @return BuilderInterface|string Either the builder for the when-needs-give replacement, or the input parameter
     *                                 class if not found.
     */
    public function whenNeedsGive($id, $paramClass)
    {
        return isset($this->whenNeedsGive[$id][$paramClass]) ?
            $this->whenNeedsGive[$id][$paramClass]
            : $paramClass;
    }

    /**
     * Sets an entry in the when->needs->give chain.
     *
     * @param string           $whenClass  The "when" part of the chain, a class name or id.
     * @param string           $needsClass The "needs" part of the chain, a class name or id.
     * @param BuilderInterface $builder    The Builder instance that should be returned when a class needs the
     *                                     specified id.
     *
     * @return void This method does not return any value.
     */
    public function setWhenNeedsGive($whenClass, $needsClass, BuilderInterface $builder)
    {
        $this->whenNeedsGive[$whenClass][$needsClass] = $builder;
    }

    /**
     * Resolves an ide to an implementation with the input arguments.
     *
     * @param string|mixed       $id                                  The id, class name or built value to resolve.
     * @param array<string>|null $afterBuildMethods                   A list of methods that should run on the built
     *                                                                instance.
     * @param mixed              ...$buildArgs                        A set of build arguments that will be passed to
     *                                                                the implementation constructor.
     * @return BuilderInterface|ReinitializableBuilderInterface|mixed The builder, set up to use the specified set of
     *                                                                build arguments.
     * @throws NotFoundException If the id is a string that does not resolve to an existing, concrete, class.
     */
    public function resolveWithArgs($id, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (! is_string($id)) {
            return $id;
        }

        if (empty($afterBuildMethods) && empty($buildArgs)) {
            return $this->resolve($id);
        }
        return $this->cloneBuilder($id, $afterBuildMethods, ...$buildArgs)->build();
    }

    /**
     * Resolves an id or input value to a value or object instance.
     *
     * @param string|mixed       $id        Either the id of a bound implementation, a class name or an object
     *                                      to resolve.
     * @param array<string>|null $buildLine The build line to append the resolution leafs to, or `null` to use the
     *                                      current one.
     * @return mixed The resolved value or instance.
     *
     * @throws NotFoundException If the id is a string that is not bound and is not an existing, concrete, class.
     */
    public function resolve($id, array $buildLine = null)
    {
        if ($buildLine !== null) {
            $this->buildLine = $buildLine;
        }

        if (! is_string($id)) {
            return $id;
        }

        if (!isset($this->bindings[$id])) {
            return $this->resolveUnbound($id);
        }

        if ($this->bindings[$id] instanceof BuilderInterface) {
            $built = $this->resolveBound($id);
        } else {
            $built = $this->bindings[$id];
        }

        return $built;
    }

    /**
     * Builds, with auto-wiring, an instance of a not bound class.
     *
     * @param string $id The class name to build an instance of.
     *
     * @return object The built class instance.
     *
     * @throws NotFoundException If the id cannot be resolved to an existing, concrete class.
     */
    private function resolveUnbound($id)
    {
        $built = (new ClassBuilder($id, $this, $id))->build();

        if ($this->resolveUnboundAsSingletons) {
            $this->singletons[$id] = true;
            $this->bindings[$id] = $built;
        }

        return $built;
    }

    /**
     * Resolves a bound implementation to a value or object.
     *
     * @param string $id The id to resolve the implementation for.
     *
     * @return mixed The resolved instance.
     */
    private function resolveBound($id)
    {
        // @phpstan-ignore-next-line
        $built = $this->bindings[$id]->build();
        if (isset($this->singletons[$id])) {
            $this->bindings[$id] = $built;
        }
        return $built;
    }

    /**
     * Clones the builder assigned to an id and re-initializes it.
     *
     * The clone operation leverages the already resolved dependencies of a builder to create an up-to-date instance.
     *
     * @param string             $id                The id to clone the builder of.
     * @param array<string>|null $afterBuildMethods A set of methods to run on the built instance.
     * @param mixed              ...$buildArgs      An optional set of arguments that will be passed to the instance
     *                                              constructor.
     * @return BuilderInterface A new instance of the builder currently related to the id.
     *
     * @throws NotFoundException If trying to clone the builder for a non existing id or an id that does not map to a
     *                           concrete class name.
     */
    private function cloneBuilder($id, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (isset($this->bindings[$id]) && $this->bindings[$id] instanceof BuilderInterface) {
            $builder = clone $this->bindings[$id];
            if ($builder instanceof ReinitializableBuilderInterface) {
                $builder->reinit($afterBuildMethods, ...$buildArgs);
            }
        } else {
            $builder = new ClassBuilder($id, $this, $id, $afterBuildMethods, ...$buildArgs);
        }

        return $builder;
    }

    /**
     * Adds an entry to the build line.
     *
     * @param string $type          The type of parameter the Resolver is currently attempting to resolve.
     * @param string $parameterName The name of the parameter in the method signature, if any.
     *
     * @return void This method does not return any value.
     */
    public function addToBuildLine($type, $parameterName)
    {
        $this->buildLine[] = trim("{$type} \${$parameterName}");
    }

    /**
     * Returns the current build line.
     *
     * The build line will return a straight path from the current resolution root to the leaf
     * currently being resolved. Used for error logging and formatting.
     *
     * @return array<string> A set of consecutive items the resolver is currently trying to build.
     */
    public function getBuildLine()
    {
        return $this->buildLine;
    }

    /**
     * Removes the last element from the build line, if any.
     *
     * @return void The method does not return any value.
     */
    public function buildLinePop()
    {
        array_pop($this->buildLine);
    }
}
