<?php
/**
 * A facade to make a DI container instance globally available as a Service Locator.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52;

use Pods\Prefixed\lucatume\DI52\Builders\ValueBuilder;

/**
 * Class App
 *
 * @package lucatume\DI52
 */
class App
{
    /** A reference to the singleton instance of the DI container
     * the application uses as Service Locator.
     *
     * @var Container|null
     */
    protected static $container;

    /**
     * Returns the singleton instance of the DI container the application
     * will use as Service Locator.
     *
     * @return Container The singleton instance of the Container used as Service Locator
     *                   by the application.
     */
    public static function container()
    {
        if (!isset(static::$container)) {
            static::$container = new Container();
        }

        return static::$container;
    }
    
    /**
     * Sets the container instance the Application should use as a Service Locator.
     *
     * If the Application already stores a reference to a Container instance, then
     * this will be replaced by the new one.
     *
     * @param Container $container A reference to the Container instance the Application
     *                             should use as a Service Locator.
     *
     * @return void The method does not return any value.
     */
    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }
    
        /**
     * Sets a variable on the container.
     *
     * @param string $key   The alias the container will use to reference the variable.
     * @param mixed  $value The variable value.
     *
     * @return void The method does not return any value.
     */
    public static function setVar($key, $value)
    {
        static::container()->setVar($key, $value);
    }

    /**
     * Sets a variable on the container using the ArrayAccess API.
     *
     * When using the container as an array bindings will be bound as singletons.
     * These are equivalent: `$container->singleton('foo','ClassOne');`, `$container['foo'] = 'ClassOne';`.
     *
     * @param string $offset The alias the container will use to reference the variable.
     * @param mixed  $value  The variable value.
     *
     * @return void This method does not return any value.
     *
     * @throws ContainerException If the closure building fails.
     */
    public static function offsetSet($offset, $value)
    {
        static::container()->offsetSet($offset, $value);
    }

    /**
     * Binds an interface a class or a string slug to an implementation and will always return the same instance.
     *
     * @param string             $id                A class or interface fully qualified name or a string slug.
     * @param mixed              $implementation    The implementation that should be bound to the alias(es); can be a
     *                                              class name, an object or a closure.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If there's any issue reflecting on the class, interface or the implementation.
     */
    public static function singleton($id, $implementation = null, array $afterBuildMethods = null)
    {
        static::container()->singleton($id, $implementation, $afterBuildMethods);
    }

    /**
     * Returns a variable stored in the container.
     *
     * If the variable is a binding then the binding will be resolved before returning it.
     *
     * @param string     $key     The alias of the variable or binding to fetch.
     * @param mixed|null $default A default value to return if the variable is not set in the container.
     *
     * @return mixed The variable value or the resolved binding.
     * @throws ContainerException If there's an issue resolving the variable.
     *
     * @see Container::get()
     */
    public static function getVar($key, $default = null)
    {
        return static::container()->getVar($key, $default);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $offset Identifier of the entry to look for.
     *
     * @return mixed The entry for an id.
     *
     * @return mixed The value for the offset.
     *
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException  No entry was found for **this** identifier.
     */
    public static function offsetGet($offset)
    {
        return static::container()->offsetGet($offset);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed The entry for an id.
     *
     * @throws ContainerException Error while retrieving the entry.
     */
    public static function get($id)
    {
        return static::container()->get($id);
    }

    /**
     * Returns an instance of the class or object bound to an interface, class  or string slug if any, else it will try
     * to automagically resolve the object to a usable instance.
     *
     * If the implementation has been bound as singleton using the `singleton` method
     * or the ArrayAccess API then the implementation will be resolved just on the first request.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed
     * @throws ContainerException If the target of the make is not bound and is not a valid,
     *                                              concrete, class name or there's any issue making the target.
     */
    public static function make($id)
    {
        return static::container()->make($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `$container[$id]` returning true does not mean that `$container[$id]` will not throw an exception.
     * It does however mean that `$container[$id]` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     */
    public static function offsetExists($offset)
    {
        return static::container()->offsetExists($offset);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool Whether the container contains a binding for an id or not.
     */
    public static function has($id)
    {
        return static::container()->has($id);
    }

    /**
     * Tags an array of implementations bindings for later retrieval.
     *
     * The implementations can also reference interfaces, classes or string slugs.
     * Example:
     *
     *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
     *
     * @param array<string|callable|object> $implementationsArray The ids, class names or objects to apply the tag to.
     * @param string                        $tag                  The tag to apply.
     *
     * @return void This method does not return any value.
     * @see Container::tagged()
     *
     */
    public static function tag(array $implementationsArray, $tag)
    {
        static::container()->tag($implementationsArray, $tag);
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
     * @param string $tag The tag to return the tagged values for.
     *
     * @return array<mixed> An array of resolved bound implementations.
     * @throws NotFoundException If nothing is tagged with the tag.
     * @throws ContainerException If one of the bindings is not of the correct type.
     * @see Container::tag()
     */
    public static function tagged($tag)
    {
        return static::container()->tagged($tag);
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     *
     * @return bool
     * @see Container::tag()
     *
     */
    public static function hasTag($tag)
    {
        return static::container()->hasTag($tag);
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
     * @param string $serviceProviderClass The fully-qualified Service Provider class name.
     * @param string ...$alias             A list of aliases the provider should be registered with.
     * @return void This method does not return any value.
     * @throws ContainerException If the Service Provider is not correctly configured or there's an issue
     *                                     reflecting on it.
     * @see ServiceProvider::register()
     * @see ServiceProvider::isDeferred()
     * @see ServiceProvider::provides()
     * @see Container::getProvider()
     * @see ServiceProvider::boot()
     */
    public static function register($serviceProviderClass, ...$alias)
    {
        static::container()->register($serviceProviderClass, ...$alias);
    }

    /**
     * Binds an interface, a class or a string slug to an implementation.
     *
     * Existing implementations are replaced.
     *
     * @param string             $id                A class or interface fully qualified name or a string slug.
     * @param mixed              $implementation    The implementation that should be bound to the alias(es); can be a
     *                                              class name, an object or a closure.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return void The method does not return any value.
     * @throws ContainerException      If there's an issue while trying to bind the implementation.
     */
    public static function bind($id, $implementation = null, array $afterBuildMethods = null)
    {
        static::container()->bind($id, $implementation, $afterBuildMethods);
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     *
     * If there are bootable providers (providers overloading the `boot` method) then the `boot` method will be
     * called on each bootable provider.
     *
     * @return void This method does not return any value.
     *
     * @see ServiceProvider::boot()
     */
    public static function boot()
    {
        static::container()->boot();
    }

    /**
     * Binds a class, interface or string slug to a chain of implementations decorating a base
     * object; the chain will be lazily resolved only on the first call.
     *
     * The base decorated object must be the last element of the array.
     *
     * @param string                        $id                The class, interface or slug the decorator chain should
     *                                                         be bound to.
     * @param array<string|object|callable> $decorators        An array of implementations that decorate an object.
     * @param array<string>|null            $afterBuildMethods An array of methods that should be called on the
     *                                                         instance after it has been built; the methods should not
     *                                                         require any argument.
     *
     * @return void This method does not return any value.
     * @throws ContainerException
     */
    public static function singletonDecorators($id, $decorators, array $afterBuildMethods = null)
    {
        static::container()->singletonDecorators($id, $decorators, $afterBuildMethods);
    }

    /**
     * Binds a class, interface or string slug to to a chain of implementations decorating a
     * base object.
     *
     * The base decorated object must be the last element of the array.
     *
     * @param string                        $id                The class, interface or slug the decorator chain should
     *                                                         be bound to.
     * @param array<string|object|callable> $decorators        An array of implementations that decorate an object.
     * @param array<string>|null            $afterBuildMethods An array of methods that should be called on the
     *                                                         instance after it has been built; the methods should not
     *                                                         require any argument.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If there's any issue binding the decorators.
     */
    public static function bindDecorators($id, array $decorators, array $afterBuildMethods = null)
    {
        static::container()->bindDecorators($id, $decorators, $afterBuildMethods);
    }

    /**
     * Unsets a binding or tag in the container.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void The method does not return any value.
     */
    public static function offsetUnset($offset)
    {
        static::container()->offsetUnset($offset);
    }

    /**
     * Starts the `when->needs->give` chain for a contextual binding.
     *
     * @param string $class The fully qualified name of the requesting class.
     *
     * Example:
     *
     *      // Any class requesting an implementation of `LoggerInterface` will receive this implementation ...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // But if the requesting class is `Worker` return another implementation
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @return Container The container instance, to continue the when/needs/give chain.
     */
    public static function when($class)
    {
        return static::container()->when($class);
    }

    /**
     * Second step of the `when->needs->give` chain for a contextual binding.
     *
     * Example:
     *
     *      // Any class requesting an implementation of `LoggerInterface` will receive this implementation ...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // But if the requesting class is `Worker` return another implementation.
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @param string $id The class or interface needed by the class.
     *
     * @return Container The container instance, to continue the when/needs/give chain.
     */
    public static function needs($id)
    {
        return static::container()->needs($id);
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
     *
     * @return void This method does not return any value.
     * @throws NotFoundException
     */
    public static function give($implementation)
    {
        static::container()->give($implementation);
    }

    /**
     * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
     * bound to `$id` and return the value of a call to `$method` method with the call arguments.
     *
     * @param string|object $id               A fully-qualified class name, a bound slug or an object o call the
     *                                        callback on.
     * @param string        $method           The method that should be called on the resolved implementation with the
     *                                        specified array arguments.
     *
     * @return callable The callback function.
     *
     * @throws ContainerException If the id is not a bound implementation or valid class name.
     */
    public static function callback($id, $method)
    {
        return static::container()->callback($id, $method);
    }

    /**
     * Returns a callable object that will build an instance of the specified class using the
     * specified arguments when called.
     *
     * The callable will be a closure on PHP 5.3+ or a lambda function on PHP 5.2.
     *
     * @param string|mixed       $id                The fully qualified name of a class or an interface.
     * @param array<mixed>       $buildArgs         An array of arguments that should be used to build the instance;
     *                                              note that any argument will be resolved using the container itself
     *                                              and bindings will apply.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return callable  A callable function that will return an instance of the specified class when
     *                   called.
     */
    public static function instance($id, array $buildArgs = [], array $afterBuildMethods = null)
    {
        return static::container()->instance($id, $buildArgs, $afterBuildMethods);
    }

    /**
     * Protects a value to make sure it will not be resolved, if callable or if the name of an existing class.
     *
     * @param mixed $value The value to protect.
     *
     * @return ValueBuilder A protected value instance, its value set to the provided value.
     */
    public static function protect($value)
    {
        return static::container()->protect($value);
    }

    /**
     * Returns the Service Provider instance registered.
     *
     * @param string $providerId The Service Provider clas to return the instance for.
     *
     * @return ServiceProvider The service provider instance.
     *
     * @throws NotFoundException|ContainerException If the Service Provider class was never registered in the container
     *                                              or there's an issue retrieving it.
     */
    public static function getProvider($providerId)
    {
        return static::container()->getProvider($providerId);
    }

    /**
     * Returns whether a binding exists in the container or not.
     *
     * `isBound($id)` returning `true` means the a call to `bind($id, $implementaion)` or `singleton($id,
     * $implementation)` (or equivalent ArrayAccess methods) was explicitly made.
     *
     * @param string $id The id to check for bindings in the container.
     *
     * @return bool Whether an explicit binding for the id exists in the container or not.
     */
    public static function isBound($id)
    {
        return static::container()->isBound($id);
    }

    /**
     * Sets the mask for the throwables that should be caught and re-thrown as container exceptions.
     *
     * @param int $maskThrowables The mask for the throwables that should be caught and re-thrown as container
     *
     * @return void
     */
    public static function setExceptionMask($maskThrowables)
    {
        static::container()->setExceptionMask($maskThrowables);
    }
}
