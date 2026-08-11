<?php

declare(strict_types=1);

namespace RB\FileSystem;

use RB\Core\StaticObjectWithOptions;
use RB\Exception\RubiconException;

if (!defined('RB_AUTO_INITIALIZE_FILE_FORMAT_IDENTIFIER')) {
	define('RB_AUTO_INITIALIZE_FILE_FORMAT_IDENTIFIER',	TRUE);
} // end if

final class FileFormatIdentifier extends StaticObjectWithOptions {

	public const	BLOCK_SIZE	= 524288;

	protected	$ident_ref_table,
				$bof_max_length,
				$eof_max_length;

	protected function __construct() {
		$this->_create_option('cache_path',	'./file_format_identifier');
		parent::__construct();
		$this->options['cache_path'] = RB_rel2abs($this->options['cache_path'], RB_CACHE_PATH);
		if (!is_dir($this->options['cache_path'])) {
			throw new RubconException(sprintf('%s(): The directory \'%s\' does not exist', __METHOD__, $this->options['cache_path']));
		} // end if
		if (!is_writable($this->options['cache_path'])) {
			throw new RubconException(sprintf('%s(): The directory \'%s\' is not writable', __METHOD__, $this->options['cache_path']));
		} // end if
	} // end of the '__construct()' constructor

	protected function _init() : bool {
		$filename = $this->options['cache_path'] . DS . '_cache_file_format_identifier.ser';
		if (!is_file($filename) || !is_array($data = FileSystem::file_unserialize($filename))) {
			require RB_INCLUDE_PATH . '/file_format_identifier.php';
			$this->bof_max_length = 0;
			$this->eof_max_length = 0;
			foreach ($this->ident_ref_table as &$ident) {
				if (isset($ident[1])) {
					$len = $ident[1];
				} else {
					$len = strlen($ident[3]);
					if ($ident[2] == 'h') {
						$ident[2] = 's';
						$ident[3] = hex2bin($ident[3]);
						$len /= 2;
					} // end if
					$ident[1] = $len;
				} // end if
				$len += $ident[0];
				if ($len > $this->bof_max_length) $this->bof_max_length = $len;
				if (isset($ident[5])) {
					$len = $ident[5];
				} else {
					$len = strlen($ident[7]);
					if ($ident[6] == 'h') {
						$ident[6] = 's';
						$ident[7] = hex2bin($ident[7]);
						$len /= 2;
					} // end if
					$ident[5] = $len;
				} // end if
				$len += $ident[4];
				if ($len > $this->eof_max_length) $this->eof_max_length = $len;
			} // end foreach
			usort(
				$this->ident_ref_table,
				function ($a, $b) {
					if ($a[0] == $b[0]) {
						if ($a[1] == $b[1]) {
							if ($a[4] == $b[4]) {
								if ($a[5] == $b[5]) {
									if ($a[8] == $b[8]) {
										return 0;
									} else {
										return $a[8] > $b[8] ? 1 : -1;
									} // end if
								} else {
									return $a[5] < $b[5] ? 1 : -1;
								} // end if
							} else {
								return $a[4] < $b[4] ? 1 : -1;
							} // end if
						} else {
							return $a[1] < $b[1] ? 1 : -1;
						} // end if
					} else {
						return $a[0] < $b[0] ? 1 : -1;
					} // end if
				}
			);
			FileSystem::file_serialize($filename, [$this->ident_ref_table, $this->bof_max_length, $this->eof_max_length]);
		} else {
			list($this->ident_ref_table, $this->bof_max_length, $this->eof_max_length) = $data;
		} // end if
		return TRUE;
	} // end of the '_init()' method

	public static function detect_mime_type($filename) : ?array {
		$objref = static::_singleton();
		if ($objref->initialized) {
			$file_obj = File::create_obj($filename);
			$bof_len = $eof_len = 0;
			$size = $file_obj->size;
			$bof_max_length = min($objref->bof_max_length, $size);
			$eof_max_length = min($objref->eof_max_length, $size);
			$bof_bin = $file_obj->fread($bof_max_length);
			if (is_string($bof_bin)) {
				$bof_len = strlen($bof_bin);
			} // end if
			$file_obj->fseek(-$eof_max_length, SEEK_END);
			$eof_bin = $file_obj->fread($eof_max_length);
			if (is_string($eof_bin)) {
				$eof_bin = strrev($eof_bin);
				$eof_len = strlen($eof_bin);
			} // end if
			if ($bof_len > 0 || $eof_len > 0) {
				foreach ($objref->ident_ref_table as $ident) {
					if (empty($ident[1]) && empty($ident[5])) continue;
					if (!empty($ident[1])) {
						if ($ident[0] > $bof_len) continue;
						if ($ident[2][0] == 's') {
							$cmp = substr($bof_bin, $ident[0], $ident[1]);
						} else {
							continue;
						} // end if
						if (!(is_string($cmp) && ($ident[2] == 'sr' && preg_match('@^'. $ident[3] . '@', $cmp) || $cmp === $ident[3]))) {
							continue;
						} // end if
					} // end if
					if (!empty($ident[5])) {
						if ($ident[4] > $eof_len) continue;
						if ($ident[6][0] == 's') {
							$cmp = strrev(substr($eof_bin, $ident[4], $ident[5]));
						} else {
							continue;
						} // end if
						if (!(is_string($cmp) && ($ident[6] == 'sr' && preg_match('@'. $ident[7] . '$@', $cmp) || $cmp === $ident[7]))) {
							continue;
						} // end if
					} // end if
					return [
						'default_extension'	=> $ident[8],
						'extensions'		=> $ident[9],
						'mime_type'			=> $ident[10],
						'uti'				=> $ident[11],
						'name'				=> $ident[12],
						'version'			=> $ident[13],
						'other_names'		=> $ident[14],
						'puid'				=> $ident[15],
						'family'			=> $ident[16],
						'classification'	=> $ident[17],
						'disclosure'		=> $ident[18],
						'description'		=> $ident[19],
						'orientation'		=> $ident[20],
						'byte_order'		=> $ident[21],
						'urls'				=> $ident[22]
					];
				} // end foreach
			} // end if
		} // end if
		return NULL;
	} // end of the 'detect_mime_type()' method

} // end of the 'FileFormatIdentifier' class

FileFormatIdentifier::init();

if (RB_AUTO_INITIALIZE_FILE_FORMAT_IDENTIFIER) {
	try {
		FileFormatIdentifier::init();
	} catch (\Throwable $e) {
		////////
	} // end try
} // end if
