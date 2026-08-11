<?php

declare(strict_types=1);

namespace RB\Core;

class ObjectB extends AbstractObject {

	protected function __construct() {
		$this->_update_obj_id();
	} // end of the '__construct()' constructor

	final public static function create_obj(mixed ...$args) : static {
		if (isset($args[0]) && ($args[0] instanceof static)) {
			return clone $args[0];
		} // end if
		$factory_objref = RB_create_instance_without_constructor(static::class);
		$factory_objref->__construct(...$args);
		return $factory_objref;
	} // end of the 'create_obj()' method

} // end of the 'ObjectB' class

?>