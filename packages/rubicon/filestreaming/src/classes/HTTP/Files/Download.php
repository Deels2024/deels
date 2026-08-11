<?php

declare(strict_types=1);

namespace RB\HTTP\Files;

use RB\Core\StaticObjectWithOptions;
use RB\Exception\RubconException;
use RB\HTTP\HTTPStatusCodes;
use RB\HTTP\HTTPCommon;
use RB\FileSystem\MimeType;
use RB\FileSystem\FileSystem;
use RB\Common;
use RB\Exception\RubiconException;

final class Download extends StaticObjectWithOptions {

	public const	DEFAULT_CHUNK_SIZE	= 8192;
	public const	USLEEP_K			= 976.5625;	// 1000000 / 1024

	protected function __construct() {
		$this->_create_option('data_dir',			'./file_download');
		$this->_create_option('enable_partial',		TRUE);
		$this->_create_option('size_limit',			0);
		$this->_create_option('chunksize',			self::DEFAULT_CHUNK_SIZE);
		$this->_create_option('speed_limit',		0);
		$this->_create_option('exec_time_limit',	3600);
		////////
		parent::__construct();
	} // end of the '__construct()' constructor

	protected function _init() : bool {
		$this->options['data_dir'] = RB_rel2abs($this->options['data_dir'], RB_DATA_PATH);
		if (!is_dir($this->options['data_dir'])) {
			throw new RubiconException(sprintf('%s(): The directory \'%s\' does not exist', __METHOD__, $this->options['data_dir']));
		} // end if
		if (!is_writable($this->options['data_dir'])) {
			throw new RubiconException(sprintf('%s(): The directory \'%s\' is not writable', __METHOD__, $this->options['data_dir']));
		} // end if
		$this->options['enable_partial'] = (bool) $this->options['enable_partial'];
		$this->options['size_limit'] = Common::unsigned_int($this->options['size_limit']);
		$this->options['chunksize'] = Common::unsigned_int($this->options['chunksize']);
		$this->options['speed_limit'] = Common::unsigned_int($this->options['speed_limit']);
		$this->options['exec_time_limit'] = Common::unsigned_int($this->options['exec_time_limit']);
		////////
		return TRUE;
	} // end of the '_init()' method

	protected function _get_file(string $filename, bool $enable_download = NULL, string $content_type = NULL, int $lock_flags = NULL, string $filename_attachment = NULL) : void {
		$fsize = filesize($filename);
		$ftime = filemtime($filename);
		if (!is_int($fsize) || $fsize < 0 || !is_int($ftime)) {
			HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_FORBIDDEN, FALSE);
		} // end if
		$fh = FileSystem::fopen($filename, FileSystem::O_RDONLY);
		if (isset($lock_flags) && !flock($fh, $lock_flags)) {
			fclose($fh);
			HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_FORBIDDEN, FALSE);
		} // end if
		$enable_download = $enable_download ?? FALSE;
		if (!isset($content_type)) {
			$content_type = FileSystem::find_mime_type($filename);
			$content_type = array_shift($content_type);
		} // end if
		$ftime = HTTPCommon::http_date($ftime);
		$filename_attachment = $filename_attachment ?? $filename;
		$filename_attachment = basename($filename_attachment);
		if (!RB_IS_CLI && $this->options['exec_time_limit'] >= 0) {
//			ini_set('max_execution_time', $this->options['exec_time_limit']);
			set_time_limit($this->options['exec_time_limit']);
		} // end if
		if ($this->options['enable_partial'] && isset($_SERVER['HTTP_RANGE'])) {
			if (!is_array($ranges = HTTPCommon::parse_ranges($_SERVER['HTTP_RANGE'], $fsize)) || count($ranges) < 1) {
				fclose($fh);
				HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, FALSE);
			} // end if
			HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_PARTIAL_CONTENT);
			HTTPCommon::header('Last-Modified: ' . $ftime);
			HTTPCommon::header('Accept-Ranges: bytes');
			if (count($ranges) > 1) {
				$boundary = Common::random_hex_id(16);
				$content_length = 0;
				foreach ($ranges as $range) {
					$content_length += strlen("\r\n--$boundary\r\n");
					$content_length += strlen(sprintf("Content-Type: %s\r\n", $content_type));
					$content_length += strlen(sprintf("Content-Range: bytes %u-%u/%u\r\n\r\n", $range[0], $range[1], $fsize));
					$content_length += ($range[1] - $range[0] + 1);
				} // end foreach
				$content_length += strlen("\r\n--$boundary--\r\n");
				HTTPCommon::header(sprintf('Content-Length: %u', $content_length));
//				HTTPCommon::header(sprintf('Content-Type: multipart/x-byteranges; boundary=%s', $boundary));
				if ($enable_download) {
					HTTPCommon::header('Content-Type: application/force-download');
					HTTPCommon::header('Content-Type: application/octet-stream', FALSE);
					HTTPCommon::header('Content-Type: application/download', FALSE);
					HTTPCommon::header(sprintf('Content-Type: multipart/x-byteranges; boundary=%s', $boundary), FALSE);
					HTTPCommon::header('Content-Disposition: attachment; filename="' . rawurlencode($filename_attachment) . '"');
					HTTPCommon::header('Content-Transfer-Encoding: binary');
				} else {
					HTTPCommon::header(sprintf('Content-Type: multipart/x-byteranges; boundary=%s', $boundary));
					HTTPCommon::header('Content-Disposition: inline; filename="' . rawurlencode($filename_attachment) . '"');
				} // end if
				if (RB_REQUEST_METHOD == 'GET' || RB_REQUEST_METHOD == 'POST') {
					Common::ob_end_clean_all();
					foreach ($ranges as $x => $range) {
						echo "\r\n--$boundary\r\n";
						echo sprintf("Content-type: %s\r\n", $content_type);
						echo sprintf("Content-range: bytes %u-%u/%u\r\n\r\n", $range[0], $range[1], $fsize);
						self::_output_content($fh, $range[0], $range[1], $this->options['chunksize'], $this->options['speed_limit']);
					} // end foreach
					echo "\r\n--$boundary--\r\n";
				} // end if
			} else {
				$range = $ranges[0];
				$content_length = $range[1] - $range[0] + 1;
				if ($this->options['size_limit'] > 0 && $content_length > $this->options['size_limit']) {
					$range[1] = $this->options['size_limit'] + $range[0] - 1;
					$content_length = $this->options['size_limit'];
				} // end if
				if ($enable_download) {
					HTTPCommon::header('Content-Type: application/force-download');
					HTTPCommon::header('Content-Type: application/octet-stream', FALSE);
					HTTPCommon::header('Content-Type: application/download', FALSE);
					if (strcasecmp($content_type, MimeType::DEFAULT_MIME_TYPE)) {
						HTTPCommon::header(sprintf('Content-Type: %s', $content_type), FALSE);
					} // end if
					HTTPCommon::header('Content-Disposition: attachment; filename="' . rawurlencode($filename_attachment) . '"');
					HTTPCommon::header('Content-Transfer-Encoding: binary');
				} else {
					HTTPCommon::header(sprintf('Content-Type: %s', $content_type));
					HTTPCommon::header('Content-Disposition: inline; filename="' . rawurlencode($filename_attachment) . '"');
				} // end if
				HTTPCommon::header(sprintf('Content-Range: bytes %u-%u/%u', $range[0], $range[1], $fsize));
				HTTPCommon::header(sprintf('Content-Length: %u', $content_length));
				if (RB_REQUEST_METHOD == 'GET' || RB_REQUEST_METHOD == 'POST') {
					self::_output_content($fh, $range[0], $range[1], $this->options['chunksize'], $this->options['speed_limit']);
				} // end if
			} // end if
		} else {
			HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_OK);
			HTTPCommon::header('Last-Modified: ' . $ftime);
			if ($enable_download) {
				HTTPCommon::header('Content-Type: application/force-download');
				HTTPCommon::header('Content-Type: application/octet-stream', FALSE);
				HTTPCommon::header('Content-Type: application/download', FALSE);
				if (strcasecmp($content_type, MimeType::DEFAULT_MIME_TYPE)) {
					HTTPCommon::header(sprintf('Content-Type: %s', $content_type), FALSE);
				} // end if
				HTTPCommon::header('Content-Disposition: attachment; filename="' . rawurlencode($filename_attachment) . '"');
				HTTPCommon::header('Content-Transfer-Encoding: binary');
			} else {
				HTTPCommon::header(sprintf('Content-Type: %s', $content_type));
				HTTPCommon::header('Content-Disposition: inline; filename="' . rawurlencode($filename_attachment) . '"');
			} // end if
			if ($this->options['enable_partial']) {
				HTTPCommon::header('Accept-Ranges: bytes');
			} // end if
			HTTPCommon::header(sprintf('Content-Length: %u', $fsize));
			if (RB_REQUEST_METHOD == 'GET' || RB_REQUEST_METHOD == 'POST') {
				$first_pos = 0;
				if ($this->options['size_limit'] > 0 && $fsize > $this->options['size_limit']) {
					$last_pos = $this->options['size_limit'] - 1;
				} else {
					$last_pos = $fsize - 1;
				} // end if
				self::_output_content($fh, $first_pos, $last_pos, $this->options['chunksize'], $this->options['speed_limit']);
			} // end if
		} // end if
		if (isset($lock_flags)) {
			flock($fh, LOCK_UN);
		} // end if
		fclose($fh);
	} // end of the '_get_file()' method

	protected static function _output_content($fh, int $first_pos, int $last_pos, int $chunksize, int $speed_limit) : void {
		$current_offset = $first_pos;
		fseek($fh, $current_offset);
		Common::ob_end_clean_all();
		while ($current_offset <= $last_pos && !feof($fh)) {
			$current_chunksize = min($last_pos - $current_offset + 1, $chunksize);
			$chunk = fread($fh, $current_chunksize);
			$chunk_len = strlen($chunk);
			if ($speed_limit > 0) {
				usleep((int) ((self::USLEEP_K * $chunk_len) / $speed_limit));
			} // end if
			echo $chunk;
			flush();
			$current_offset += $chunk_len;
		} // end while
	} // end of the '_output_content()' method

	public static function get_file(string $filename, bool $enable_download = NULL, string $content_type = NULL, int $lock_flags = NULL, string $filename_attachment = NULL) : void {
		$objref = static::_singleton();
		if ($objref->initialized) {
			$filename = RB_rel2abs($filename, $objref->options['data_dir']);
			if (!is_file($filename)) {
				HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_NOT_FOUND, FALSE);
			} // end if
			if (!is_readable($filename)) {
				HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_FORBIDDEN, FALSE);
			} // end if
			try {
				@$objref->_get_file($filename, $enable_download, $content_type, $lock_flags, $filename_attachment);
			} catch (\Throwable $e) {
				HTTPStatusCodes::send_status(HTTPStatusCodes::HTTP_INTERNAL_SERVER_ERROR, FALSE);
			} // end try
		} // end if
	} // end of the 'get_file()' method

	public static function download(string $filename, string $content_type = NULL, string $filename_attachment = NULL) : void {
		self::get_file($filename, TRUE, $content_type, NULL, $filename_attachment);
	} // end of the 'download()' method

} // end of the 'Download' class

?>