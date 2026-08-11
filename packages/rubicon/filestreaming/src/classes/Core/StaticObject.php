<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

class StaticObject extends AbstractObject {

	private static	$instances;

	private	$static_methods;

	protected function __construct() {
		$this->_update_obj_id();
		$this->static_methods = [];
	} // end of the '__construct()' constructor

	final protected function _set_static_method(string ...$args) : void {
		$ref_class = $this->get_reflection();
		foreach ($args as $method_name) {
			if (!$ref_class->hasMethod($method_name)) {
				throw new RubconException(sprintf('%s(): The method \'%s::%s\' is not found', __METHOD__, get_class($this), $method_name));
			} // end if
			$ref_method = $ref_class->getMethod($method_name);
			if ($ref_method->isStatic()) {
				throw new RubconException(sprintf('%s(): The method \'%s::%s\' should not be static', __METHOD__, get_class($this), $method_name));
			} // end if
			$method_name = strtolower($method_name);
			$this->static_methods[$method_name] = $ref_method->getShortName();
		} // end foreach
	} // end of the '_set_static_method()' method

	final protected function _delete_static_method(string ...$args) : void {
		foreach ($args as $method_name) {
			$method_name = strtolower($method_name);
			if (isset($this->static_methods[$method_name])) unset($this->static_methods[$method_name]);
		} // end foreach
	} // end of the '_delete_static_method()' method

	final protected function _isset_static_method(string $method_name) : bool {
		$method_name = strtolower($method_name);
		return isset($this->static_methods[$method_name]);
	} // end of the '_isset_static_method()' method

	final protected function _call_static_method(string $method_name, array $args) {
		$method_name = strtolower($method_name);
		if (!isset($this->static_methods[$method_name])) {
			throw new RubconException(sprintf('%s(): The method \'%s::%s\' is not found', __METHOD__, get_class($this), $method_name));
		} // end if
		return call_user_func_array([$this, $this->static_methods[$method_name]], $args);
	} // end of the '_call_static_method()' method

	public static function __callStatic(string $method_name, array $args) {
		$objref = static::_singleton();
		if ($objref->_isset_static_method($method_name)) {
			return $objref->_call_static_method($method_name, $args);
		} // end if
		return parent::__callStatic($method_name, $args);
	} // end of the '__callStatic()' method

	final protected static function _singleton() : self {
		$classname = static::class;
		if (!isset(self::$instances[$classname])) {
			self::$instances[$classname] = new $classname();
		} // end if
		return self::$instances[$classname];
	} // end of the '_singleton()' method

	final public static function object_list() : array {
		return is_array(self::$instances) ? array_keys(self::$instances) : [];
	} // end of the 'object_list()' method

	final public static function delete_instances() : void {
		if (static::class !== self::class) {
			throw new RubconException(sprintf('%s(): Method inheritance is not allowed', __METHOD__));
		} // end if
		self::$instances = NULL;
	} // end of the 'delete_instances()' method

	final public static function delete_instance() : void {
		$classname = static::class;
		if (isset(self::$instances[$classname])) {
			unset(self::$instances[$classname]);
		} // end if
	} // end of the 'delete_instance()' method

	final public static function instance_exists() : bool {
		$classname = static::class;
		return isset(self::$instances[$classname]);
	} // end of the 'instance_exists()' method

	final public static function get_data_dump_static() : array {
		$objref = static::_singleton();
		return $objref->get_data_dump();
	} // end of the 'get_data_dump_static()' method

	final public static function display_data_dump_static() : void {
		$objref = static::_singleton();
		$objref->display_data_dump();
	} // end of the 'display_data_dump_static()' method

	final public static function get_object_dump_static() : array {
		$objref = static::_singleton();
		return $objref->get_object_dump();
	} // end of the 'get_object_dump_static()' method

	final public static function display_object_dump_static() : void {
		$objref = static::_singleton();
		$objref->display_object_dump();
	} // end of the 'display_object_dump_static()' method

} // end of the 'StaticObject' class
