<?php

declare(strict_types=1);

namespace RB;

use RB\Core\TraitCommonStatic;
use RB\Exception\RubiconException;

final class Common {

	use TraitCommonStatic;

	public const	DEFAULT_HEX_ID_LENGTH	= 32;

	public static function is_stream(mixed $resource) : bool {
		return is_resource($resource) && !strcasecmp(get_resource_type($resource), 'stream');
	} // end of the 'is_stream()' method

	public static function is_stream_context(mixed $resource) : bool {
		return is_resource($resource) && !strcasecmp(get_resource_type($resource), 'stream-context');
	} // end of the 'is_stream_context()' method

	public static function ob_end_clean_all() : void {
		while (ob_get_level() > 0) {
			ob_clean();
			ob_end_clean();
		} // end while
	} // end of the 'ob_end_clean_all()' method

	public static function ob_end_flush_all() : void {
		while (ob_get_level() > 0) ob_end_flush();
	} // end of the 'ob_end_flush_all()' method

	public static function ob_get_clean_all(bool $reverse_flag = NULL) : string {
		$reverse_flag = $reverse_flag ?? FALSE;
		$buffer = '';
		while (ob_get_level() > 0) {
			$buffer_part = ob_get_clean();
			if ($reverse_flag) {
				$buffer .= $buffer_part;
			} else {
				$buffer = $buffer_part . $buffer;
			} // end if
		} // end if
		return $buffer;
	} // end of the 'ob_get_clean_all()' method

	public static function ob_get_flush_all(bool $reverse_flag = NULL) : string {
		echo ($buffer = self::ob_get_clean_all($reverse_flag));
		return $buffer;
	} // end of the 'ob_get_flush_all()' method

	public static function unsigned_int($number) : int {
		$number = (int) $number;
		return $number & RB_INT_MAX;
	} // end of the 'unsigned_int()' method

	public static function random_hex_id(int $length = NULL) : string {
		$length = $length ?? self::DEFAULT_HEX_ID_LENGTH;
		$length = max(1, $length);
		return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
	} // end of the 'random_hex_id()' method

} // end of the 'Common' class
