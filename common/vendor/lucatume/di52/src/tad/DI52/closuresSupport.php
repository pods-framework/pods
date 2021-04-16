<?php
/**
 * Builds and returns a closure to be used to lazily make objects on PHP 5.3+, call a method on them and return the
 * method value.
 *
 * @param tad_DI52_Container $container
 * @param string $classOrInterface
 * @param string $method
 *
 * @return Closure
 */
function di52_callbackClosure(tad_DI52_Container $container, $classOrInterface, $method) {
	$classOrInterfaceName = is_object($classOrInterface) ? get_class($classOrInterface) : $classOrInterface;
	if (is_object($classOrInterface)) {
		$objectId = uniqid(rand(1, 9999) . md5($classOrInterfaceName));
		$container->bind($objectId, $classOrInterface);
	} else {
		$objectId = $classOrInterface;
	}

	return function () use ($container, $objectId, $method) {
		$a = func_get_args();
		$i = $container->make($objectId);
		return call_user_func_array(array($i, $method), $a);
	};
}

/**
 * Builds and returns a closure to be used to lazily make objects on PHP 5.3+ and return them.
 *
 * @param tad_DI52_Container $container
 * @param                  string $classOrInterface
 * @param array $vars
 *
 * @return Closure
 */
function di52_instanceClosure(tad_DI52_Container $container, $classOrInterface, array $vars = array()) {
	return function () use ($container, $classOrInterface, $vars) {
		if (is_object($classOrInterface)) {
			if (is_callable($classOrInterface)) {
				return call_user_func_array($classOrInterface, $vars);
			}
			return $classOrInterface;
		}

		$r = new ReflectionClass($classOrInterface);
		$constructor = $r->getConstructor();
		if (null === $constructor || empty($vars)) {
			return $container->make($classOrInterface);
		}
		$args = array();
		foreach ($vars as $var) {
			try {
				$args[] = $container->make($var);
			} catch (RuntimeException $e) {
				$args[] = $var;
			}
		}
		return $r->newInstanceArgs($args);
	};
}
