<?php

declare(strict_types=1);

if (!defined('DS')) {

	define('DS',											DIRECTORY_SEPARATOR);

} // end if

define('RB_IS_WINDOWS',										DS === '\\');

define('RB_SAPI_NAME',										php_sapi_name());

define('RB_IS_CLI',											RB_SAPI_NAME == 'cli');

define('RB_SERVER_PROTOCOL',								isset($_SERVER['SERVER_PROTOCOL']) ? strtoupper($_SERVER['SERVER_PROTOCOL']) : 'HTTP/1.1');
define('RB_REQUEST_METHOD',									isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '');
define('RB_IS_POST_METHOD',									RB_REQUEST_METHOD == 'POST');
define('RB_IS_GET_METHOD',									RB_REQUEST_METHOD == 'GET');
define('RB_IS_PUT_METHOD',									RB_REQUEST_METHOD == 'PUT');
define('RB_IS_HTTPS',										!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off');
define('RB_IS_HTTP1',										RB_SERVER_PROTOCOL == 'HTTP/1.0');
define('RB_IS_HTTP11',										RB_SERVER_PROTOCOL == 'HTTP/1.1');
define('RB_IS_HTTP2',										RB_SERVER_PROTOCOL == 'HTTP/2.0');

define('RB_INT_MAX',										PHP_INT_MAX);
define('RB_INT_MIN',										PHP_INT_MIN);

define('RB_IS_X64',											RB_INT_MAX === 0x7fffffffffffffff);

define('RB_XDEBUG_EXTENSION_LOADED',						extension_loaded('xdebug'));

define('RB_INCLUDE_PATH',									realpath(__DIR__));

if (!defined('RB_DATA_PATH')) {

	define('RB_DATA_PATH',									realpath(__DIR__ . '/../..') . DS . 'data');

} // end if

if (!is_dir(RB_DATA_PATH)) {

	exit('DATA folder not found.');

} // end if

if (!is_readable(RB_DATA_PATH)) {

	exit('DATA folder not readable.');

} // end if

define('RB_CACHE_PATH',										RB_DATA_PATH . DS . 'cache');

if (!is_dir(RB_CACHE_PATH)) {

	exit('CACHE folder not found.');

} // end if

define('RB_RE_OPTION_NAME',									'@^[A-Za-z_][A-Za-z0-9_]*(?:\:[A-Za-z_][A-Za-z0-9_]*)*$@');

if (RB_XDEBUG_EXTENSION_LOADED) {

	ini_set('xdebug.max_nesting_level', '3000');

	ini_set('xdebug.var_display_max_depth', '-1');
	ini_set('xdebug.var_display_max_children', '-1');
	ini_set('xdebug.var_display_max_data', '-1');

} // end if

if (!function_exists('clamp')) {

	// URL: https://wiki.php.net/rfc/clamp
	function clamp(int|float $num, int|float $min, int|float $max) : int|float {
		if (!is_int($num) && !is_float($num)) {
			throw new \TypeError(sprintf('%s() expects parameter %d to be integer or float, %s given', __FUNCTION__, 1, gettype($num)));
		} // end if
		if (!is_int($min) && !is_float($min)) {
			throw new \TypeError(sprintf('%s() expects parameter %d to be integer or float, %s given', __FUNCTION__, 2, gettype($min)));
		} // end if
		if (!is_int($max) && !is_float($max)) {
			throw new \TypeError(sprintf('%s() expects parameter %d to be integer or float, %s given', __FUNCTION__, 3, gettype($max)));
		} // end if
		if ($min > $max) {
			throw new \Exception(__FUNCTION__ . '(): Minimum value must be less than or equal to the maximum value');
		} // end if
		return min(max($num, $min), $max);
	} // end of the 'clamp()' function

} // end if

function RB_html_errors_enabled() : bool {
	return (bool) ini_get('html_errors');
} // end of the 'RB_html_errors_enabled()' function

function RB_get_var_dump(mixed $variable) : string {
	ob_start();
	if (function_exists('xdebug_var_dump')) {
		@xdebug_var_dump($variable);
	} else {
		@var_dump($variable);
	} // end if
	$content = ob_get_clean();
	return (RB_IS_CLI || RB_XDEBUG_EXTENSION_LOADED && RB_html_errors_enabled()) ? $content : sprintf('<pre>%s</pre>', htmlspecialchars($content, ENT_QUOTES, 'ISO-8859-1'));
} // end of the 'RB_get_var_dump()' function

function RB_var_dump(mixed $arg) : void {
	$args = func_get_args();
	foreach ($args as $arg) {
		echo RB_get_var_dump($arg);
	} // end foreach
} // end of the 'RB_var_dump()' function

function RB_create_instance_without_constructor(object|string $class) : ?object {
	try {
		$ref_class = new \ReflectionClass($class);
		return $ref_class->newInstanceWithoutConstructor();
	} catch (\ReflectionException $e) {
	} // end try
	return NULL;
} // end of the 'RB_create_instance_without_constructor()' function

if (RB_IS_WINDOWS) {

	function RB_canonpath(string $path) : string {
		$path = str_replace('/', '\\', $path);
		$volume = NULL;
		if (preg_match('@^(?:([A-Za-z]:)|(\\\\{2}[^\\\\]+\\\\[^\\\\]+))(.*)@s', $path, $matches)) {
			$volume = strtoupper($matches[1]);
			$path = $matches[3];
		} // end if
		$path = preg_replace('@\\\\{2,}@', '\\', $path);
		$is_absolute_path = strlen($path) && $path[0] == '\\';
		$dirStack = [];
		$pieces = explode('\\', $path);
		$count = 0;
		foreach ($pieces as $piece) {
			if ($piece == '' || $piece == '.') continue;
			if ($piece == '..') {
				if ($is_absolute_path || $count > 0 && $dirStack[$count - 1] != '..') {
					if ($count > 0) {
						array_pop($dirStack);
						$count--;
					} // end if
				} else {
					$dirStack[$count++] = '..';
				} // end if
				continue;
			} // end if
			$dirStack[$count++] = $piece;
		} // end foreach
		$path = implode('\\', $dirStack);
		if ($is_absolute_path) {
			$path = '\\' . $path;
		} // end if
		if (isset($volume)) {
			$path = $volume . $path;
		} // end if
		if (strlen($path) == 0) {
			$path = '.';
		} // end if
		return $path;
	} // end of the 'RB_canonpath()' function

	function RB_rel2abs(string $path, string $base = NULL) : string {
		$path = RB_canonpath($path);
		$regexp = '@^([A-Z]:|\\\\{2}[^\\\\]+\\\\[^\\\\]+)\\\\@';
		if (preg_match($regexp, $path)) {
			return $path;
		} // end if
		if (isset($base)) {
			$base = RB_canonpath($base);
			if (!preg_match($regexp, $base)) {
				$base = RB_rel2abs($base);
			} // end if
		} else {
			$base = getcwd();
		} // end if
		$regexp = '@^([A-Z]:|\\\\{2}[^\\\\]+\\\\[^\\\\]+)?(.*)@s';
		preg_match($regexp, $path, $matches1);
		preg_match($regexp, $base, $matches2);
		if (strlen($matches1[1])) {
			if (strcasecmp($matches1[1], $matches2[1]) == 0) {
				$path = $base . '\\' . $matches1[2];
			} else {
				$path = $matches1[1] . '\\' . $matches1[2];
			} // end if
		} else {
			if (preg_match('@^\\\\@', $path)) {
				$path = $matches2[1] . $path;
			} else {
				$path = $base . '\\' . $path;
			} // end if
		} // end if
		return RB_canonpath($path);
	} // end of the 'RB_rel2abs()' function

	function RB_abs2rel(string $path, string $base = NULL) : string {
		$path = RB_rel2abs($path);
		if (isset($base)) {
			$base = RB_rel2abs($base);
		} else {
			$base = getcwd();
		} // end if
		preg_match('@^([A-Z]:|\\\\{2}[^\\\\]+\\\\[^\\\\]+)?(.*)@s', $path, $matches1);
		preg_match('@^([A-Z]:|\\\\{2}[^\\\\]+\\\\[^\\\\]+)?(.*)@s', $base, $matches2);
		if (strcasecmp($matches1[1], $matches2[1]) != 0) {
			return $path;
		} // end if
		$path = $matches1[2];
		$base = $matches2[2];
		$pathchunks = explode('\\', $path);
		$basechunks = explode('\\', $base);
		while (count($pathchunks) && count($basechunks) && (strcasecmp($pathchunks[0], $basechunks[0]) == 0)) {
			array_shift($pathchunks);
			array_shift($basechunks);
		} // end while
		$path = implode('\\', $pathchunks);
		$base = str_repeat('..' . '\\', count($basechunks));
		$path = $base . $path;
		return strlen($path) > 0 ? $path : '.';
	} // end of the 'RB_abs2rel()' function

} else {

	function RB_canonpath(string $path) : string {
		$path = preg_replace('@/{2,}@', '/', $path);
		$is_absolute_path = strlen($path) > 0 && $path[0] == '/';
		$dirStack = [];
		$pieces = explode('/', $path);
		$count = 0;
		foreach ($pieces as $piece) {
			if ($piece == '' || $piece == '.') continue;
			if ($piece == '..') {
				if ($is_absolute_path || $count > 0 && $dirStack[$count - 1] != '..') {
					if ($count > 0) {
						array_pop($dirStack);
						$count--;
					} // end if
				} else {
					$dirStack[$count++] = '..';
				} // end if
				continue;
			} // end if
			$dirStack[$count++] = $piece;
		} // end foreach
		$path = implode('/', $dirStack);
		if ($is_absolute_path) {
			$path = '/' . $path;
		} // end if
		if (strlen($path) == 0) {
			$path = '.';
		} // end if
		return $path;
	} // end of the 'RB_canonpath()' function

	function RB_rel2abs(string $path, string $base = NULL) : string {
		$path = RB_canonpath($path);
		$regexp = '@^/@';
		if (preg_match($regexp, $path)) {
			return $path;
		} // end if
		if (isset($base)) {
			$base = RB_canonpath($base);
			if (!preg_match($regexp, $base)) {
				$base = RB_rel2abs($base);
			} // end if
		} else {
			$base = getcwd();
		} // end if
		$path = $base . '/' . $path;
		return RB_canonpath($path);
	} // end of the 'RB_rel2abs()' function

	function RB_abs2rel(string $path, string $base = NULL) : string {
		$path = RB_rel2abs($path);
		if (isset($base)) {
			$base = RB_rel2abs($base);
		} else {
			$base = getcwd();
		} // end if
		$pathchunks = explode('/', $path);
		$basechunks = explode('/', $base);
		while (count($pathchunks) && count($basechunks) && (strcmp($pathchunks[0], $basechunks[0]) == 0)) {
			array_shift($pathchunks);
			array_shift($basechunks);
		} // end while
		$path = implode('/', $pathchunks);
		$base = str_repeat('..' . '/', count($basechunks));
		$path = $base . $path;
		return strlen($path) > 0 ? $path : '.';
	} // end of the 'RB_abs2rel()' function

} // end if

function RB_preg_token(string $string, string $regexp, ?int &$offset) : ?array {
	if (!isset($offset)) $offset = 0;
	if ($offset < strlen($string)) {
		$ntoken = 0;
		$named_matches = [];
		if (preg_match($regexp, $string, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			if ($matches[0][1] == $offset) {
				$next_skip = FALSE;
				foreach ($matches as $k => $v) {
					if ($next_skip) {
						$next_skip = FALSE;
						continue;
					} // end if
					if (is_string($k)) {
						$next_skip = TRUE;
						if ($v[1] >= 0) {
							$named_matches[$k] = $v[0];
						} // end if
					} elseif ($k > 0) {
						++$ntoken;
					} // end if
				} // end foreach
				$value = $matches[$ntoken][0];
			} else {
				$value = substr($string, $offset, $matches[0][1] - $offset);
			} // end if
		} else {
			$value = substr($string, $offset);
		} // end if
		$token = [
			'ntoken'		=> $ntoken,
			'value'			=> $value,
			'named_matches'	=> $named_matches,
			'offset'		=> $offset
		];
		$offset += strlen($value);
		return $token;
	} // end if
	return NULL;
} // end of the 'RB_preg_token()' function
