<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

abstract class AbstractObjectFactory implements InterfaceObjectFactory {

	use TraitCommonStatic;

	final public function __construct() {
		throw new RubconException(sprintf('%s(): Creating an instance of the factory class is not allowed', __METHOD__));
	} // end of the '__construct()' constructor

	final public static function get_ref_class() : \ReflectionClass {
		static $cache;
		$class = static::get_classname();
		if (isset($cache[$class])) {
			$ref_class = $cache[$class];
		} else {
			if (!class_exists($class)) {
				throw new RubconException(sprintf('%s(): The class \'%s\' does not exist', __METHOD__, $class));
			} // end if
			$ref_class = new \ReflectionClass($class);
			if ($ref_class->hasMethod('__construct')) {
				$ref_method = $ref_class->getMethod('__construct');
				if (!$ref_method->isPublic()) {
					throw new RubconException(sprintf('%s(): The method \'%s::%s\' must be public', __METHOD__, $ref_class->getName(), '__construct'));
				} // end if
			} // end if
			$cache[$class] = $ref_class;
		} // end if
		return $ref_class;
	} // end of the 'get_ref_class()' method

	public static function __callStatic(string $method_name, array $args) {
		$ref_class = static::get_ref_class();
		if ($ref_class->hasMethod($method_name)) {
			$ref_method = $ref_class->getMethod($method_name);
			if ($ref_method->isPublic() && $ref_method->isStatic()) {
				return call_user_func_array([$ref_class->getName(), $method_name], $args);
			} // end if
		} // end if
		throw new RubconException(sprintf('%s(): The method \'%s::%s\' is not found', __METHOD__, get_class($this), $method_name));
	} // end of the '__callStatic()' method

	final public static function get_constant(string $name) : mixed {
		$ref_class = static::get_ref_class();
		if (!$ref_class->hasConstant($name)) {
			throw new RubconException(sprintf('%s(): Unknown constant: \'%s\'', __METHOD__, $name));
		} // end if
		return $ref_class->getConstant($name);
	} // end of the 'get_constant()' method

	final public static function create_obj(mixed ...$args) : object {
		$ref_class = static::get_ref_class();
		$class = $ref_class->getName();
		if (isset($args[0]) && ($args[0] instanceof $class)) {
			return clone $args[0];
		} // end if
		return $ref_class->newInstanceArgs($args);
	} // end of the 'create_obj()' method

} // end of the 'AbstractObjectFactory' class
