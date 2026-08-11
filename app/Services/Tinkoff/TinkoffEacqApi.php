<?php

namespace App\Services\Tinkoff;

use function array_diff_key;
use function array_flip;
use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function env;
use function hash;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function mb_strimwidth;
use function property_exists;
use function strlen;
use function time;
use function ucfirst;

class TinkoffEacqApi {
    const STATUS_AUTHORIZED       = 'AUTHORIZED';
    const STATUS_CANCELED         = 'CANCELED';
    const STATUS_CONFIRMED        = 'CONFIRMED';
    const STATUS_PARTIAL_REFUNDED = 'PARTIAL_REFUNDED';
    const STATUS_REFUNDED         = 'REFUNDED';

    /**
     * @var \App\Services\Tinkoff\TinkoffEacqApi
     */
    private static $client;
    /**
     * @var \App\Services\Tinkoff\TinkoffEacqApi
     */
    private static $marketplace_client;

    protected $error;
    protected $payment_id;
    protected $payment_status;
    protected $payment_url;
    protected $response;
    private   $acquiring_url;
    public    $rebill_id;
    /**
     * @var array
     */
    private $requestParams;
    private $secret_key;
    private $terminal_id;
    private $url_cancel;
    /**
     * @var string
     */
    private $url_charge;
    private $url_closing_receipt;
    private $url_confirm;
    private $url_get_state;
    private $url_init;

    public static function getMarketplacePaymentStatus($payment_id) {
        $tinkoff = self::_marketplaceClientInit();

        $state = $tinkoff->getStatus($payment_id);

        return ['client' => $tinkoff, 'state' => $state, 'result' => !$tinkoff->_errorsFound()];
    }

    public static function getMarketplacePaymentURL($payment, $items, $shopCode, $options = []) {
        $tinkoff    = self::_marketplaceClientInit();
        $options    = array_merge($options, [
                "Shops" => [
                    [
                        "ShopCode" => $shopCode,
                        "Amount"   => $items[0]["Price"] * 100, // в копейках!
                        "Name"     => $items[0]['Name'],
                    ],
                ],
            ]
		);
		$paymentURL = $tinkoff->paymentURL($payment, $items, $options);

		return ['client' => $tinkoff, 'paymentURL' => $paymentURL, 'result' => !$tinkoff->_errorsFound()];
	}

	public static function getPaymentStatus($payment_id) {
		$tinkoff = self::_clientInit();

		$state = $tinkoff->getStatus($payment_id);

		return ['client' => $tinkoff, 'state' => $state, 'result' => !$tinkoff->_errorsFound()];
	}

	public static function getPaymentURL($payment, $items, $options = []) {
        $tinkoff    = self::_clientInit();
        $paymentURL = $tinkoff->paymentURL($payment, $items, $options);

        return [
            'client'     => $tinkoff,
            'rebillId'   => $tinkoff->rebill_id,
            'paymentURL' => $paymentURL,
            'result'     => !$tinkoff->_errorsFound(),
        ];
    }

    public static function paymentCharge($rebill_id, $payment, $items, $options = []) {
        $tinkoff    = self::_clientInit();
        $paymentURL = $tinkoff->paymentURL($payment, $items, $options);
        $state = $tinkoff->charge($tinkoff->payment_id, $rebill_id);
        return ['client' => $tinkoff, 'state' => $state, 'result' => !$tinkoff->_errorsFound()];
	}
    
	public static function paymentCancel($payment_id) {
		$tinkoff = self::_clientInit();

		if ($tinkoff->_isPaymentConfirmed($payment_id)) {
			$tinkoff->cencelPayment($payment_id);
		}

		if ($tinkoff->_isPaymentCanceled($payment_id)) {
			return [
				'client' => $tinkoff,
				'status' => $tinkoff->responseData('status'),
				'result' => true
			];
		}

		return ['client' => $tinkoff, 'status' => null, 'result' => false];
	}

	public static function paymentClose($payment_id, $receipt_data) {
		$tinkoff = self::_clientInit();

		$result = $tinkoff->sendClosingReceipt($payment_id, $receipt_data);

		return ['client' => $tinkoff, 'status' => $result, 'result' => true];
	}

	public static function paymentConfirm($payment_id) {
		$tinkoff = self::_clientInit();

		$result = $tinkoff->confirmPayment($payment_id);

		return ['client' => $tinkoff, 'status' => $result, 'result' => true];
	}

	private static function _clientInit():TinkoffEacqApi {
		if (empty(self::$client)) {
			$api_url      = config('payment.tinkoff.api_url');
			$terminal     = config('payment.tinkoff.terminal');
			$secret_key   = config('payment.tinkoff.terminal_secret');;
			self::$client = new self($api_url, $terminal, $secret_key);
		}

		return self::$client;
	}

	private static function _marketplaceClientInit():TinkoffEacqApi {
		if (empty(self::$marketplace_client)) {
			$api_url                  = env('TINKOFF_EACQ_API_URL', 'https://rest-api-test.tinkoff.ru/');
			$terminal                 = env('TINKOFF_MARKETPLACE_TERMINAL', '');
			$secret_key               = env('TINKOFF_MARKETPLACE_SECRET_KEY', null);
			self::$marketplace_client = new self($api_url, $terminal, $secret_key);
		}

		return self::$marketplace_client;
	}

	/**
	 * Inicialize Tinkoff class
	 *
	 * @param [string] $acquiring_url - tinkoff acquiring APi url
	 * @param [string] $terminal_id   - acquiring terminal number
	 * @param [string] $secret_key    - acquiring terminal password
	 */
	public function __construct($acquiring_url, $terminal_id, $secret_key) {
		$this->acquiring_url = $acquiring_url.'v2/';
		$this->terminal_id   = $terminal_id;
		$this->secret_key    = $secret_key;
		$this->_setupUrls();
	}

	/**
	 * return protected props
	 *
	 * @param  [mixed] $property name
	 *
	 * @return [mixed]           value
	 */
	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}

	/**
	 * Cancel payment
	 *
	 * @param  [string] Tinkoff payment id
	 *
	 * @return [mixed] status of payment or false
	 */
	public function cencelPayment($payment_id) {
		$params = ['PaymentId' => $payment_id];

		if ($this->_sendApiRequest($this->url_cancel, $params)) {
			return $this->payment_status;
		}

		return false;
	}

	/**
	 * Confirm payment
	 *
	 * @param  [string] Tinkoff payment id
	 *
	 * @return [mixed] status of payment or false
	 */
	public function confirmPayment($payment_id) {
		$params = ['PaymentId' => $payment_id];

		if ($this->_sendApiRequest($this->url_confirm, $params)) {
			return $this->payment_status;
		}

		return false;
	}

	/**
	 * Check payment status
	 *
	 * @param  [string] Tinkoff payment id
	 *
	 * @return [mixed] status of payment or false
	 */
	public function getStatus($payment_id) {
		$params = ['PaymentId' => $payment_id];

		if ($this->_sendApiRequest($this->url_get_state, $params)) {
			return $this->payment_status;
		}

		return false;
	}
	
	public function charge($payment_id, $rebill_id) {
		$params = ['PaymentId' => $payment_id, 'RebillId'=>$rebill_id];

		if ($this->_sendApiRequest($this->url_charge, $params)) {
			return $this->payment_status;
		}

		return false;
	}

	/**
	 * Generate payment URL
	 *
	 * -------------------------------------------------
	 * For generate url need to send $payment array and array of $items
	 * All keys for correct checking in paymentArrayChecked()
	 * and itemsArrayChecked()
	 *
	 * Tinkoff does not accept a Item name longer than $item_name_max_lenght
	 * $amount_multiplicator - need for convert price to cents
	 *
	 * @param array $payment array of payment data
	 * @param array $items   array of items
	 *
	 * @return mixed - return payment url if has no errors
	 */
	public function paymentURL(array $payment, array $items, $options = []) {
		if (!$this->_paymentArrayChecked($payment)) {
			$this->error = 'Incomplete payment data';

			return false;
		}

		$item_name_max_lenght = 64;
		$amount_multiplicator = 100;
        $amount = 0;
		/**
		 * Generate items array for Receipt
		 */
		foreach ($items as $item) {
			if (!$this->_itemsArrayChecked($item)) {
				$this->error = 'Incomplete items data';

				return false;
			}
            $amount += $item['Price'] * $amount_multiplicator;
			$payment['Items'][] = [
				'Name'     => mb_strimwidth($item['Name'], 0, $item_name_max_lenght - 1, ''),
				'Price'    => $item['Price'] * $amount_multiplicator,
				'Quantity' => 1.0,
				'Amount'   => $item['Price'] * $amount_multiplicator,
				'Tax'      => $item['NDS'],
			];
		}

		$params = [
			'OrderId'     => $payment['OrderId'],
			'Amount'      => $amount,//$payment['Amount'] * $amount_multiplicator,
			'Language'    => 'ru',
			'Description' => $payment['Description'],
			'PayType'     => 'O',
			'DATA'        => [
				'Email' => $payment['Email'],
				'Phone' => $payment['Phone'],
				'Name'  => $payment['Name'],
			],
			'Receipt'     => [
				'Email'    => $payment['Email'],
				'Phone'    => $payment['Phone'],
				'Taxation' => $payment['Taxation'],
				'Items'    => $payment['Items'],
			],
		];
		$params = array_merge($options, $params);

		if ($this->_sendApiRequest($this->url_init, $params)) {
			return $this->payment_url;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function requestParams():array {
		return $this->requestParams;
	}

	public function responseData($key = null) {
		$result = json_decode($this->response, true);

		return empty($key) ? $result : ($result[$key] ?? $result[ucfirst($key)] ?? null);
	}

	/**
	 * Send Closing Receipt
	 *
	 * @param  [string] Tinkoff payment id
	 *
	 * @return [mixed] status of payment or false
	 */
	public function sendClosingReceipt($payment_id, $receipt = []) {
		$params = ['PaymentId' => $payment_id, 'Receipt' => $receipt];

		if ($this->_sendApiRequest($this->url_closing_receipt, $params)) {
			return $this->payment_status;
		}

		return false;
	}

	/**
	 * Checking for existing all $keys in $arr
	 *
	 * @param array $keys - array of keys
	 * @param array $arr  - checked array
	 *
	 * @return [bool]
	 */
	private function _allKeysIsExistInArray(array $keys, array $arr) {
		return (bool)!array_diff_key(array_flip($keys), $arr);
	}

	/**
	 * Adding slash on end of url string if not there
	 *
	 * @return url string
	 */
	private function _checkSlashOnUrlEnd($url) {
		if ($url[strlen($url) - 1] !== '/') {
			$url .= '/';
		}

		return $url;
	}

	/**
	 * Finding all possible errors
	 *
	 * @return bool
	 */
	private function _errorsFound():bool {
		$response = json_decode($this->response, true);

		if (isset($response['ErrorCode'])) {
			$error_code = (int)$response['ErrorCode'];
		} else {
			$error_code = 0;
		}

		if (isset($response['Message'])) {
			$error_msg = $response['Message'];
		} else {
			$error_msg = 'Unknown error.';
		}

		if (isset($response['Details'])) {
			$error_message = $response['Details'];
		} else {
			$error_message = 'Unknown error.';
		}

		if ($error_code !== 0) {
			$this->error = 'Error code: '.$error_code.
				' | Msg: '.$error_msg.
				' | Message: '.$error_message;

			return true;
		}

		return false;
	}

	/**
	 * Generate sha256 token for bank API
	 *
	 * @param array of args
	 *
	 * @return string token
	 */
	private function _generateToken(array $args) {
		$token               = '';
		$args['TerminalKey'] = $this->terminal_id;
		$args['Password']    = $this->secret_key;
		ksort($args);

		foreach ($args as $arg) {
			if (!is_array($arg)) {
				$token .= $arg;
			}
		}

		return hash('sha256', $token);
	}

	private function _isPaymentCanceled($payment_id) {
		$status = $this->getStatus($payment_id);

		return in_array($status,
			[self::STATUS_CANCELED, self::STATUS_REFUNDED, self::STATUS_PARTIAL_REFUNDED]);
	}

	private function _isPaymentConfirmed($payment_id) {
		$status = $this->getStatus($payment_id);

		return ($status === self::STATUS_CONFIRMED);
	}

	/**
	 * Check items array for all keys is isset
	 *
	 * @param array for checking
	 *
	 * @return [bool]
	 */
	private function _itemsArrayChecked(array $array_for_check) {
		$keys = ['Name', 'Price', 'NDS'];

		return $this->_allKeysIsExistInArray($keys, $array_for_check);
	}

	/**
	 * Check payment array for all keys is isset
	 *
	 * @param array for checking
	 *
	 * @return [bool]
	 */
	private function _paymentArrayChecked(array $array_for_check) {
		$keys = [
			'OrderId', 'Amount', 'Language',
			'Description', 'Email', 'Phone',
			'Name', 'Email', 'Phone', 'Taxation',
		];

		return $this->_allKeysIsExistInArray($keys, $array_for_check);
	}

	/**
	 * Send reques to bank acquiring API
	 *
	 * @param  [string] $path url
	 * @param  [array]  $args data
	 *
	 * @return [json]   json decoded data
	 */
	private function _sendApiRequest($path, array $args) {
		$args['TerminalKey'] = $this->terminal_id;
		$args['Token']       = $this->_generateToken($args);
		$this->requestParams = $args;
		$log_time            = time();

		$args_json = json_encode($args);

		if ($curl = curl_init()) {
			curl_setopt($curl, CURLOPT_URL, $path);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args_json);
			curl_setopt($curl, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
			]);

			$response = curl_exec($curl);
			curl_close($curl);

			$this->response = $response;
			$json           = json_decode($response, false);

			// file_put_contents(__DIR__.'/response-eacq-'.$log_time.'.json',
			// 	json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

			if ($json) {
				if ($this->_errorsFound()) {
					return false;
				} else {
                    $this->payment_id     = @$json->PaymentId;
                    $this->rebill_id      = @$json->RebillId;
                    $this->payment_url    = @$json->PaymentURL;
                    $this->payment_status = @$json->Status;

                    return true;
                }
			}

			$this->error .= "Can't create connection to: $path | with args: $args_json";

			return false;
		}
		$this->error .= "CURL init filed: $path | with args: $args_json";

		return false;
	}

	/**
	 * Setting up urls for API
	 *
	 * @return void
	 */
	private function _setupUrls() {
		$this->acquiring_url       = $this->_checkSlashOnUrlEnd($this->acquiring_url);
		$this->url_init            = $this->acquiring_url.'Init/';
		$this->url_charge          = $this->acquiring_url.'Charge/';
		$this->url_cancel          = $this->acquiring_url.'Cancel/';
		$this->url_confirm         = $this->acquiring_url.'Confirm/';
		$this->url_closing_receipt = $this->acquiring_url.'SendClosingReceipt/';
		$this->url_get_state       = $this->acquiring_url.'GetState/';
	}
}
