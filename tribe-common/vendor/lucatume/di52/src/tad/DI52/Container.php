<?php

class tad_DI52_Container implements ArrayAccess {

	/**
	 * @var boolean
	 */
	protected $useClosures;

	/**
	 * @var array
	 */
	protected $callbacks = array();

	/**
	 * @var array
	 */
	protected $protected = array();

	/**
	 * @var array
	 */
	protected $strings = array();

	/**
	 * @var array
	 */
	protected $objects = array();

	/**
	 * @var array
	 */
	protected $callables = array();

	/**
	 * @var array
	 */
	protected $singletons = array();

	/**
	 * @var array
	 */
	protected $deferred = array();

	/**
	 * @var array
	 */
	protected $chains = array();

	/**
	 * @var array
	 */
	protected $reflections = array();

	/**
	 * @var array
	 */
	protected $afterbuild = array();

	/**
	 * @var string
	 */
	protected $resolving = '';

	/**
	 * @var array
	 */
	protected $tags = array();

	/**
	 * @var array
	 */
	protected $bootable = array();

	/**
	 * @var array
	 */
	protected $contexts = array();

	/**
	 * @var string
	 */
	protected $bindingFor;

	/**
	 * @var string
	 */
	protected $neededImplementation;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $bindings = array();

	/**
	 * @var array
	 */
	protected $instanceCallbacks = array();

	/**
	 * @var array
	 */
	public $__instanceCallbackArgs = array();

	/**
	 * @var array
	 */
	protected $dependants = array();

	/**
	 * tad_DI52_Container constructor.
	 */
	public function __construct() {
		$this->id = uniqid(rand(1, 9999));
		$GLOBALS['__container_' . $this->id] = $this;
	}

	/**
	 * Sets a variable on the container.
	 *
	 * Variables will be evaluated before storing, to protect a variable from the process, e.g. storing a closure, use
	 * the `protect` method:
	 *
	 *        $container->setVar('foo', $container->protect($f));
	 *
	 * @see tad_DI52_Container::protect()
	 *
	 * @param string $key The alias the container will use to reference the variable.
	 * @param mixed $value The variable value.
	 */
	public function setVar($key, $value) {
		$this->offsetSet($key, $value);
	}

	/**
	 * Sets a variable on the container using the ArrayAccess API.
	 *
	 * When using the container as an array bindings will be bound as singletons; the two functions below are
	 * equivalent:
	 *
	 *        $container->singleton('foo','ClassOne');
	 *        $container['foo'] = 'ClassOne';
	 *
	 * Variables will be evaluated before storing, to protect a variable from the process, e.g. storing a closure, use
	 * the `protect` method:
	 *
	 *        $container['foo'] = $container->protect($f));
	 *
	 * @see   tad_DI52_Container::protect()
	 * @see   tad_DI52_Container::singleton()
	 *
	 * @param string $key The alias the container will use to reference the variable.
	 * @param mixed $value The variable value.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value) {
		if ($value instanceof tad_DI52_ProtectedValue) {
			$this->protected[$offset] = true;
			$value = $value->getValue();
		}

		$this->offsetUnset($offset);

		$this->singletons[$offset] = $offset;

		if (isset($this->protected[$offset])) {
			$this->strings[$offset] = $value;
			return;
		}

		if (is_callable($value)) {
			$this->callables[$offset] = $value;
			return;
		}

		if (is_object($value)) {
			$this->objects[$offset] = $value;
			return;
		}

		$this->strings[$offset] = $value;
	}

	/**
	 * Returns a variable stored in the container.
	 *
	 * If the variable is a binding then the binding will be resolved before returning it.
	 *
	 * @see tad_DI52_Container::make
	 *
	 * @param string $key The alias of the variable or binding to fetch.
	 *
	 * @return mixed The variable value or the resolved binding.
	 */
	public function getVar($key) {
		try {
			return $this->offsetGet($key);
		} catch (RuntimeException $e) {
			return null;
		}
	}

	/**
	 * Retrieves a variable or a binding from the database.
	 *
	 * If the offset is bound to an implementation then it will be resolved before returning it.
	 * * @param string $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset) {
		if (is_object($offset)) {
			return is_callable($offset) ? call_user_func($offset, $this) : $offset;
		}

		if (isset($this->objects[$offset])) {
			return $this->objects[$offset];
		}

		if (isset($this->strings[$offset])) {
			if (isset($this->protected[$offset])) {
				return $this->strings[$offset];
			}
			if (is_string($this->strings[$offset]) && class_exists($this->strings[$offset])) {
				$instance = $this->make($this->strings[$offset]);
				$this->objects[$offset] = $instance;
				return $instance;
			}
			return $this->strings[$offset];
		}

		if (isset($this->callables[$offset])) {
			return call_user_func($this->callables[$offset], $this);
		}

		if (is_string($offset) && class_exists($offset)) {
			return $this->resolve($offset);
		}

		throw new RuntimeException("Nothing is bound to the key '{$offset}'");
	}

	/**
	 * Returns an instance of the class or object bound to an interface, class  or string slug if any, else it will try
	 * to automagically resolve the object to a usable instance.
	 *
	 * If the implementation has been bound as singleton using the `singleton` method
	 * or the ArrayAccess API then the implementation will be resolved just on the first request.
	 *
	 * @param string $classOrInterface A fully qualified class or interface name.
	 *
	 * @return mixed
	 */
	public function make($classOrInterface) {
		if (is_object($classOrInterface)) {
			return $classOrInterface;
		}

		if (!isset($this->bindings[$classOrInterface])) {
			try {
				return $this->build($classOrInterface, true);
			} catch (Exception $e) {
				// continue... we tried an early resolution
			}
		}

		if (isset($this->objects[$classOrInterface])) {
			return $this->objects[$classOrInterface];
		}

		if (isset($this->callables[$classOrInterface])) {
			$resolved = call_user_func($this->callables[$classOrInterface], $this);
		} else {
			$resolved = $this->resolve($classOrInterface);
		}

		if (isset($this->singletons[$classOrInterface])) {
			$this->objects[$classOrInterface] = $resolved;
		}

		return $resolved;
	}

	/**
	 * Returns an instance of the class or object bound to an interface, class  or string slug if any, else it will try
	 * to automagically resolve the object to a usable instance.
	 *
	 * Differently from the `make` method singleton implementations will be be ignored.
	 *
	 * @param string $classOrInterface
	 *
	 * @throws RuntimeException|ReflectionException
	 *
	 * @return array|mixed
	 */
	protected function resolve($classOrInterface) {
		$original = $this->resolving;
		$this->resolving = $classOrInterface;

		try {
			if (isset($this->deferred[$classOrInterface])) {
				/** @var tad_DI52_ServiceProviderInterface $provider */
				$provider = $this->deferred[$classOrInterface];
				$provider->register();
			}

			if (!isset($this->strings[$classOrInterface])) {
				try {
					$instance = $this->build($classOrInterface);
				} catch (Exception $e) {
					if ( $e instanceof ReflectionException ) {
						throw $e;
					} elseif ( $e instanceof RuntimeException ) {
						throw $e;
					}

					throw new RuntimeException("'{$classOrInterface}' is not a bound alias or an existing class.");
				}
			} else {
				if (isset($this->chains[$classOrInterface])) {
					$instance = $this->buildFromChain($classOrInterface);
				} else {
					$instance = $this->build($this->strings[$classOrInterface]);
				}
			}

			if (isset($this->afterbuild[$classOrInterface])) {
				foreach ($this->afterbuild[$classOrInterface] as $method) {
					call_user_func(array($instance, $method));
				}
			}

			$this->resolving = $original;

			return $instance;
		} catch (Exception $e) {
			preg_match('/Error while making/', $e->getMessage(), $matches);
			if (count($matches)) {
				// @codeCoverageIgnoreStart
				$separator = "\n\t =>";
				$prefix    = '';
				// @codeCoverageIgnoreEnd
			} else {
				$separator = ':';
				$prefix    = 'Error while making ';
			}
			$message = "{$prefix}'{$classOrInterface}'{$separator} " . $e->getMessage();

			throw new RuntimeException($message);
		}
	}

	/**
	 * @param      $implementation
	 * @param bool $resolving
	 *
	 * @return mixed
	 */
	protected function build($implementation, $resolving = false) {
		$this->resolving = $resolving ? $implementation : $this->resolving;
		if (!isset($this->reflections[$implementation])) {
			$this->reflections[$implementation] = new ReflectionClass($implementation);
		}

		/** @var ReflectionClass $classReflection */
		$classReflection = $this->reflections[$implementation];
		$constructor = $classReflection->getConstructor();
		$parameters = empty($constructor) ? array() : $constructor->getParameters();
		$builtParams = array_map(array($this, '_getParameter'), $parameters);

		$instance = !empty($builtParams) ?
			$this->reflections[$implementation]->newInstanceArgs($builtParams)
			: new $implementation;

		return $instance;
	}

	/**
	 * @param string $classOrInterface
	 *
	 * @return mixed
	 */
	protected function buildFromChain($classOrInterface) {
		$chainElements = $this->chains[$classOrInterface];
		unset($this->chains[$classOrInterface]);

		$instance = null;
		foreach (array_reverse($chainElements) as $element) {
			$instance = $this->resolve($element);
			$this->objects[$classOrInterface] = $instance;
		}

		$this->chains[$classOrInterface] = $chainElements;
		unset($this->objects[$classOrInterface]);

		return $instance;
	}

	/**
	 * Tags an array of implementations bindings for later retrieval.
	 *
	 * The implementations can also reference interfaces, classes or string slugs.
	 * Example:
	 *
	 *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
	 *
	 * @see tad_DI52_Container::tagged()
	 *
	 * @param array $implementationsArray
	 * @param string $tag
	 */
	public function tag(array $implementationsArray, $tag) {
		$this->tags[$tag] = $implementationsArray;
	}

	/**
	 * Retrieves an array of bound implementations resolving them.
	 *
	 * The array of implementations should be bound using the `tag` method:
	 *
	 *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
	 *        foreach($container->tagged('endpoints') as $endpoint){
	 *            $endpoint->register();
	 *        }
	 *
	 * @see tad_DI52_Container::tag()
	 *
	 * @param string $tag
	 *
	 * @return array An array of resolved bound implementations.
	 */
	public function tagged($tag) {
		if ($this->hasTag($tag)) {
			return array_map(array($this, 'offsetGet'), $this->tags[$tag]);
		}

		throw new RuntimeException("Nothing has been tagged {$tag}.");
	}

	/**
	 * Checks whether a tag group exists in the container.
	 *
	 * @see tad_DI52_Container::tag()
	 *
	 * @param string $tag
	 *
	 * @return bool
	 */
	public function hasTag($tag) {
		return isset($this->tags[$tag]);
	}

	/**
	 * Registers a service provider implementation.
	 *
	 * The `register` method will be called immediately on the service provider.
	 *
	 * If the provider overloads the  `isDeferred` method returning a truthy value then the `register` method will be
	 * called only if one of the implementations provided by the provider is requested. The container defines which
	 * implementations is offering overloading the `provides` method; the method should return an array of provided
	 * implementations.
	 *
	 * If a provider overloads the `boot` method that method will be called when the `boot` method is called on the
	 * container itself.
	 *
	 * @see tad_DI52_ServiceProviderInterface::register()
	 * @see tad_DI52_ServiceProviderInterface::isDeferred()
	 * @see tad_DI52_ServiceProviderInterface::provides()
	 * @see tad_DI52_ServiceProviderInterface::boot()
	 *
	 * @param string $serviceProviderClass
	 */
	public function register($serviceProviderClass) {
		/** @var tad_DI52_ServiceProviderInterface $provider */
		$provider = new $serviceProviderClass($this);
		if (!$provider->isDeferred()) {
			$provider->register();
		} else {
			$provided = $provider->provides();

			$count = count($provided);
			if ($count === 0) {
				throw new RuntimeException("Service provider '{$serviceProviderClass}' is marked as deferred but is not providing any implementation.");
			}

			$this->bindings = array_merge($this->bindings, array_combine($provided, $provided));
			$this->deferred = array_merge($this->deferred,
				array_combine($provided, array_fill(0, $count, $provider)));
		}
		$ref = new ReflectionMethod($provider, 'boot');
		$requiresBoot = ($ref->getDeclaringClass()->getName() === get_class($provider));
		if ($requiresBoot) {
			$this->bootable[] = $provider;
		}
	}

	/**
	 * Boots up the application calling the `boot` method of each registered service provider.
	 *
	 * If there are bootable providers (providers overloading the `boot` method) then the `boot` method will be
	 * called on each bootable provider.
	 *
	 * @see tad_DI52_ServiceProviderInterface::boot()
	 */
	public function boot() {
		if (!empty($this->bootable)) {
			foreach ($this->bootable as $provider) {
				/** @var tad_DI52_ServiceProviderInterface $provider */
				$provider->boot();
			}
		}
	}

	/**
	 * Checks whether an interface, class or string slug has been bound in the container.
	 *
	 * @param string $classOrInterface
	 *
	 * @return bool
	 */
	public function isBound($classOrInterface) {
		return $this->offsetExists($classOrInterface);
	}

	/**
	 * Whether a offset exists
	 *
	 * @see   isBound
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($offset) {
		return isset($this->bindings[$offset]);
	}

	/**
	 * Binds a class, interface or string slug to a chain of implementations decorating a base
	 * object; the chain will be lazily resolved only on the first call.
	 *
	 * The base decorated object must be the last element of the array.
	 *
	 * @param string $classOrInterface The class, interface or slug the decorator chain should be bound to.
	 * @param array $decorators An array of implementations that decorate an object.
	 * @param array $afterBuildMethods An array of methods that should be called on the instance after it has been
	 *                                  built; the methods should not require any argument.
	 */
	public function singletonDecorators($classOrInterface, $decorators, array $afterBuildMethods = null) {
		$this->bindDecorators($classOrInterface, $decorators, $afterBuildMethods);
		$this->singletons[$classOrInterface] = $classOrInterface;
	}

	/**
	 * Binds a class, interface or string slug to to a chain of implementations decorating a
	 * base object.
	 *
	 * The base decorated object must be the last element of the array.
	 *
	 * @param string $classOrInterface The class, interface or slug the decorator chain should be bound to.
	 * @param array $decorators An array of implementations that decorate an object.
	 * @param array $afterBuildMethods An array of methods that should be called on the instance after it has been
	 *                                  built; the methods should not require any argument.
	 */
	public function bindDecorators($classOrInterface, array $decorators, array $afterBuildMethods = null) {
		$this->bindings[$classOrInterface] = $classOrInterface;
		$this->strings[$classOrInterface] = $decorators;
		$this->chains[$classOrInterface] = $decorators;

		if (!empty($afterBuildMethods)) {
			$base = end($decorators);
			$this->afterbuild[$base] = $afterBuildMethods;
		}
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($offset) {
		unset(
			$this->strings[$offset],
			$this->singletons[$offset],
			$this->objects[$offset],
			$this->bindings[$offset],
			$this->afterbuild[$offset],
			$this->callables[$offset],
			$this->contexts[$offset],
			$this->tags[$offset],
			$this->chains[$offset]
		);
	}

	/**
	 * Starts the `when->needs->give` chain for a contextual binding.
	 *
	 * @param string $class The fully qualified name of the requesting class.
	 *
	 * Example:
	 *
	 *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
	 *      $container->singleton('LoggerInterface', 'FilesystemLogger');
	 *      // but if the requesting class is `Worker` return another implementation
	 *      $container->when('Worker')
	 *          ->needs('LoggerInterface)
	 *          ->give('RemoteLogger);
	 *
	 * @return tad_DI52_Container
	 */
	public function when($class) {
		$this->bindingFor = $class;

		return $this;
	}

	/**
	 * Second step of the `when->needs->give` chain for a contextual binding.
	 *
	 * Example:
	 *
	 *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
	 *      $container->singleton('LoggerInterface', 'FilesystemLogger');
	 *      // but if the requesting class is `Worker` return another implementation
	 *      $container->when('Worker')
	 *          ->needs('LoggerInterface)
	 *          ->give('RemoteLogger);
	 *
	 * @param string $classOrInterface The class or interface needed by the class.
	 *
	 * @return tad_DI52_Container
	 */
	public function needs($classOrInterface) {
		$this->neededImplementation = $classOrInterface;
		return $this;
	}

	/**
	 * Third step of the `when->needs->give` chain for a contextual binding.
	 *
	 * Example:
	 *
	 *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
	 *      $container->singleton('LoggerInterface', 'FilesystemLogger');
	 *      // but if the requesting class is `Worker` return another implementation
	 *      $container->when('Worker')
	 *          ->needs('LoggerInterface)
	 *          ->give('RemoteLogger);
	 *
	 * @param mixed $implementation The implementation specified
	 */
	public function give($implementation) {
		$this->bindings[$this->bindingFor] = $this->bindingFor;

		$this->contexts[$this->neededImplementation] =
			!empty($this->contexts[$this->neededImplementation]) ?
				$this->contexts[$this->neededImplementation] : array();
		$this->contexts[$this->neededImplementation][$this->bindingFor] = $implementation;
	}

	/**
	 * Protects a value from being resolved by the container.
	 *
	 * Example usage `$container['var'] = $container->protect(function(){return 'bar';});`
	 *
	 * @param mixed $value
	 */
	public function protect($value) {
		return new tad_DI52_ProtectedValue($value);
	}

	/**
	 * Binds an interface, a class or a string slug to an implementation.
	 *
	 * Existing implementations are replaced.
	 *
	 * @param string $classOrInterface A class or interface fully qualified name or a string slug.
	 * @param mixed $implementation The implementation that should be bound to the alias(es); can be a class name,
	 *                                  an object or a closure.
	 * @param array $afterBuildMethods An array of methods that should be called on the built implementation after
	 *                                  resolving it.
	 *
	 * @throws ReflectionException      When binding a class that does not exist without defining an implementation.
	 * @throws InvalidArgumentException When binding a class that cannot be instantiated without defining an implementation.
	 */
	public function bind($classOrInterface, $implementation = null, array $afterBuildMethods = null) {
		if (is_null($implementation)) {
			$reflection = new ReflectionClass($classOrInterface);
			if (!$reflection->isInstantiable()) {
				throw new InvalidArgumentException( sprintf('To bind a class in the Container without defining an implementation, the class must be instantiable. %s is not instantiable.', $classOrInterface) );
			}
			$implementation = $classOrInterface;
		}

		$this->offsetUnset($classOrInterface);

		$this->bindings[$classOrInterface] = $classOrInterface;

		if (is_callable($implementation)) {
			$this->callables[$classOrInterface] = $implementation;
			return;
		}

		if (is_object($implementation)) {
			$this->objects[$classOrInterface] = $implementation;
			return;
		}

		$this->strings[$classOrInterface] = $implementation;

		if (!empty($afterBuildMethods)) {
			$this->afterbuild[$classOrInterface] = $afterBuildMethods;
		}
	}

	/**
	 * Binds an interface a class or a string slug to an implementation and will always return the same instance.
	 *
	 * @param string $classOrInterface A class or interface fully qualified name or a string slug.
	 * @param mixed $implementation The implementation that should be bound to the alias(es); can be a class name,
	 *                                  an object or a closure.
	 * @param array $afterBuildMethods An array of methods that should be called on the built implementation after
	 *                                  resolving it.
	 */
	public function singleton($classOrInterface, $implementation = null, array $afterBuildMethods = null) {
		$this->bind($classOrInterface, $implementation, $afterBuildMethods);

		$this->singletons[$classOrInterface] = $classOrInterface;
	}

	/**
	 * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
	 * bound to `$classOrInterface` and return the value of a call to `$method` method with the call arguments.
	 *
	 * @param string|object $classOrInterface A class or interface fully qualified name or a string slug.
	 * @param string        $method           The method that should be called on the resolved implementation with the
	 *                                        specified array arguments.
	 *
	 * @return mixed The called method return value.
	 */
	public function callback($classOrInterface, $method) {
		$this->initClosuresSupport();

		if (!is_string($method)) {
			throw new RuntimeException('Callback method must be a string');
		}

		$classOrInterfaceName = is_object($classOrInterface) ? spl_object_hash($classOrInterface) : $classOrInterface;
		$cacheKey = $classOrInterfaceName . '::' . $method;

		if ( isset( $this->callbacks[ $cacheKey ] ) ) {
			// Only return the existing callback if $classOrInterface was not an object (so it remains unique).
			return $this->callbacks[ $cacheKey ];
		}

		if ($this->useClosures) {
			$f = di52_callbackClosure($this, $classOrInterface, $method);
		} else {
			$classOrInterfaceName = is_object($classOrInterface) ? get_class($classOrInterface) : $classOrInterface;
			// @codeCoverageIgnoreStart
			if (is_object($classOrInterface) || is_callable($classOrInterface)) {
				$objectId = uniqid(rand(1, 9999) . md5($classOrInterfaceName));
				$this->bind($objectId, $classOrInterface);
				$body = '$a = func_get_args();
					global $__container_' . $this->id . ';
					$c = $__container_' . $this->id . ';
					$i = $c->make(\'' . $objectId . '\');
					return call_user_func_array(array($i, \'' . $method . '\'),$a);';
			} else {
				$body = '$a = func_get_args();
					global $__container_' . $this->id . ';
					$c = $__container_' . $this->id . ';
					$i = $c->make(\'' . $classOrInterfaceName . '\');
					return call_user_func_array(array($i, \'' . $method . '\'),$a);';
			}
			$f = create_function('', $body);
			// @codeCoverageIgnoreEnd
		}

		$this->callbacks[ $cacheKey ] = $f;

		return $f;
	}

	public function _getParameter(ReflectionParameter $parameter) {
		if (defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 80000) {
			$class = $parameter->getType() && ! $parameter->getType()->isBuiltin() ? new ReflectionClass( $parameter->getType()->getName() ) : null;
		} else {
			$class = $parameter->getClass();
		}

		if (null === $class) {
			if (!$parameter->isDefaultValueAvailable()) {
				throw new ReflectionException("parameter '{$parameter->name}' of '{$this->resolving}::__construct' does not have a default value.");
			}
			return $parameter->getDefaultValue();
		}

		if (defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 80000) {
			$parameterClass = $parameter->getType() && ! $parameter->getType()->isBuiltin() ? $parameter->getType()->getName() : null;
		} else {
			$parameterClass = $parameter->getClass()->getName();
		}

		if (!$this->isBound($parameterClass) && !$class->isInstantiable()) {
			if (!$parameter->isDefaultValueAvailable()) {
				throw new ReflectionException("parameter '{$parameter->name}' of '{$this->resolving}::__construct' does not have a default value.");
			}
			return $parameter->getDefaultValue();
		}

		if (!isset($this->dependants[$parameterClass])) {
			$this->dependants[$parameterClass] = array($this->resolving);
		} else {
			$this->dependants[$parameterClass][] = $this->resolving;
		}

		return isset($this->contexts[$parameterClass][$this->resolving]) ?
			$this->offsetGet($this->contexts[$parameterClass][$this->resolving])
			: $this->offsetGet($parameterClass);
	}

	/**
	 * Returns a callable object that will build an instance of the specified class using the
	 * specified arguments when called.
	 *
	 * The callable will be a closure on PHP 5.3+ or a lambda function on PHP 5.2.
	 *
	 * @param  string $classOrInterface The fully qualified name of a class or an interface.
	 * @param  array $args An array of arguments that should be used to build the instancee;
	 *                                  note that any argument will be resolved using the container itself and bindings
	 *                                  will apply.
	 *
	 * @return callable  A callable function that will return an instance of the specified class when
	 *                   called.
	 */
	public function instance($classOrInterface, array $args = array()) {
		$this->initClosuresSupport();

		$classOrInterfaceName = is_object($classOrInterface) ? get_class($classOrInterface) : $classOrInterface;

		$instanceId = md5($classOrInterfaceName . '::' . serialize($args));
		if (!isset($this->instanceCallbacks[$instanceId])) {
			$this->__instanceCallbackArgs[$instanceId] = $args;

			if ($this->useClosures) {
				$f = di52_instanceClosure($this, $classOrInterface, $args);
			} else {
				// @codeCoverageIgnoreStart
				if (is_object($classOrInterface) || is_callable($classOrInterface)) {
					$objectId = uniqid(rand(1, 9999) . md5($classOrInterfaceName));
					$this->bind($objectId, $classOrInterface);
					$body = "global \$__container_{$this->id};
					\$c = \$__container_{$this->id};
					return \$c->make('{$objectId}'); ";
				} else {
					$body = "global \$__container_{$this->id};
					\$c = \$__container_{$this->id};
					\$r = new ReflectionClass('{$classOrInterface}');
					\$vars = \$c->__instanceCallbackArgs['{$instanceId}'];
					\$constructor = \$r->getConstructor();
					if (null === \$constructor || empty(\$vars)) {
						return \$c->make('{$classOrInterface}');
					}
					\$args = array();
					foreach (\$vars as \$var) {
						try {
							\$args[] = \$c->make(\$var);
						} catch (RuntimeException \$e) {
							\$args[] = \$var;
						}
					}
					return \$r->newInstanceArgs(\$args);";
				}
				$f = create_function('', $body);
				// @codeCoverageIgnoreEnd
			}

			$this->instanceCallbacks[$instanceId] = $f;
		}

		return $this->instanceCallbacks[$instanceId];
	}

	/**
	 * Initializes the closure support on PHP 5.3+.
	 */
	protected function initClosuresSupport() {
		if (null === $this->useClosures) {
			$this->useClosures = version_compare(PHP_VERSION, '5.3.0', '>=');
			if ($this->useClosures) {
				require_once dirname(__FILE__) . '/closuresSupport.php';
			}
		}
	}
}
