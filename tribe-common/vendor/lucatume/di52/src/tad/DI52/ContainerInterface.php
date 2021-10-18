<?php

/**
 * Interface tad_DI52_ContainerInterface
 *
 * The API supported by the container.
 */
interface tad_DI52_ContainerInterface
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function setVar($key, $value);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key);

    /**
     * Binds an interface or class to an implementation.
     *
     * @param string $classOrInterface An alias or an array of implementation aliases
     * @param mixed $implementation
     * @param array $afterBuildMethods
     */
    public function bind($classOrInterface, $implementation, array $afterBuildMethods = null);

    /**
     * Binds an interface or class to an implementation replacing an existing implementation.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function replaceBind($classOrInterface, $implementation, array $afterBuildMethods = null);

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function make($classOrInterface);

    /**
     * Binds an interface or class to an implementation and will always return the same instance.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function singleton($classOrInterface, $implementation, array $afterBuildMethods = null);

    /**
     * Binds an interface or class to an implementation and will always return the same instance replacing an
     * existing singleton binding.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function replaceSingleton($classOrInterface, $implementation, array $afterBuildMethods = null);

    /**
     * Tags an array of implementation bindings.
     *
     * @param array $implementationsArray
     * @param string $tag
     */
    public function tag(array $implementationsArray, $tag);

    /**
     * Retrieves an array of bound implementations resolving them.
     *
     * @param string $tag
     * @return array An array of resolved bound implementations.
     */
    public function tagged($tag);

    /**
     * Registers a service provider implementation.
     *
     * @param string $serviceProviderClass
     */
    public function register($serviceProviderClass);

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     */
    public function boot();

    /**
     * Checks whether if an interface or class has been bound to a concrete implementation.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isBound($classOrInterface);

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag);

    /**
     * Binds a chain of decorators to a class or interface.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function bindDecorators($classOrInterface, array $decorators);

    /**
     * Binds a chain of decorators to a class or interface to be returned as a singleton.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function singletonDecorators($classOrInterface, $decorators);

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
     * @return tad_DI52_ContainerInterface
     */
    public function when($class);

    /**
     * Second step the `when->needs->give` chain for a contextual binding.
     *
     * @param string $classOrInterface The fully qualified name of the requested class.
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
     * @return tad_DI52_ContainerInterface
     */
    public function needs($classOrInterface);

    /**
     * Last step the `when->needs->give` chain for a contextual binding.
     *
     * @param mixed $implementation An implementation of the requested class.
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
     * @return tad_DI52_ContainerInterface
     */
    public function give($implementation);
}
