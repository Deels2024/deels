<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

trait TraitCommon {

	public function __isset(string $varname) : bool {
		try {
			return !is_null($this->__get($varname));
		} catch (\Throwable $e) {
		} // end try
		return FALSE;
	} // end of the '__isset()' method

	public function __get(string $varname) : mixed {
		switch ($varname) {
			case 'classname':
				return get_class($this);
		} // end switch
		throw new RubconException(sprintf('%s(): Unable to access the object property: \'%s::%s\'', __METHOD__, get_class($this), $varname));
	} // end of the '__get()' method

	public function __set(string $varname, mixed $value) : void {
		throw new RubconException(sprintf('%s(): Unable to access the object property: \'%s::%s\'', __METHOD__, get_class($this), $varname));
	} // end of the '__set()' method

	public function __call(string $method_name, array $args) {
		throw new RubconException(sprintf('%s(): The method \'%s::%s\' is not found', __METHOD__, get_class($this), $method_name));
	} // end of the '__call()' method

	public function get_data_dump() : array {
		return [
			'classname'	=> $this->classname
		];
	} // end of the 'get_data_dump()' method

	final public function display_data_dump() : void {
		RB_var_dump($this->get_data_dump());
	} // end of the 'display_data_dump()' method

	final public function get_object_dump() : string {
		return RB_get_var_dump($this);
	} // end of the 'get_object_dump()' method

	final public function display_object_dump() : void {
		RB_var_dump($this);
	} // end of the 'display_object_dump()' method

} // end of the 'TraitCommon' trait
