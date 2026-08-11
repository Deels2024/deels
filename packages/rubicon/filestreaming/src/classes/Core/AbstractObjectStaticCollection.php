<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

abstract class AbstractObjectStaticCollection extends StaticObjectWithOptions {

	public const	WORK_AREA_NAME_REGEXP	= '@^[A-Za-z_][A-Za-z0-9_]*$@';

	protected	$ref_external_class,
				$number_of_constructor_parameters,
				$current_work_area_name,
				$work_areas;

	protected function __construct(string $external_class) {
		if (!class_exists($external_class)) {
			throw new RubconException(sprintf('%s(): The class \'%s\' does not exist', __METHOD__, $external_class));
		} // end if
		$this->ref_external_class = new \ReflectionClass($external_class);
		$this->number_of_constructor_parameters = 0;
		if ($this->ref_external_class->hasMethod('__construct')) {
			$ref_method = $this->ref_external_class->getMethod('__construct');
			if (!$ref_method->isPublic()) {
				throw new RubconException(sprintf('%s(): The method \'%s::%s\' must be public', __METHOD__, get_class($this), '__construct'));
			} // end if
			$this->number_of_constructor_parameters = $ref_method->getNumberOfParameters();
		} // end if
		$this->work_areas = [];
		parent::__construct();
	} // end of the '__construct()' constructor

	abstract protected function _prepare_instance_args(array &$args) : void;

	final public static function create_obj(mixed ...$args) : int|string|null {
		$objref = static::_singleton();
		if ($objref->initialized) {
			$work_area_name = NULL;
			if (count($args) > $objref->number_of_constructor_parameters) {
				$args = array_slice($args, 0, $objref->number_of_constructor_parameters + 1);
				$work_area_name = array_pop($args);
				if (!self::is_valid_work_area_name($work_area_name)) {
					throw new RubconException(sprintf('%s(): Invalid work area name: \'%s\'', __METHOD__, $work_area_name));
				} // end if
			} // end if
			$objref->_prepare_instance_args($args);
			$external_obj = $objref->ref_external_class->newInstanceArgs($args);
			if (!isset($work_area_name)) {
				$objref->work_areas[] = $external_obj;
				end($objref->work_areas);
				$objref->current_work_area_name = key($objref->work_areas);
			} else {
				$objref->work_areas[$work_area_name] = $external_obj;
				$objref->current_work_area_name = $work_area_name;
			} // end if
			return $objref->current_work_area_name;
		} // end if
		return NULL;
	} // end of the 'create_obj()' method

	final protected function _call_external_object_method(string $method_name, array $args, int|string $work_area_name = NULL) {
		if ($this->initialized) {
			if (!isset($work_area_name)) $work_area_name = $this->current_work_area_name;
			if (!isset($work_area_name)) {
				throw new RubconException(sprintf('%s(): Undefined work area name', __METHOD__));
			} // end if
			if (!isset($this->work_areas[$work_area_name])) {
				throw new RubconException(sprintf('%s(): The work area \'%s\' was not found', __METHOD__, $work_area_name));
			} // end if
			$external_obj = $this->work_areas[$work_area_name];
			return call_user_func_array([$external_obj, $method_name], $args);
		} // end if
		return NULL;
	} // end of the '_call_external_object_method()' method

	public static function __callStatic(string $method_name, array $args) {
		$objref = static::_singleton();
		$ref_class = $objref->ref_external_class;
		if ($ref_class->hasMethod($method_name)) {
			$ref_method = $ref_class->getMethod($method_name);
			if ($ref_method->isPublic() && !$ref_method->isStatic()) {
				$work_area_name = NULL;
				if (count($args) > ($number_of_parameters = $ref_method->getNumberOfParameters())) {
					$args = array_slice($args, 0, $number_of_parameters + 1);
					$work_area_name = array_pop($args);
					if (!self::is_valid_work_area_name($work_area_name)) {
						throw new RubconException(sprintf('%s(): Invalid work area name: \'%s\'', __METHOD__, $work_area_name));
					} // end if
				} // end if
				return $objref->_call_external_object_method($method_name, $args, $work_area_name);
			} // end if
		} // end if
		return parent::__callStatic($method_name, $args);
	} // end of the '__callStatic()' method

	final public static function get_external_object(int|string $work_area_name = NULL) : ?object {
		$objref = static::_singleton();
		if ($objref->initialized) {
			if (!isset($work_area_name)) $work_area_name = $objref->current_work_area_name;
			if (!isset($work_area_name)) {
				throw new RubconException(sprintf('%s(): Undefined work area name', __METHOD__));
			} // end if
			if (!self::is_valid_work_area_name($work_area_name)) {
				throw new RubconException(sprintf('%s(): Invalid work area name: \'%s\'', __METHOD__, $work_area_name));
			} // end if
			if (!isset($objref->work_areas[$work_area_name])) {
				throw new RubconException(sprintf('%s(): The work area \'%s\' was not found', __METHOD__, $work_area_name));
			} // end if
			return $objref->work_areas[$work_area_name];
		} // end if
		return NULL;
	} // end of the 'get_external_object()' method

	final public static function destroy(int|string $work_area_name = NULL) : void {
		$objref = static::_singleton();
		if ($objref->initialized) {
			if (!isset($work_area_name)) $work_area_name = $objref->current_work_area_name;
			if (!isset($work_area_name)) {
				throw new RubconException(sprintf('%s(): Undefined work area name', __METHOD__));
			} // end if
			if (!self::is_valid_work_area_name($work_area_name)) {
				throw new RubconException(sprintf('%s(): Invalid work area name: \'%s\'', __METHOD__, $work_area_name));
			} // end if
			if (!isset($objref->work_areas[$work_area_name])) {
				throw new RubconException(sprintf('%s(): The work area \'%s\' was not found', __METHOD__, $work_area_name));
			} // end if
			unset($objref->work_areas[$work_area_name]);
			if ($objref->current_work_area_name === $work_area_name) {
				reset($objref->work_areas);
				$objref->current_work_area_name = key($objref->work_areas);
			} // end if
		} // end if
	} // end of the 'destroy()' method

	final public static function destroy_all() : void {
		$objref = static::_singleton();
		if ($objref->initialized) {
			$objref->current_work_area_name = NULL;
			$objref->work_areas = [];
		} // end if
	} // end of the 'destroy_all()' method

	final public static function get_current_work_area_name() : int|string|null {
		$objref = static::_singleton();
		if ($objref->initialized) {
			return $objref->current_work_area_name;
		} // end if
		return NULL;
	} // end of the 'get_current_work_area_name()' method

	final public static function set_current_work_area_name(int|string $work_area_name) : void {
		$objref = static::_singleton();
		if ($objref->initialized) {
			if (!self::is_valid_work_area_name($work_area_name)) {
				throw new RubconException(sprintf('%s(): Invalid work area name: \'%s\'', __METHOD__, $work_area_name));
			} // end if
			if (!isset($objref->work_areas[$work_area_name])) {
				throw new RubconException(sprintf('%s(): The work area \'%s\' was not found', __METHOD__, $work_area_name));
			} // end if
			$objref->current_work_area_name = $work_area_name;
		} // end if
	} // end of the 'set_current_work_area_name()' method

	final public static function is_valid_work_area_name(int|string $work_area_name) : bool {
		if (is_string($work_area_name)) {
			return (bool) preg_match(static::WORK_AREA_NAME_REGEXP, $work_area_name);
		} elseif (is_int($work_area_name) && $work_area_name >= 0) {
			return TRUE;
		} // end if
		return FALSE;
	} // end of the 'is_valid_work_area_name()' method

} // end of the 'AbstractObjectStaticCollection' class
