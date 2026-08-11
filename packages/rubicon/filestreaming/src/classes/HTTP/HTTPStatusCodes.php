<?php

declare(strict_types=1);

namespace RB\HTTP;

use RB\Core\TraitCommonStatic;
use RB\Common;

final class HTTPStatusCodes {

	use TraitCommonStatic;

	// [Informational 1xx]
	public const	HTTP_CONTINUE							= 100;
	public const	HTTP_SWITCHING_PROTOCOLS				= 101;
	public const	HTTP_PROCESSING							= 102;
	public const	HTTP_EARLY_HINTS						= 103;

	// [Successful 2xx]
	public const	HTTP_OK									= 200;
	public const	HTTP_CREATED							= 201;
	public const	HTTP_ACCEPTED							= 202;
	public const	HTTP_NON_AUTHORITATIVE_INFORMATION		= 203;
	public const	HTTP_NO_CONTENT							= 204;
	public const	HTTP_RESET_CONTENT						= 205;
	public const	HTTP_PARTIAL_CONTENT					= 206;
	public const	HTTP_MULTI_STATUS						= 207;
	public const	HTTP_ALREADY_REPORTED					= 208;
	public const	HTTP_IM_USED							= 226;

	// [Redirection 3xx]
	public const	HTTP_MULTIPLE_CHOICES					= 300;
	public const	HTTP_MOVED_PERMANENTLY					= 301;
	public const	HTTP_MOVED_TEMPORARILY					= 302;
	public const	HTTP_FOUND								= 302;
	public const	HTTP_SEE_OTHER							= 303;
	public const	HTTP_NOT_MODIFIED						= 304;
	public const	HTTP_USE_PROXY							= 305;
	public const	HTTP_SWITCH_PROXY						= 306;
	public const	HTTP_TEMPORARY_REDIRECT					= 307;
	public const	HTTP_PERMANENT_REDIRECT					= 308;

	// [Client Error 4xx]
	public const	HTTP_BAD_REQUEST						= 400;
	public const	HTTP_UNAUTHORIZED						= 401;
	public const	HTTP_PAYMENT_REQUIRED					= 402;
	public const	HTTP_FORBIDDEN							= 403;
	public const	HTTP_NOT_FOUND							= 404;
	public const	HTTP_METHOD_NOT_ALLOWED					= 405;
	public const	HTTP_NOT_ACCEPTABLE						= 406;
	public const	HTTP_PROXY_AUTHENTICATION_REQUIRED		= 407;
	public const	HTTP_REQUEST_TIMEOUT					= 408;
	public const	HTTP_CONFLICT							= 409;
	public const	HTTP_GONE								= 410;
	public const	HTTP_LENGTH_REQUIRED					= 411;
	public const	HTTP_PRECONDITION_FAILED				= 412;
	public const	HTTP_REQUEST_ENTITY_TOO_LARGE			= 413;
	public const	HTTP_REQUEST_URI_TOO_LONG				= 414;
	public const	HTTP_UNSUPPORTED_MEDIA_TYPE				= 415;
	public const	HTTP_REQUESTED_RANGE_NOT_SATISFIABLE	= 416;
	public const	HTTP_EXPECTATION_FAILED					= 417;
	public const	HTTP_IM_A_TEAPOT						= 418;
	public const	HTTP_AUTHENTICATION_TIMEOUT				= 419;
	public const	HTTP_MISDIRECTED_REQUEST				= 421;
	public const	HTTP_UNPROCESSABLE_ENTITY				= 422;
	public const	HTTP_LOCKED								= 423;
	public const	HTTP_FAILED_DEPENDENCY					= 424;
//	public const	HTTP_UNORDERED_COLLECTION				= 425;
	public const	HTTP_TOO_EARLY							= 425;
	public const	HTTP_UPGRADE_REQUIRED					= 426;
	public const	HTTP_PRECONDITION_REQUIRED				= 428;
	public const	HTTP_TOO_MANY_REQUESTS					= 429;
	public const	HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE	= 431;
	public const	HTTP_REQUESTED_HOST_UNAVAILABLE			= 434;
	public const	HTTP_RETRY_WITH							= 449;
	public const	HTTP_UNAVAILABLE_FOR_LEGAL_REASONS		= 451;
	public const	HTTP_UNRECOVERABLE_ERROR				= 456;
	public const	HTTP_CLIENT_CLOSED_REQUEST				= 499;

	// [Server Error 5xx]
	public const	HTTP_INTERNAL_SERVER_ERROR				= 500;
	public const	HTTP_NOT_IMPLEMENTED					= 501;
	public const	HTTP_BAD_GATEWAY						= 502;
	public const	HTTP_SERVICE_UNAVAILABLE				= 503;
	public const	HTTP_GATEWAY_TIMEOUT					= 504;
	public const	HTTP_VERSION_NOT_SUPPORTED				= 505;
	public const	HTTP_VARIANT_ALSO_NEGOTIATES			= 506;
	public const	HTTP_INSUFFICIENT_STORAGE				= 507;
	public const	HTTP_LOOP_DETECTED						= 508;
	public const	HTTP_BANDWIDTH_LIMIT_EXCEEDED			= 509;
	public const	HTTP_NOT_EXTENDED						= 510;
	public const	HTTP_NETWORK_AUTHENTICATION_REQUIRED	= 511;
	public const	HTTP_UNKNOWN_ERROR						= 520;
	public const	HTTP_WEB_SERVER_IS_DOWN					= 521;
	public const	HTTP_CONNECTION_TIMED_OUT				= 522;
	public const	HTTP_ORIGIN_IS_UNREACHABLE				= 523;
	public const	HTTP_A_TIMEOUT_OCCURRED					= 524;
	public const	HTTP_SSL_HANDSHAKE_FAILED				= 525;
	public const	HTTP_INVALID_SSL_CERTIFICATE			= 526;

	private static	$messages = [

		// [Informational 1xx]
		100	=> '100 Continue',
		101	=> '101 Switching Protocols',
		102	=> '102 Processing',
		103	=> '103 Early Hints',

		// [Successful 2xx]
		200	=> '200 OK',
		201	=> '201 Created',
		202	=> '202 Accepted',
		203	=> '203 Non-Authoritative Information',
		204	=> '204 No Content',
		205	=> '205 Reset Content',
		206	=> '206 Partial Content',
		207	=> '207 Multi-Status',
		208	=> '208 Already Reported',
		226	=> '226 IM Used',

		// [Redirection 3xx]
		300	=> '300 Multiple Choices',
		301	=> '301 Moved Permanently',
		302	=> RB_IS_HTTP1 ? '302 Moved Temporarily' : '302 Found',
		303	=> '303 See Other',
		304	=> '304 Not Modified',
		305	=> '305 Use Proxy',
		307	=> '307 Temporary Redirect',
		308	=> '308 Permanent Redirect',

		// [Client Error 4xx]
		400	=> '400 Bad Request',
		401	=> '401 Unauthorized',
		402	=> '402 Payment Required',
		403	=> '403 Forbidden',
		404	=> '404 Not Found',
		405	=> '405 Method Not Allowed',
		406	=> '406 Not Acceptable',
		407	=> '407 Proxy Authentication Required',
		408	=> '408 Request Timeout',
		409	=> '409 Conflict',
		410	=> '410 Gone',
		411	=> '411 Length Required',
		412	=> '412 Precondition Failed',
		413	=> '413 Request Entity Too Large',
		414	=> '414 Request-URI Too Long',
		415	=> '415 Unsupported Media Type',
		416	=> '416 Requested Range Not Satisfiable',
		417	=> '417 Expectation Failed',
		418	=> '418 I\'m a teapot',
		419	=> '419 Authentication Timeout',
		421	=> '421 Misdirected Request',
		422	=> '422 Unprocessable Entity',
		423	=> '423 Locked',
		424	=> '424 Failed Dependency',
//		425	=> '425 Unordered Collection',
		425	=> '425 Too Early',
		426	=> '426 Upgrade Required',
		428	=> '428 Precondition Required',
		429	=> '429 Too Many Requests',
		431	=> '431 Request Header Fields Too Large',
		434	=> '434 Requested Host Unavailable',
		449	=> '449 Retry With',
		451	=> '451 Unavailable For Legal Reasons',
		456 => '456 Unrecoverable Error',
		499 => '499 Client Closed Request',

		// [Server Error 5xx]
		500	=> '500 Internal Server Error',
		501	=> '501 Not Implemented',
		502	=> '502 Bad Gateway',
		503	=> '503 Service Unavailable',
		504	=> '504 Gateway Timeout',
		505	=> '505 HTTP Version Not Supported',
		506	=> '506 Variant Also Negotiates',
		507	=> '507 Insufficient Storage',
		508	=> '508 Loop Detected',
		509	=> '509 Bandwidth Limit Exceeded',
		510	=> '510 Not Extended',
		511	=> '511 Network Authentication Required',
		520	=> '520 Unknown Error',
		521	=> '521 Web Server Is Down',
		522	=> '522 Connection Timed Out',
		523	=> '523 Origin Is Unreachable',
		524	=> '524 A Timeout Occurred',
		525	=> '525 SSL Handshake Failed',
		526	=> '526 Invalid SSL Certificate'

	];

	public static function msg_status(int $code) : ?string {
		if (isset(self::$messages[$code])) {
			return self::$messages[$code];
		} // end if
		return NULL;
	} // end of the 'msg_status()' method

	public static function is_info(int $code) : bool {
		return $code >= 100 && $code < 200;
	} // end of the 'is_info()' method

	public static function is_success(int $code) : bool {
		return $code >= 200 && $code < 300;
	} // end of the 'is_success()' method

	public static function is_redirect(int $code) : bool {
		return $code >= 300 && $code < 400;
	} // end of the 'is_redirect()' method

	public static function is_error(int $code) : bool {
		return $code >= 400 && $code < 600;
	} // end of the 'is_error()' method

	public static function is_client_error(int $code) : bool {
		return $code >= 400 && $code < 500;
	} // end of the 'is_client_error()' method

	public static function is_server_error(int $code) : bool {
		return $code >= 500 && $code < 600;
	} // end of the 'is_server_error()' method

	public static function is_partial(int $code) : bool {
		return $code == self::HTTP_PARTIAL_CONTENT;
	} // end of the 'is_partial()' method

	public static function can_have_body(int $code) : bool {
		return
			// True if not in 100s
			($code < 100 || $code >= 200)
			&& // and not 204 NO CONTENT
			$code != self::HTTP_NO_CONTENT
			&& // and not 304 NOT MODIFIED
			$code != self::HTTP_NOT_MODIFIED;
	} // end of the 'can_have_body()' method

	public static function send_status(int $code, bool $enable_body = NULL, string|Language $lang = NULL) : void {
		if (!RB_IS_CLI && isset(self::$messages[$code])) {
			if (RB_SERVER_PROTOCOL == 'HTTP/1.0') {
				HTTPCommon::header(sprintf('HTTP/1.0 %s', self::$messages[$code]));
			} elseif (RB_SERVER_PROTOCOL == 'HTTP/1.1') {
				HTTPCommon::header(sprintf('HTTP/1.1 %s', self::$messages[$code]));
			} elseif (RB_SERVER_PROTOCOL == 'HTTP/2.0') {
				HTTPCommon::header(sprintf('HTTP/2 %u', $code));
			} // end if
			if (self::is_error($code)) {
				$enable_body = $enable_body ?? TRUE;
				$html = NULL;
				if ($enable_body && is_string($http_error_tpl = @file_get_contents(__DIR__ . '/templates/error.tpl'))) {
					$msg_status = htmlspecialchars(self::msg_status($code), ENT_QUOTES, 'ISO-8859-1');
					HTTPCommon::header('Content-Type: text/html; charset=utf-8');
					$html = sprintf($http_error_tpl, $msg_status, $msg_status);
				} // end if
				Common::ob_end_clean_all();
				if (isset($html)) echo $html;
				exit;
			} // end if
		} // end if
	} // end of the 'send_status()' method

} // end of the 'HTTPStatusCodes' class

?>