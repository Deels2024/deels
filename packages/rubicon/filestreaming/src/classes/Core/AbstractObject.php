<?php

declare(strict_types=1);

namespace RB\Core;

abstract class AbstractObject extends AbstractCommon {

	private static	$start_obj_id	= 0;

	private	$obj_id,
			$ref_class;

	final protected function _update_obj_id() : void {
		$this->obj_id = self::$start_obj_id++;
	} // end of the '_update_obj_id()' method

	protected function _object_vars_filter(array &$object_vars) : void {
		unset($object_vars['obj_id'], $object_vars['ref_class']);
	} // end of the '_object_vars_filter()' method

	public function __toString() : string {
		return $this->object_string();
	} // end of the '__toString()' method

	public function __clone() : void {
		$this->_update_obj_id();
	} // end of the '__clone()' method

	public function __wakeup() : void {
		$this->_update_obj_id();
	} // end of the '__wakeup()' method

	public function __sleep() : array {
		$object_vars = get_object_vars($this);
		$this->_object_vars_filter($object_vars);
		return array_keys($object_vars);
	} // end of the '__sleep()' method

	public function __get(string $varname) : mixed {
		switch ($varname) {
			case 'obj_id':
				return $this->obj_id;
		} // end switch
		return parent::__get($varname);
	} // end of the '__get()' method

	public function get_data_dump() : array {
		return array_merge([
			'obj_id'	=> $this->obj_id
		], parent::get_data_dump());
	} // end of the 'get_data_dump()' method

	final public function get_reflection() : \ReflectionClass {
		if (!isset($this->ref_class)) {
			$this->ref_class = new \ReflectionClass($this);
		} // end if
		return $this->ref_class;
	} // end of the 'get_reflection()' method

	final public function new_instance(mixed ...$args) : static {
		$ref_class = $this->get_reflection();
		if ($ref_class->isInstantiable()) {
			return $ref_class->newInstanceArgs($args);
		} // end if
		$objref = $ref_class->newInstanceWithoutConstructor();
		if (method_exists($objref, '__construct')) {
			$objref->__construct(...$args);
		} // end if
		return $objref;
	} // end of the 'new_instance()' method

	final public function new_instance_args(array $args) : static {
		$ref_class = $this->get_reflection();
		if ($ref_class->isInstantiable()) {
			return $ref_class->newInstanceArgs($args);
		} // end if
		$objref = $ref_class->newInstanceWithoutConstructor();
		if (method_exists($objref, '__construct')) {
			$objref->__construct(...$args);
		} // end if
		return $objref;
	} // end of the 'new_instance_args()' method

	final public function object_string() : string {
		return sprintf('object(%s)id#%u', get_class($this), $this->obj_id);
	} // end of the 'object_string()' method

	final public static function is_same(self $objref1, self $objref2) : bool {
		return $objref1->obj_id === $objref2->obj_id;
	} // end of the 'is_same()' method

} // end of the 'AbstractObject' class

?>