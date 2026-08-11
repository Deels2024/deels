<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

class StaticObjectWithOptions extends StaticObject {

	protected	$options	= [];

	protected	$initialized;

	private	$required_options;

	protected function __construct() {
		parent::__construct();
		$this->initialized = FALSE;
	} // end of the '__construct()' constructor

	final protected function _get_options() : array {
		return $this->options;
	} // end of the '_get_options()' method

	final protected function _option_exists(string $option) : bool {
		if (!preg_match(RB_RE_OPTION_NAME, $option)) {
			throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
		} // end if
		$options_ref = &$this->options;
		$pieces = explode(':', $option);
		foreach ($pieces as $piece) {
			if (!is_array($options_ref) || !array_key_exists($piece, $options_ref)) {
				return FALSE;
			} // end if
			$options_ref = &$options_ref[$piece];
		} // end foreach
		return TRUE;
	} // end of the '_option_exists()' method

	final protected function _get_option(string $option) : mixed {
		if (!preg_match(RB_RE_OPTION_NAME, $option)) {
			throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
		} // end if
		$options_ref = &$this->options;
		$pieces = explode(':', $option);
		foreach ($pieces as $piece) {
			if (!is_array($options_ref) || !array_key_exists($piece, $options_ref)) {
				return NULL;
			} // end if
			$options_ref = &$options_ref[$piece];
		} // end foreach
		return $options_ref;
	} // end of the '_get_option()' method

	final protected function _set_option($option, mixed $value = NULL) : self {
		if (is_array($option)) {
			foreach ($option as $key => $value) {
				$this->_set_option($key, $value);
			} // end foreach
			return $this;
		} // end if
		$option = (string) $option;
		if (!preg_match(RB_RE_OPTION_NAME, $option)) {
			throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
		} // end if
		$options_ref = &$this->options;
		$pieces = explode(':', $option);
		foreach ($pieces as $piece) {
			if (!is_array($options_ref) || !array_key_exists($piece, $options_ref)) {
				throw new RubconException(sprintf('%s(): Unknown option: \'%s\'', __METHOD__, $option));
			} // end if
			$options_ref = &$options_ref[$piece];
		} // end foreach
		$options_ref = $value;
		return $this;
	} // end of the '_set_option()' method

	final protected function _create_option(string $option, mixed $default_value = NULL, bool $overwrite = NULL) : static {
		if (!preg_match(RB_RE_OPTION_NAME, $option)) {
			throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
		} // end if
		$overwrite = $overwrite ?? FALSE;
		$options_ref = &$this->options;
		$pieces = explode(':', $option);
		$option = array_pop($pieces);
		foreach ($pieces as $i => $piece) {
			if (!is_array($options_ref)) {
				if ($overwrite) {
					$options_ref = [];
				} else {
					$option = implode(':', array_slice($pieces, 0, $i));
					throw new RubconException(sprintf('%s(): Attempt to overwrite the option \'%s\'', __METHOD__, $option));
				} // end if
			} // end if
			if (!array_key_exists($piece, $options_ref)) {
				$options_ref[$piece] = [];
			} // end if
			$options_ref = &$options_ref[$piece];
		} // end foreach
		if ($overwrite || !array_key_exists($option, $options_ref)) {
			$options_ref[$option] = $default_value;
		} // end if
		return $this;
	} // end of the '_create_option()' method

	final protected function _delete_option(string ...$args) : static {
		foreach ($args as $option) {
			if (!preg_match(RB_RE_OPTION_NAME, $option)) {
				throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
			} // end if
			$stack = [];
			$options_ref = &$this->options;
			$pieces = explode(':', $option);
			foreach ($pieces as $piece) {
				if (!is_array($options_ref) || !array_key_exists($piece, $options_ref)) {
					continue 2;
				} // end if
				array_unshift($stack, [$piece, &$options_ref]);
				$options_ref = &$options_ref[$piece];
			} // end foreach
			while ($s = array_shift($stack)) {
				unset($s[1][$s[0]]);
				if (count($s[1]) > 0) {
					break;
				} // end if
			} // end if
		} // end foreach
		return $this;
	} // end of the '_delete_option()' method

	final protected function _set_required_option(string ...$args) : static {
		foreach ($args as $option) {
			if (!preg_match(RB_RE_OPTION_NAME, $option)) {
				throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
			} // end if
			$this->required_options[$option] = TRUE;
		} // end foreach
		return $this;
	} // end of the '_set_required_option()' method

	final protected function _unset_required_option(string ...$args) : static {
		foreach ($args as $option) {
			if (!preg_match(RB_RE_OPTION_NAME, $option)) {
				throw new RubconException(sprintf('%s(): Invalid option name: \'%s\'', __METHOD__, $option));
			} // end if
			if (isset($this->required_options[$option])) {
				unset($this->required_options[$option]);
			} // end if
		} // end foreach
		return $this;
	} // end of the '_unset_required_option()' method

	final protected function _check_required_options() : static {
		if (is_array($this->required_options)) {
			foreach ($this->required_options as $required_option => $v) {
				$options_ref = &$this->options;
				$pieces = explode(':', $required_option);
				foreach ($pieces as $piece) {
					if (!is_array($options_ref) || !isset($options_ref[$piece])) {
						throw new RubconException(sprintf('%s(): The required option \'%s\' is not specified', __METHOD__, $required_option));
					} // end if
					$options_ref = &$options_ref[$piece];
				} // end foreach
			} // end foreach
		} // end if
		return $this;
	} // end of the '_check_required_options()' method

	protected function _post_init() : void {
		////////
	} // end of the '_post_init()' method

	protected function _init() : bool {
		return TRUE;
	} // end of the '_init()' method

	public function get_data_dump() : array {
		return array_merge([
			'required_options'	=> $this->required_options,
			'options'			=> $this->options,
			'initialized'		=> $this->initialized
		], parent::get_data_dump());
	} // end of the 'get_data_dump()' method

	final public static function init(array $options = NULL) : bool {
		$objref = static::_singleton();
		if (!$objref->initialized) {
			if (isset($options)) {
				$objref->_set_option($options);
			} // end if
			$objref->_check_required_options();
			$objref->initialized = $objref->_init();
			$objref->_post_init();
		} // end if
		return $objref->initialized;
	} // end of the 'init()' method

	final public static function is_initialized() : bool {
		$objref = static::_singleton();
		return $objref->initialized;
	} // end of the 'is_initialized()' method

	final public static function get_options() : array {
		$objref = static::_singleton();
		return $objref->_get_options();
	} // end of the 'get_options()' method

	final public static function option_exists(string $option) : bool {
		$objref = static::_singleton();
		return $objref->_option_exists($option);
	} // end of the 'option_exists()' method

	final public static function get_option(string $option) : mixed {
		$objref = static::_singleton();
		return $objref->_get_option($option);
	} // end of the 'get_option()' method

} // end of the 'StaticObjectWithOptions' class
