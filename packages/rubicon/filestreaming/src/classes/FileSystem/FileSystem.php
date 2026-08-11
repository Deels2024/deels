<?php

declare(strict_types=1);

namespace RB\FileSystem;

use RB\Common;
use RB\Exception\RubiconException;

final class FileSystem {

	public const	BLOCK_SIZE				= 8192;

	public const	MASK_IFMT				= 0170000;	// bitmask for the file type bitfields
	public const	MASK_IFSOCK				= 0140000;	// socket
	public const	MASK_IFLNK				= 0120000;	// symbolic link
	public const	MASK_IFREG				= 0100000;	// regular file
	public const	MASK_IFBLK				= 0060000;	// block device
	public const	MASK_IFDIR				= 0040000;	// directory
	public const	MASK_IFCHR				= 0020000;	// character device
	public const	MASK_IFIFO				= 0010000;	// FIFO
	public const	MASK_ISUID				= 0004000;	// set UID bit
	public const	MASK_ISGID				= 0002000;	// set-group-ID bit
	public const	MASK_ISVTX				= 0001000;	// sticky bit
	public const	MASK_IRWXU				= 00700;	// mask for file owner permissions
	public const	MASK_IRUSR				= 00400;	// owner has read permission
	public const	MASK_IWUSR				= 00200;	// owner has write permission
	public const	MASK_IXUSR				= 00100;	// owner has execute permission
	public const	MASK_IRWXG				= 00070;	// mask for group permissions
	public const	MASK_IRGRP				= 00040;	// group has read permission
	public const	MASK_IWGRP				= 00020;	// group has write permission
	public const	MASK_IXGRP				= 00010;	// group has execute permission
	public const	MASK_IRWXO				= 00007;	// mask for permissions for others (not in group)
	public const	MASK_IROTH				= 00004;	// others have read permission
	public const	MASK_IWOTH				= 00002;	// others have write permission
	public const	MASK_IXOTH				= 00001;	// others have execute permission
	public const	MASK_IRWXA				= 00777;	// mask for permissions for all (owner, group and others)

	// Open for reading only; place the file pointer at the beginning of the file.
	public const	O_RDONLY				= 'rb';
	// Open for reading and writing; place the file pointer at the beginning of the file.
	public const	O_RDWR					= 'r+b';
	// Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length.
	// If the file does not exist, attempt to create it.
	public const	O_WRONLY_CREAT_TRUNC	= 'wb';
	// Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length.
	// If the file does not exist, attempt to create it.
	public const	O_RDWR_CREAT_TRUNC		= 'w+b';
	// Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
	// In this mode, fseek() has no effect, writes are always appended.
	public const	O_WRONLY_CREAT_APPEND	= 'ab';
	// Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
	// In this mode, fseek() only affects the reading position, writes are always appended.
	public const	O_RDWR_CREAT_APPEND		= 'a+b';
	// Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen()
	// call will fail by returning FALSE and generating an error of level E_WARNING. If the file does not exist, attempt to create it.
	// This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
	public const	O_WRONLY_CREAT_EXCL		= 'xb';
	// Create and open for reading and writing; otherwise it has the same behavior as 'x'.
	public const	O_RDWR_CREAT_EXCL		= 'x+b';
	// Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither truncated
	// (as opposed to 'w'), nor the call to this function fails (as is the case with 'x'). The file pointer is positioned
	// on the beginning of the file. This may be useful if it's desired to get an advisory lock (see flock()) before attempting
	// to modify the file, as using 'w' could truncate the file before the lock was obtained (if truncation is desired, ftruncate()
	// can be used after the lock is requested).
	public const	O_WRONLY_CREAT			= 'cb';
	// Open the file for reading and writing; otherwise it has the same behavior as 'c'.
	public const	O_RDWR_CREAT			= 'c+b';

	public const	GLOB_READABLE			=  1;
	public const	GLOB_WRITABLE			=  2;
	public const	GLOB_EXECUTABLE			=  4;
	public const	GLOB_SKIP_LINKS			=  8;
	public const	GLOB_SKIP_FILES			= 16;
	public const	GLOB_SKIP_DIRS			= 32;
	public const	GLOB_GROUP				= 64;

	public static function is_url(string $filename) : bool {
		return (bool) preg_match('@^(?:[^:/?#]+://|data:)@', $filename);
	} // end of the 'is_url()' method

	public static function check_access_mode(string $mode) : bool {
		return (bool) preg_match('@^[rwaxc]\+?[bt]?$@', $mode);
	} // end of the 'check_access_mode()' method

	public static function is_read_mode(string $mode) : bool {
		return strpos($mode, 'r') !== FALSE || strpos($mode, '+') !== FALSE;
	} // end of the 'is_read_mode()' method

	public static function is_writ_mode(string $mode) : bool {
		return strpos($mode, 'w') !== FALSE || strpos($mode, 'a') !== FALSE || strpos($mode, 'x') !== FALSE || strpos($mode, 'c') !== FALSE || strpos($mode, '+') !== FALSE;
	} // end of the 'is_writ_mode()' method

	public static function is_rdwr_mode(string $mode) : bool {
		return strpos($mode, '+') !== FALSE;
	} // end of the 'is_rdwr_mode()' method

	public static function is_trunc_mode(string $mode) : bool {
		return strpos($mode, 'w') !== FALSE;
	} // end of the 'is_trunc_mode()' method

	public static function is_creat_mode(string $mode) : bool {
		return strpos($mode, 'w') !== FALSE || strpos($mode, 'a') !== FALSE || strpos($mode, 'x') !== FALSE || strpos($mode, 'c') !== FALSE;
	} // end of the 'is_creat_mode()' method

	public static function is_excl_mode(string $mode) : bool {
		return strpos($mode, 'x') !== FALSE;
	} // end of the 'is_excl_mode()' method

	public static function is_append_mode(string $mode) : bool {
		return strpos($mode, 'a') !== FALSE;
	} // end of the 'is_append_mode()' method

	public static function is_bin_mode(string $mode) : bool {
		return strpos($mode, 'b') !== FALSE;
	} // end of the 'is_bin_mode()' method

	public static function is_pattern(string $path) : bool {
		return strpos($path, '*') !== FALSE || strpos($path, '?') !== FALSE;
	} // end of the 'is_pattern()' method

	public static function pattern_to_regexp(string $pattern) : string {
		static $callback_func;
		if (!isset($callback_func)) {
			$callback_func = function ($matches) {
				if (count($matches) > 1) {
					$r = RB_IS_WINDOWS ? '[^\\\\]' : '[^/]';
					$n = substr_count($matches[1], '?');
					if (strpos($matches[1], '*') !== FALSE) {
						if ($n == 0) {
							$r .= '*';
						} elseif ($n == 1) {
							$r .= '+';
						} else {
							$r .= '{' . $n . ',}';
						} // end if
					} else {
						if ($n > 1) {
							$r .= '{' . $n . '}';
						} // end if
					} // end if
					return $r;
				} // end if
				return preg_quote($matches[0], '@');
			};
		} // end if
		return preg_replace_callback('@[^\*\?]+|([\*\?]+)@', $callback_func, $pattern);
	} // end of the 'pattern_to_regexp()' method

	public static function parse_pattern(string $path) : ?array {
		static $cache;
		$chache_key = $path = RB_rel2abs($path);
		if (isset($cache[$chache_key])) {
			return $cache[$chache_key];
		} // end if
		$pattern = $path_regexp = NULL;
		if (preg_match(RB_IS_WINDOWS ? '@\\\\[^\*\?\\\\]*[\*\?].*$@' : '@/[^\*\?/]*[\*\?].*$@', $path, $m)) {
			$pattern = $m[0];
			$length_path = strlen($path) - strlen($pattern);
			if ($length_path > 0) {
				$path = substr($path, 0, $length_path);
			} else {
				$path = NULL;
			} // end if
			$path_regexp = static::pattern_to_regexp($pattern);
		} // end if
		$fullpath_regexp = '@^' . preg_quote($path, '@');
		if (isset($path_regexp)) {
			$fullpath_regexp .= $path_regexp;
			$path_regexp = '@' . $path_regexp . '$@u';
			if (RB_IS_WINDOWS) {
				$path_regexp .= 'i';
			} // end if
		} // end if
		$fullpath_regexp .= '$@u';
		if (RB_IS_WINDOWS) {
			$fullpath_regexp .= 'i';
		} // end if
		return $cache[$chache_key] = ['path' => $path, 'pattern' => $pattern, 'path_regexp' => $path_regexp, 'fullpath_regexp' => $fullpath_regexp];
	} // end of the 'parse_pattern()' method

	public static function check_pattern(string $path, string $pattern) : bool {
		$path = RB_rel2abs($path);
		$pattern_data = static::parse_pattern($pattern);
		return (bool) preg_match($pattern_data['fullpath_regexp'], $path);
	} // end of the 'check_pattern()' method

	public static function fopen(string $filename, string $mode, bool $use_include_path = NULL, $context = NULL) {
		if (!static::check_access_mode($mode)) {
			throw new RubconException(sprintf('%s(): Invalid access mode: \'%s\'', __METHOD__, $mode));
		} // end if
		$use_include_path = $use_include_path ?? FALSE;
		if (!static::is_url($filename)) {
			$filename = RB_rel2abs($filename);
		} // end if
		if (isset($context)) {
			if (!Common::is_stream_context($context)) {
				throw new RubconException(sprintf('%s(): The argument \'%s\' must be a stream context', __METHOD__, '$context'));
			} // end if
			$fh = @fopen($filename, $mode, $use_include_path, $context);
		} else {
			$fh = @fopen($filename, $mode, $use_include_path);
		} // end if
		if (!is_resource($fh)) {
			throw new RubconException(sprintf('%s(): Failed to open the file: \'%s\'', __METHOD__, $filename));
		} // end if
		return $fh;
	} // end of the 'fopen()' method

	public static function file_serialize(string $filename, $value) : int|false {
		return @file_put_contents($filename, serialize($value));
	} // end of the 'file_serialize()' method

	public static function file_unserialize(string $filename) : mixed {
		$filename = (string) $filename;
		if (is_string($ser_data = @file_get_contents($filename))) {
			return @unserialize($ser_data);
		} // end if
	} // end of the 'file_unserialize()' method

	public static function coordinate_char($stream, int $pos, int $max_block_size = NULL) : array {
		if (!Common::is_stream($stream)) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be a stream resource', __METHOD__, '$stream'));
		} // end if
		$meta_data = stream_get_meta_data($stream);
		if (!isset($meta_data['seekable']) || !$meta_data['seekable']) {
			throw new RubconException(sprintf('%s(): Stream is not seekable', __METHOD__));
		} // end if
		$saved_pos = ftell($stream);
		fseek($stream, 0, SEEK_END);
		$length = ftell($stream);
		$row = $col = NULL;
		if ($pos >= 0 && $pos < $length) {
			$max_block_size = $max_block_size ?? 1048576;
			$max_block_size = max($max_block_size, 1);
			$row = 1;
			$col = 0;
			$offset = 0;
			fseek($stream, $offset);
			while ($pos >= $offset) {
				$block_size = min($max_block_size, $pos - $offset + 1);
				$block = fread($stream, $block_size);
				$offset += $block_size;
				if ($pos >= $offset && substr($block, -1) == "\r") {
					$char = fread($stream, 1);
					if ($char == "\n") {
						$block .= $char;
						++$block_size;
						++$offset;
					} else {
						fseek($stream, $offset);
					} // end if
				} // end if
				$block_offset = 0;
				while (preg_match('@\\r\\n?|\\n@S', $block, $matches, PREG_OFFSET_CAPTURE, $block_offset)) {
					$col = 0;
					++$row;
					$block_offset = $matches[0][1] + strlen($matches[0][0]);
				} // end while
				$col += $block_size - $block_offset;
			} // end while
		} // end if
		fseek($stream, $saved_pos);
		return [$row, $col];
	} // end of the 'coordinate_char()' method

	public static function search_regexp($stream, string $regexp, int $offset = NULL, int $block_size = NULL, string $check_regexp = NULL, int $cutting_depth = NULL) : ?array {
		if (!Common::is_stream($stream)) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be a stream resource', __METHOD__, '$stream'));
		} // end if
		$meta_data = stream_get_meta_data($stream);
		if (!isset($meta_data['seekable']) || !$meta_data['seekable']) {
			throw new RubconException(sprintf('%s(): Stream is not seekable', __METHOD__));
		} // end if
		if (isset($offset)) {
			if ($offset < 0) {
				fseek($stream, $offset, SEEK_END);
			} else {
				fseek($stream, $offset);
			} // end if
		} // end if
		$block_size = $block_size ?? self::BLOCK_SIZE;
		if ($block_size <= 0) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be greater than 0', __METHOD__, '$block_size'));
		} // end if
		$cutting_depth = $cutting_depth ?? 0;
		if ($cutting_depth < 0) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be greater than or equal to 0', __METHOD__, '$cutting_depth'));
		} // end if
		$prev_block = '';
		while ($block_len = strlen($block = fread($stream, $block_size))) {
			if (isset($check_regexp)) {
				$min_cutting_depth = min($block_len, $cutting_depth);
				$cutting_count = 0;
				do {
					$r = (bool) preg_match($check_regexp, $block);
					if (!$r) {
						--$block_len;
						++$cutting_count;
						$block = substr($block, 0, -1);
						fseek($stream, -1, SEEK_CUR);
					} // end if
				} while (!$r && $cutting_count < $min_cutting_depth && $block_len > 0);
				if (!$r) {
					fseek($stream, -$block_len, SEEK_CUR);
					break;
				} // end if
			} // end if
			$tmp_block = $prev_block . $block;
			$prev_block = $block;
			$block = $tmp_block;
			$offset = ftell($stream) - strlen($block);
			if (preg_match($regexp, $block, $m, PREG_OFFSET_CAPTURE)) {
				foreach ($m as $k => $v) {
					$m[$k][1] += $offset;
				} // end foreach
				fseek($stream, $m[0][1] + strlen($m[0][0]));
				return $m;
			} // end if
		} // end while
		return NULL;
	} // end of the 'search_regexp()' method

	public static function utf8_search_regexp($stream, string $regexp, int $offset = NULL, int $block_size = NULL) : ?array {
		return self::search_regexp($stream, $regexp, $offset, $block_size, '@@u', 4);
	} // end of the 'utf8_search_regexp()' method

	public static function search_regexp_all($stream, string $regexp, int $offset = NULL, int $block_size = NULL, string $check_regexp = NULL, int $cutting_depth = NULL) : array {
		if (!Common::is_stream($stream)) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be a stream resource', __METHOD__, '$stream'));
		} // end if
		$meta_data = stream_get_meta_data($stream);
		if (!isset($meta_data['seekable']) || !$meta_data['seekable']) {
			throw new RubconException(sprintf('%s(): Stream is not seekable', __METHOD__));
		} // end if
		if (isset($offset)) {
			if ($offset < 0) {
				fseek($stream, $offset, SEEK_END);
			} else {
				fseek($stream, $offset);
			} // end if
		} // end if
		$block_size = $block_size ?? self::BLOCK_SIZE;
		if ($block_size <= 0) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be greater than 0', __METHOD__, '$block_size'));
		} // end if
		$cutting_depth = $cutting_depth ?? 0;
		if ($cutting_depth < 0) {
			throw new RubconException(sprintf('%s(): The argument \'%s\' must be greater than or equal to 0', __METHOD__, '$cutting_depth'));
		} // end if
		$result = [];
		$prev_block = '';
		while ($block_len = strlen($block = fread($stream, $block_size))) {
			if (isset($check_regexp)) {
				$min_cutting_depth = min($block_len, $cutting_depth);
				$cutting_count = 0;
				do {
					$r = (bool) preg_match($check_regexp, $block);
					if (!$r) {
						--$block_len;
						++$cutting_count;
						$block = substr($block, 0, -1);
						fseek($stream, -1, SEEK_CUR);
					} // end if
				} while (!$r && $cutting_count < $min_cutting_depth && $block_len > 0);
				if (!$r) {
					fseek($stream, -$block_len, SEEK_CUR);
					break;
				} // end if
			} // end if
			$tmp_block = $prev_block . $block;
			$prev_block = $block;
			$block = $tmp_block;
			$offset = ftell($stream) - strlen($block);
			if (preg_match($regexp, $block, $m, PREG_OFFSET_CAPTURE)) {
				$prev_block = '';
				foreach ($m as $k => $v) {
					$m[$k][1] += $offset;
				} // end foreach
				fseek($stream, $m[0][1] + strlen($m[0][0]));
				$result[] = $m;
			} // end if
		} // end while
		return $result;
	} // end of the 'search_regexp_all()' method

	public static function utf8_search_regexp_all($stream, string $regexp, int $offset = NULL, int $block_size = NULL) : array {
		return self::search_regexp_all($stream, $regexp, $offset, $block_size, '@@u', 4);
	} // end of the 'utf8_search_regexp_all()' method

	public static function extname(string $path) : ?string {
		$path = basename($path);
		if (preg_match('@.*\.([^.]+)$@s', $path, $matches)) {
			return $matches[1];
		} // end if
		return '';
	} // end of the 'extname()' method

	public static function replace_extension(string $path, string $extension) : string {
		$path = preg_replace('@\.[^.]*$@', '', $path);
		if (strlen($extension) > 0) {
			$path .= '.' . $extension;
		} // end if
		return $path;
	} // end of the 'replace_extension()' method

	public static function add_extension(string $path, string $extension) : string {
		if (preg_match('@\.[^.]*$@', $path, $matches)) {
			if ($matches[0] == '.') {
				if (strlen($extension) > 0) {
					$path .= $extension;
				} else {
					$path = substr($path, 0, -1);
				} // end if
			} // end if
		} elseif (strlen($extension) > 0 && $extension != '.') {
			if ($extension[0] != '.') $extension = '.' . $extension;
			$path .= $extension;
		} // end if
		return $path;
	} // end of the 'add_extension()' method

	public static function find_mime_type(string $filename) : ?array {
		if (is_file($filename) && is_array($file_info = FileFormatIdentifier::detect_mime_type($filename)) && isset($file_info['mime_type'])) {
			return is_array($file_info['mime_type']) ? $file_info['mime_type'] : [$file_info['mime_type']];
		} // end if
		if (strlen($ext = static::extname($filename)) > 0) {
			return MimeType::get_mime_type($ext);
		} // end if
		return [MimeType::DEFAULT_MIME_TYPE];
	} // end of the 'find_mime_type()' method

} // end of the 'FileSystem' class
