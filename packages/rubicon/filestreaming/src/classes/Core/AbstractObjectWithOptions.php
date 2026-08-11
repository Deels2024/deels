<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

abstract class AbstractObjectWithOptions extends AbstractObject {

	protected	$options	= [];

	private	$required_options;

	final public function get_options() : array {
		return $this->options;
	} // end of the 'get_options()' method

	final public function option_exists(string $option) : bool {
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
	} // end of the 'option_exists()' method

	final public function get_option(string $option) : mixed {
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
	} // end of the 'get_option()' method

	final protected function _set_option($option, mixed $value = NULL) : static {
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
		$args = func_get_args();
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

} // end of the 'AbstractObjectWithOptions' class
