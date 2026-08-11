<?php

declare(strict_types=1);

namespace RB\FileSystem;

use RB\Core\StaticObject;
use RB\Exception\RubiconException;

class MimeType extends StaticObject {

	public const	DEFAULT_MIME_TYPE	= 'application/octet-stream';

	protected	$mime_types,
				$extensions;

	protected function __construct() {
		parent::__construct();
		require RB_INCLUDE_PATH . '/mime_types.php';
	} // end of the '__construct()' constructor

	public static function list_types() : array {
		$objref = static::_singleton();
		return array_keys($objref->mime_types);
	} // end of the 'list_types()' method

	public static function list_subtypes(string $type) : ?array {
		$objref = static::_singleton();
		$type = strtolower($type);
		if (isset($objref->mime_types[$type])) {
			return array_keys($objref->mime_types[$type]);
		} // end if
		return NULL;
	} // end of the 'list_subtypes()' method

	public static function list_extensions(string $type, string $subtype) : ?array {
		$objref = static::_singleton();
		$type = strtolower($type);
		$subtype = strtolower($subtype);
		if (isset($objref->mime_types[$type][$subtype])) {
			return $objref->mime_types[$type][$subtype];
		} // end if
		return NULL;
	} // end of the 'list_extensions()' method

	public static function get_mime_type(string $ext) : array {
		$ext = strtolower($ext);
		$objref = static::_singleton();
		return isset($objref->extensions[$ext]) ? $objref->extensions[$ext] : [static::DEFAULT_MIME_TYPE];
	} // end of the 'get_mime_type()' method

	public static function parse(string $mime_type, bool $assoc = NULL, bool $strict = NULL, bool $silent = NULL) : ?array {
		$objref = static::_singleton();
		$silent = $silent ?? TRUE;
		$result = NULL;
		if (!preg_match('@[\\x80-\\xff]@', $mime_type)) {
			$assoc = $assoc ?? FALSE;
			$strict = $strict ?? TRUE;
			$regexp = [
				'@([0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126}/[0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126})|([\\t\\n\\r ]+)|(;)|(.)@s',
				'@([\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]+)|([\\t\\n\\r ]+)|(;)|(.)@s',
				'@(=)|([\\t\\n\\r ]+)|(.)@s',
				'@([\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]+)|(")|([\\t\\n\\r ]+)|(.)@s',
				'@(")|([^\\\\"]+)|(\\\\[\\x00-\\x7f])|(.)@s',
				'@(;)|([\\t\\n\\r ]+)|(.)@s'
			];
			$state = 0;
			$offset = 0;
			$param = NULL;
			while (($token = RB_preg_token($mime_type, $regexp[$state], $offset)) !== NULL) {
				switch ($state) {
					case 0:
						switch ($token['ntoken']) {
							case 1:
								list($type, $subtype) = explode('/', strtolower($token['value']));
								$result = ['type' => $type, 'subtype' => $subtype, 'params' => []];
								break;
							case 3:
								$state = 1;
								break;
							case 4;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
					case 1:
						switch ($token['ntoken']) {
							case 1:
								$param = strtolower($token['value']);
								$result['params'][$param] = '';
								$state = 2;
								break;
							case 4;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
					case 2:
						switch ($token['ntoken']) {
							case 1:
								$state = 3;
								break;
							case 3;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
					case 3:
						switch ($token['ntoken']) {
							case 1:
								$result['params'][$param] = $token['value'];
								$param = NULL;
								$state = 5;
								break;
							case 2:
								$state = 4;
								break;
							case 4;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
					case 4:
						switch ($token['ntoken']) {
							case 1:
								$param = NULL;
								$state = 5;
								break;
							case 2:
								$result['params'][$param] .= $token['value'];
								break;
							case 3:
								$result['params'][$param] .= $token['value'][1];
								break;
							case 4;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
					case 5:
						switch ($token['ntoken']) {
							case 1:
								$state = 1;
								break;
							case 3;
								if ($silent) {
									$result = NULL;
									break 3;
								} else {
									throw new RubconException(sprintf('%s(): Unexpected data found at offset \'%u\'. Data: \'\\x%02x\'...', __METHOD__, $offset, ord($token['value'])));
								} // end if
						} // end switch
						break;
				} // end switch
			} // end while
			if ($state > 1 && $state < 5) {
				if ($silent) {
					$result = NULL;
				} else {
					throw new RubconException(sprintf('%s(): Unexpected completion parsing with state \'%u\'', __METHOD__, $state));
				} // end if
			} // end if
			if (isset($result)) {
				if ($strict && !isset($objref->mime_types[$result['type']][$result['subtype']])) {
					if ($silent) {
						$result = NULL;
					} else {
						$mime_type = $result['type'] . '/' . $result['subtype'];
						throw new RubconException(sprintf('%s(): Unknown MIME type: \'%s\'', __METHOD__, $mime_type));
					} // end if
				} elseif (!$assoc) {
					$result = array_values($result);
				} // end if
			} // end if
		} elseif (!$silent) {
			throw new RubconException(sprintf('%s(): Invalid ASCII string', __METHOD__));
		} // end if
		return $result;
	} // end of the 'parse()' method

	public static function glue(array $mime_type_parts, bool $assoc = NULL) : ?string {
		$mime_type = NULL;
		$assoc = $assoc ?? FALSE;
		if ($assoc) {
			if (isset($mime_type_parts['type']) && preg_match('@^[0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126}$@', $mime_type_parts['type']) && isset($mime_type_parts['subtype']) && preg_match('@^[0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126}$@', $mime_type_parts['subtype'])) {
				$mime_type = $mime_type_parts['type'] . '/' . $mime_type_parts['subtype'];
				if (isset($mime_type_parts['params']) && is_array($mime_type_parts['params'])) {
					foreach ($mime_type_parts['params'] as $k => $v) {
						if (preg_match('@^[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]+$@', $k) && !preg_match('@[\\x80-\\xff]@', $v)) {
							if (preg_match('@[^\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]@', $v)) {
								$v = '"' . addcslashes($v, '\\"') . '"';
							} // end if
							$mime_type .= sprintf('; %s=%s', $k, $v);
						} // end if
					} // end foreach
				} // end if
			} // end if
		} elseif (count($mime_type_parts) > 1) {
			$type = array_shift($mime_type_parts);
			$subtype = array_shift($mime_type_parts);
			if (preg_match('@^[0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126}$@', $type) && preg_match('@^[0-9A-Za-z][0-9A-Za-z!#$&+\-.^_]{0,126}$@', $subtype)) {
				$mime_type = $type . '/' . $subtype;
				if (is_array($params = array_shift($mime_type_parts))) {
					foreach ($params as $k => $v) {
						if (preg_match('@^[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]+$@', $k) && !preg_match('@[\\x80-\\xff]@', $v)) {
							if (preg_match('@[^\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]@', $v)) {
								$v = '"' . addcslashes($v, '\\"') . '"';
							} // end if
							$mime_type .= sprintf('; %s=%s', $k, $v);
						} // end if
					} // end foreach
				} // end if
			} // end if
		} // end if
		return $mime_type;
	} // end of the 'glue()' method

	public static function normalize(string $mime_type) : ?string {
		if (is_array($mime_info = static::parse($mime_type, TRUE, FALSE))) {
			$mime_type = $mime_info['type'] . '/' . $mime_info['subtype'];
			foreach ($mime_info['params'] as $k => $v) {
				if (preg_match('@[^\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x30-\\x39\\x41-\\x5a\\x5e-\\x7a\\x7c\\x7e]@', $v)) {
					$v = '"' . addcslashes($v, '\\"') . '"';
				} // end if
				$mime_type .= sprintf('; %s=%s', $k, $v);
			} // end foreach
			return $mime_type;
		} // end if
		return NULL;
	} // end of the 'normalize()' method

	public static function is_valid(string $mime_type, bool $strict = NULL) : bool {
		return static::parse($mime_type, TRUE, $strict) !== NULL;
	} // end of the 'is_valid()' method

	public static function auto_suffix(string $filename, string $mime_type) : string {
		if (is_array($mime_info = static::parse($mime_type))) {
			list($type, $subtype) = $mime_info;
			$extensions = static::list_extensions($type, $subtype);
			if (is_array($extensions) && count($extensions) > 0) {
				$filename = FileSystem::add_extension($filename, array_shift($extensions));
			} // end if
		} // end if
		return $filename;
	} // end of the 'auto_suffix()' method

} // end of the 'MimeType' class

?>