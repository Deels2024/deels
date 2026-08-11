<?php

declare(strict_types=1);

namespace RB\Core;

class ObjectWithOptionsA extends AbstractObjectWithOptions {

	public function __construct(array $options = NULL) {
		$this->_update_obj_id();
		if (isset($options)) {
			$this->_set_option($options);
		} // end if
		$this->_check_required_options();
	} // end of the '__construct()' constructor

} // end of the 'ObjectWithOptionsA' class

?>