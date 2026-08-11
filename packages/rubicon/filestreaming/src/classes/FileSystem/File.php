<?php

declare(strict_types=1);

namespace RB\FileSystem;

use RB\HTTP\HTTPCommon;
use RB\Exception\RubiconException;

class File extends AbstractFile {

	public const	CHUNKSIZE	= 4096;

	protected function __construct(string $filename, string $mode = NULL, bool $use_lock = NULL, int $lock = NULL, bool $locks_block = NULL) {
		$is_url = FileSystem::is_url($filename);
		if (!$is_url) {
			$filename = RB_rel2abs($filename);
			$mode = $mode ?? FileSystem::O_RDONLY;
			switch ($mode) {
				case FileSystem::O_RDONLY:
				case FileSystem::O_RDWR:
					if (!is_file($filename)) {
						throw new RubconException(sprintf('%s(): The file \'%s\' does not exist', __METHOD__, $filename));
					} // end if
					break;
				case FileSystem::O_WRONLY_CREAT_TRUNC:
				case FileSystem::O_RDWR_CREAT_TRUNC:
				case FileSystem::O_WRONLY_CREAT_APPEND:
				case FileSystem::O_RDWR_CREAT_APPEND:
				case FileSystem::O_WRONLY_CREAT_EXCL:
				case FileSystem::O_RDWR_CREAT_EXCL:
				case FileSystem::O_WRONLY_CREAT:
				case FileSystem::O_RDWR_CREAT:
					if (is_file($filename)) {
						if (!is_writable($filename)) {
							throw new RubconException(sprintf('%s(): The file \'%s\' is not writable', __METHOD__, $filename));
						} // end if
					} else {
						$dir = dirname($filename);
						mkdir($dir, NULL, TRUE);
						if (!is_dir($dir)) {
							throw new RubconException(sprintf('%s(): Failed to create the path: \'%s\'', __METHOD__, $dir));
						} // end if
					} // end if
					break;
				default:
					throw new RubconException(sprintf('%s(): Invalid access mode: \'%s\'', __METHOD__, $mode));
			} // end switch
		} // end if
		parent::__construct($filename, $mode);
		$use_lock = $use_lock ?? TRUE;
		if (!$is_url && $use_lock) {
			if (!$this->lock($lock, $locks_block)) {
				throw new RubconException(sprintf('%s(): Failed to lock the file: \'%s\'', __METHOD__, $filename));
			} // end if
		} // end if
	} // end of the '__construct()' constructor

	public function output_chunked_encoded(int $chunksize = NULL) : static {
		if (!is_resource($this->fh)) {
			throw new RubconException(sprintf('%s(): The variable \'%s\' must be a stream resource', __METHOD__, '$this->fh'));
		} // end if
		if (FileSystem::is_read_mode($this->mode)) {
			$chunksize = $chunksize ?? static::CHUNKSIZE;
			header('Transfer-Encoding: chunked');
			while (!@feof($this->fh)) {
				$chunk = @fread($this->fh, $chunksize);
				if (strlen($chunk)) {
					echo HTTPCommon::http_chunk($chunk);
				} // end if
			} // end while
			echo HTTPCommon::http_chunk('');
		} // end if
		return $this;
	} // end of the 'output_chunked_encoded()' method

	public function save_as(string $dst_file, bool $overwrite = NULL, int $file_mode = NULL, int $file_mtime = NULL) : static {
		$dst_file = RB_rel2abs($dst_file);
		$overwrite = $overwrite ?? TRUE;
		$file_mode = $file_mode ?? 0775;
		$file_mode &= FileSystem::MASK_IRWXA;
		$file_mtime = $file_mtime ?? time();
		if ($overwrite && is_file($dst_file)) {
			unlink($dst_file);
		} // end if
		if (file_exists($dst_file)) {
			throw new RubconException(sprintf('%s(): The file \'%s\' already exists', __METHOD__, $dst_file));
		} // end if
		$current_offset = $this->current_offset;
		$this->rewind();
		$file_obj = new self($dst_file, FileSystem::O_WRONLY_CREAT_TRUNC);
		while ($buffer = $this->fread(FileSystem::BLOCK_SIZE)) {
			$file_obj->fwrite($buffer);
		} // end while
		unset($file_obj);
		if (is_file($dst_file)) {
			touch($dst_file, $file_mtime);
			chmod($dst_file, $file_mode);
		} // end if
		$this->fseek($current_offset);
		return $this;
	} // end of the 'save_as()' method

} // end of the 'File' class

?>