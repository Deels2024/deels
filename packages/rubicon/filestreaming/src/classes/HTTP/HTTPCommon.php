<?php

declare(strict_types=1);

namespace RB\HTTP;

use RB\Core\TraitCommonStatic;
use RB\Exception\RubiconException;

final class HTTPCommon {

	use TraitCommonStatic;

	public static function parse_ranges(string $range_header, int $entity_body_length) : array|false {
		$entity_body_length = sprintf('%u', $entity_body_length) + 0;
		$range_list = [];
		if ($entity_body_length == 0) {
			return $range_list;
		} // end if
		if (preg_match('@^bytes=([^;]+)@i', $range_header, $match)) {
			$range_set = $match[1];
		} else {
			return FALSE;
		} // end if
		$range_spec_list = preg_split('@,@', $range_set, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($range_spec_list as $range_spec) {
			$range_spec = trim($range_spec);
			if (preg_match('@^(\d+)\-$@', $range_spec, $match)) {
				$first_byte_pos = $match[1];
				if ($first_byte_pos > $entity_body_length) {
					continue;
				} // end if
				$first_pos = $first_byte_pos;
				$last_pos = $entity_body_length - 1;
			} elseif (preg_match('@^(\d+)\-(\d+)$@', $range_spec, $match)) {
				$first_byte_pos = $match[1];
				$last_byte_pos = $match[2];
				if ($last_byte_pos < $first_byte_pos) {
					return FALSE;
				} // end if
				$first_pos = $first_byte_pos;
				$last_pos = min($entity_body_length - 1, $last_byte_pos);
			} elseif (preg_match('@^\-(\d+)$@', $range_spec, $match)) {
				$suffix_length = $match[1];
				if ($suffix_length == 0) {
					continue;
				} // end if
				$first_pos = $entity_body_length - min($entity_body_length, $suffix_length);
				$last_pos = $entity_body_length - 1;
			} else {
				return FALSE;
			} // end if
			$range_list[] = [$first_pos + 0, $last_pos + 0];
		} // end foreach
		return $range_list;
	} // end of the 'parse_ranges()' method

	public static function http_chunk(string $data = '') : string {
		return sprintf("%x\r\n%s\r\n", strlen($data), $data);
	} // end of the 'http_chunk()' method

	public static function header(string $string, bool $replace = NULL, int $http_response_code = NULL) : void {
		$replace = $replace ?? TRUE;
		if (!RB_IS_CLI) {
			if (isset($http_response_code)) {
				header($string, $replace, $http_response_code);
			} else {
				header($string, $replace);
			} // end if
		} // end if
	} // end of the 'header()' method

	public static function http_date(int $time = NULL) : string {
		return date('r', $time);
	} // end of the 'http_date()' method

} // end of the 'HTTPCommon' class
