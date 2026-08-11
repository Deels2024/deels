<?php

declare(strict_types=1);

namespace RB\Core;

class ObjectWithOptionsB extends AbstractObjectWithOptions {

	protected function __construct(array $options = NULL) {
		$this->_update_obj_id();
		if (isset($options)) {
			$this->_set_option($options);
		} // end if
		$this->_check_required_options();
	} // end of the '__construct()' constructor

	final public static function create_obj(mixed ...$args) : static {
		if (isset($args[0]) && ($args[0] instanceof static)) {
			return clone $args[0];
		} // end if
		$factory_objref = RB_create_instance_without_constructor(static::class);
		$factory_objref->__construct(...$args);
		return $factory_objref;
	} // end of the 'create_obj()' method

} // end of the 'ObjectWithOptionsB' class

?>