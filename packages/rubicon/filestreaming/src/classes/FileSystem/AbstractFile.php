<?php

declare(strict_types=1);

namespace RB\FileSystem;

use RB\Core\ObjectB;
use RB\Exception\RubiconException;

abstract class AbstractFile extends ObjectB {

	protected	$fh,
				$filename,
				$is_locked,
				$is_local,
				$is_supports_lock,
				$is_seekable,
				$mode;

	protected function __construct(string $filename, string $mode = NULL, bool $use_include_path = NULL, array $context_options = NULL, array $context_params = NULL) {
		parent::__construct();
		$mode = $mode ?? FileSystem::O_RDONLY;
		$use_include_path = $use_include_path ?? FALSE;
		$context_options = $context_options ?? [];
		$context_params = $context_params ?? [];
		if (!FileSystem::check_access_mode($mode)) {
			throw new RubconException(sprintf('%s(): Invalid access mode: \'%s\'', __METHOD__, $mode));
		} // end if
		$context = @stream_context_create($context_options, $context_params);
		if (!is_resource($context)) {
			throw new RubconException(sprintf('%s(): Failed to create a stream context', __METHOD__));
		} // end if
		$this->fh				= FileSystem::fopen($filename, $mode, $use_include_path, $context);
		$this->filename			= $filename;
		$this->mode				= $mode;
		$this->is_locked		= FALSE;
		$this->is_local			= stream_is_local($this->fh);
		$this->is_supports_lock	= stream_supports_lock($this->fh);
		$this->is_seekable		= is_array($meta_data = $this->_meta_data()) && isset($meta_data['seekable']) ? $meta_data['seekable'] : FALSE;
	} // end of the '__construct()' constructor

	public function __destruct() {
		if (is_resource($this->fh)) {
			$this->fflush();
			@fclose($this->fh);
		} // end if
	} // end of the '__destruct()' destructor

	public function __get(string $varname) : mixed {
		switch ($varname) {
			case 'fh':
			case 'filename':
			case 'is_locked':
			case 'is_local':
			case 'is_supports_lock':
			case 'is_seekable':
			case 'mode':
				return $this->$varname;
			case 'eof':
			case 'feof':
				return $this->_feof();
			case 'is_read_mode':
				return FileSystem::is_read_mode($this->mode);
			case 'is_writ_mode':
				return FileSystem::is_writ_mode($this->mode);
			case 'is_rdwr_mode':
				return FileSystem::is_rdwr_mode($this->mode);
			case 'is_trunc_mode':
				return FileSystem::is_trunc_mode($this->mode);
			case 'is_creat_mode':
				return FileSystem::is_creat_mode($this->mode);
			case 'is_excl_mode':
				return FileSystem::is_excl_mode($this->mode);
			case 'is_append_mode':
				return FileSystem::is_append_mode($this->mode);
			case 'is_bin_mode':
				return FileSystem::is_bin_mode($this->mode);
			case 'is_url':
				return FileSystem::is_url($this->filename);
			case 'ftell':
			case 'current_pos':
			case 'current_offset':
				return $this->_ftell();
			case 'stat':
				return $this->_stat();
			case 'meta_data':
				return $this->_meta_data();
			case 'size':
				return $this->_size();
			default:
				return parent::__get($varname);
		} // end switch
	} // end of the '__get()' method

	public function get_data_dump() : array {
		return array_merge([
			'filename'			=> $this->filename,
			'is_locked'			=> $this->is_locked,
			'is_local'			=> $this->is_local,
			'is_supports_lock'	=> $this->is_supports_lock,
			'is_seekable'		=> $this->is_seekable,
			'mode'				=> $this->mode,
			'eof'				=> $this->eof,
			'feof'				=> $this->feof,
			'is_read_mode'		=> $this->is_read_mode,
			'is_writ_mode'		=> $this->is_writ_mode,
			'is_rdwr_mode'		=> $this->is_rdwr_mode,
			'is_trunc_mode'		=> $this->is_trunc_mode,
			'is_creat_mode'		=> $this->is_creat_mode,
			'is_excl_mode'		=> $this->is_excl_mode,
			'is_append_mode'	=> $this->is_append_mode,
			'is_bin_mode'		=> $this->is_bin_mode,
			'is_url'			=> $this->is_url,
			'ftell'				=> $this->ftell,
			'current_pos'		=> $this->current_pos,
			'current_offset'	=> $this->current_offset,
			'stat'				=> $this->stat,
			'meta_data'			=> $this->meta_data,
			'size'				=> $this->size
		], parent::get_data_dump());
	} // end of the 'get_data_dump()' method

	public function rewind() : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return $this->is_seekable && rewind($this->fh);
	} // end of the 'rewind()' method

	public function fseek(int $offset, int $whence = NULL) : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$whence = $whence ?? SEEK_SET;
		return $this->is_seekable && fseek($this->fh, $offset, $whence) === 0;
	} // end of the 'fseek()' method

	public function fwrite(string $string, int $length = NULL) : int|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_writ_mode($this->mode)) {
			$length = $length ?? strlen($string);
			if ($length > 0 && ($bytes = fwrite($this->fh, $string, $length)) !== FALSE && $bytes === $length) {
				$result = $bytes;
			} else {
				throw new RubconException(sprintf('%s(): Failed to write %d B to \'%s\'', __METHOD__, $length, $this->filename));
			} // end if
		} // end if
		return $result;
	} // end of the 'fwrite()' method

	public function fread(int $length) : string|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if ($length > 0 && FileSystem::is_read_mode($this->mode)) {
			$result = fread($this->fh, $length);
		} // end if
		return $result;
	} // end of the 'fread()' method

	public function fgets(int $length = NULL) : string|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			if (isset($length)) {
				if ($length > 0) {
					$result = fgets($this->fh, $length);
				} // end if
			} else {
				$result = fgets($this->fh);
			} // end if
		} // end if
		return $result;
	} // end of the 'fgets()' method

	public function fgetc() : string|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			$result = fgetc($this->fh);
		} // end if
		return $result;
	} // end of the 'fgetc()' method

	public function fscanf(string $format) : array|int|false|null {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			$result = fscanf($this->fh, $format);
		} // end if
		return $result;
	} // end of the 'fscanf()' method

	public function read_buffer(int $size) : string {
		$size = max(0, $size);
		$buffer = $this->fread($size);
		if (!is_string($buffer)) {
			throw new RubconException(sprintf('%s(): Failed to read data from the file: \'%s\'', __METHOD__, $this->filename));
		} // end if
		if (($read_length = strlen($buffer)) < $size) {
			throw new RubconException(sprintf('%s(): Insufficient data. Expected %u B, got %u', __METHOD__, $size, $read_length));
		} // end if
		return $buffer;
	} // end of the 'read_buffer()' method

	public function read_pascal_string() : string {
		$buffer = $this->read_buffer(1);
		$size = ord($buffer);
		$size += 1 - $size % 2;
		$buffer = $this->read_buffer($size);
		return rtrim($buffer, "\0");
	} // end of the 'read_pascal_string()' method

	public function check_string(string ...$strings) : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if ($this->is_seekable && ($current_offset = $this->_ftell()) !== FALSE) {
			foreach ($strings as $string) {
				$checked_string = $this->fread(strlen($string));
				$this->fseek($current_offset);
				if ($checked_string === $string) {
					return TRUE;
				} // end if
			} // end foreach
		} // end if
		return FALSE;
	} // end of the 'check_string()' method

	public function coordinate_char(int $pos, int $max_block_size = NULL) : array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return FileSystem::coordinate_char($this->fh, $pos, $max_block_size);
	} // end of the 'coordinate_char()' method

	public function search_regexp(string $regexp, int $offset = NULL, int $block_size = NULL, string $check_regexp = NULL, int $cutting_depth = NULL) : ?array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return FileSystem::search_regexp($this->fh, $regexp, $offset, $block_size, $check_regexp, $cutting_depth);
	} // end of the 'search_regexp()' method

	public function utf8_search_regexp(string $regexp, int $offset = NULL, int $block_size = NULL) : ?array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return FileSystem::utf8_search_regexp($this->fh, $regexp, $offset, $block_size);
	} // end of the 'utf8_search_regexp()' method

	public function search_regexp_all(string $regexp, int $offset = NULL, int $block_size = NULL, string $check_regexp = NULL, int $cutting_depth = NULL) : array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return FileSystem::search_regexp_all($this->fh, $regexp, $offset, $block_size, $check_regexp, $cutting_depth);
	} // end of the 'search_regexp_all()' method

	public function utf8_search_regexp_all(string $regexp, int $offset = NULL, int $block_size = NULL) : array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return FileSystem::utf8_search_regexp_all($this->fh, $regexp, $offset, $block_size);
	} // end of the 'utf8_search_regexp_all()' method

	public function fflush() : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_writ_mode($this->mode)) {
			$result = fsync($this->fh);
		} // end if
		return $result;
	} // end of the 'fflush()' method

	public function ftruncate(int $size) : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_writ_mode($this->mode)) {
			$this->fflush();
			$result = ftruncate($this->fh, $size);
		} // end if
		return $result;
	} // end of the 'ftruncate()' method

	public function set_read_buffer(int $size) : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			$result = stream_set_read_buffer($this->fh, $size) === 0;
		} // end if
		return $result;
	} // end of the 'set_read_buffer()' method

	public function set_chunk_size(int $size) : int|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$size = clamp($size, 1, PHP_INT_MAX);
		$result = stream_set_chunk_size($this->fh, $size);
		return $result;
	} // end of the 'set_chunk_size()' method

	public function lock(int $lock = NULL, bool $locks_block = NULL) : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if (!$this->is_supports_lock) {
			return FALSE;
		} // end if
		if (!isset($lock)) {
			$lock = FileSystem::is_writ_mode($this->mode) ? LOCK_EX : LOCK_SH;
		} // end if
		$locks_block = $locks_block ?? TRUE;
		$lock |= ($locks_block ? 0 : LOCK_NB);
		return $this->is_locked = flock($this->fh, $lock);
	} // end of the 'lock()' method

	public function unlock() : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if ($this->is_supports_lock && flock($this->fh, LOCK_UN)) {
			$this->is_locked = FALSE;
			return TRUE;
		} // end if
		return FALSE;
	} // end of the 'unlock()' method

	public function filter_append(string $filtername, int $read_write = NULL, mixed $params = NULL) : mixed {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$filtername = strtolower($filtername);
		if (!RB_stream_filter_exists($filtername)) {
			throw new RubconException(sprintf('%s(): The filter \'%s\' is not registered', __METHOD__, $filtername));
		} // end if
		if (isset($read_write)) {
			$read_write = clamp($read_write, 1, 3);
		} else {
			$read_write = 0;
			if (FileSystem::is_read_mode($this->mode)) {
				$read_write += STREAM_FILTER_READ;
			} // end if
			if (FileSystem::is_writ_mode($this->mode)) {
				$read_write += STREAM_FILTER_WRITE;
			} // end if
		} // end if
		$args = [$this->fh, $read_write];
		if (isset($params)) {
			$args[] = $params;
		} // end if
		return @stream_filter_append(...$args);
	} // end of the 'filter_append()' method

	public function filter_prepend(string $filtername, int $read_write = NULL, mixed $params = NULL) : mixed {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$filtername = strtolower($filtername);
		if (!RB_stream_filter_exists($filtername)) {
			throw new RubconException(sprintf('%s(): The filter \'%s\' is not registered', __METHOD__, $filtername));
		} // end if
		if (isset($read_write)) {
			$read_write = clamp($read_write, 1, 3);
		} else {
			$read_write = 0;
			if (FileSystem::is_read_mode($this->mode)) {
				$read_write += STREAM_FILTER_READ;
			} // end if
			if (FileSystem::is_writ_mode($this->mode)) {
				$read_write += STREAM_FILTER_WRITE;
			} // end if
		} // end if
		$args = [$this->fh, $read_write];
		if (isset($params)) {
			$args[] = $params;
		} // end if
		return @stream_filter_prepend(...$args);
	} // end of the 'filter_prepend()' method

	public function get_contents(int $length = NULL, int $offset = NULL) : string|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			$length = $length ?? -1;
			$length = clamp($length, -1, PHP_INT_MAX);
			$offset = $offset ?? -1;
			$offset = clamp($offset, -1, PHP_INT_MAX);
			$result = stream_get_contents($this->fh, $length, $offset);
		} // end if
		return $result;
	} // end of the 'get_contents()' method

	public function fpassthru() : int|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		$result = FALSE;
		if (FileSystem::is_read_mode($this->mode)) {
			$result = fpassthru($this->fh);
		} // end if
		return $result;
	} // end of the 'fpassthru()' method

	protected function _feof() : bool {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return feof($this->fh);
	} // end of the '_feof()' method

	protected function _meta_data() : array {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return stream_get_meta_data($this->fh);
	} // end of the '_meta_data()' method

	protected function _size() : int|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if ($this->is_seekable) {
			$current_offset = ftell($this->fh);
			fseek($this->fh, 0, SEEK_END);
			$size = ftell($this->fh);
			fseek($this->fh, $current_offset);
			return $size;
		} // end if
		return FALSE;
	} // end of the '_size()' method

	protected function _ftell() : int|false {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		return ftell($this->fh);
	} // end of the '_ftell()' method

	protected function _stat() : ?\stdClass {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if ($this->is_local) {
			$this->fflush();
			$stat = array_slice(fstat($this->fh), 13);
			return (object) $stat;
		} // end if
		return NULL;
	} // end of the '_stat()' method

} // end of the 'AbstractFile' class
