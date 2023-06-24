<?php
/**
 * The base service provider class.
 *
 * @license GPL-3.0
 * Modified by Scott Kingsley Clark on 24-June-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Pods\Prefixed\lucatume\DI52;

/**
 * Class ServiceProvider
 *
 * @package lucatume\DI52
 */
abstract class ServiceProvider
{
    /**
     * Whether the service provider will be a deferred one or not.
     *
     * @var bool
     */
    protected $deferred = false;

    /**
     * @var Container
     */
    protected $container;


    /**
     * ServiceProvider constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Whether the service provider will be a deferred one or not.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->deferred;
    }

    /**
     * Returns an array of the class or interfaces bound and provided by the service provider.
     *
     * @return array<string> A list of fully-qualified implementations provided by the service provider.
     */
    public function provides()
    {
        return [];
    }

    /**
     * Binds and sets up implementations at boot time.
     *
     * @return void The method will not return any value.
     */
    public function boot()
    {
        // no-op
    }

    /**
     * Registers the service provider bindings.
     *
     * @return void The method does not return any value.
     */
    abstract public function register();
}
