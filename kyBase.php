<?php
/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Base class with Kayako REST client funcionality.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+API
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
abstract class kyBase {
	/**
	 * HTTP verb - GET. For getting objects.
	 * @var string
	 */
	const METHOD_GET = "GET";

	/**
	 * HTTP verb - POST. For creating object.
	 * @var string
	 */
	const METHOD_POST = "POST";

	/**
	 * HTTP verb - PUT. For updating objects.
	 * @var string
	 */
	const METHOD_PUT = "PUT";

	/**
	 * HTTP verb - DELETE. For deleting objects.
	 * @var string
	 */
	const METHOD_DELETE = "DELETE";

	/**
	 * Base URL of Kayako REST API.
	 * @var string
	 */
	static private $base_url = null;

	/**
	 * Kayako REST API key.
	 * @var string
	 */
	static private $api_key = null;

	/**
	 * Kayako REST API secret key.
	 * @var string
	 */
	static private $secret_key = null;

	/**
	 * True to PUT data using in-memory stream (may not work on Windows). False to PUT using post fields.
	 * See processRequest for implementation details.
	 * @var bool
	 */
	static private $put_memory_stream = true;

	/**
	 * Default Kayako controller used to operate on this objects. Override in descending classes.
	 * @var string
	 */
	static protected $controller = null;

	/**
	 * Default format of datetime object properties used in getters and setters.
	 * @var string
	 */
	static protected $datetime_format = 'Y-m-d H:i:s';

	function __construct() {
	}

	/**
	 * Creates new object. Compatible with method chaining.
	 */
	static public function createNew() {
		return new static();
	}

	/**
	 * Initializes the client.
	 * Should be called before before contacting the API.
	 *
	 * @param string $base_url Base URL of Kayako REST API.
	 * @param string $api_key Kayako REST API key.
	 * @param string $secret_key Kayako REST API secret key.
	 * @param bool $put_memory_stream True to PUT data using in-memory stream (may not work on Windows). False to PUT using post fields.
	 * @param string $datetime_format Default format of datetime object properties used in getters and setters.
	 */
	static public function init($base_url, $api_key, $secret_key, $put_memory_stream = true, $datetime_format = null) {
		self::$base_url = $base_url;
		self::$api_key = $api_key;
		self::$secret_key = $secret_key;
		self::$put_memory_stream = $put_memory_stream;
		if ($datetime_format !== null)
			self::$datetime_format = $datetime_format;
	}

	/**
	 * Prepares URL and POST data.
	 *
	 * @param string $controller Kayako controller to call. Null to use default controller defined for object.
	 * @param string $method HTTP verb.
	 * @param array $parameters List of additional parameters (like object identifiers or search parameters).
	 * @return array
	 */
	static private function getRequestData($controller, $method, $parameters = array()) {
		if ($controller === null)
			$controller = static::$controller;

		$salt = mt_rand();
		$signature = base64_encode(hash_hmac('sha256', $salt, self::$secret_key, true));

		$parameters_str = '';
		foreach ($parameters as $parameter) {
			$parameters_str .= sprintf("/%s", $parameter);
		}

		$auth_data = array();
		switch ($method) {
			case self::METHOD_POST:
			case self::METHOD_PUT:
				$url = sprintf("%s?e=%s%s", self::$base_url, $controller, $parameters_str);
				$auth_data['apikey'] = self::$api_key;
				$auth_data['salt'] = $salt;
				$auth_data['signature'] = $signature;
				break;
			case self::METHOD_GET:
			case self::METHOD_DELETE:
				$url = sprintf("%s?e=%s%s&apikey=%s&salt=%s&signature=%s", self::$base_url, $controller, $parameters_str, self::$api_key, $salt, urlencode($signature));
				break;
		}

		return array('url' => $url, 'auth_data' => $auth_data);
	}

	/**
	 * Sends the request to Kayako server and returns parsed response.
	 *
	 * @param string $controller Kayako controller to call. Null to use default controller defined for object.
	 * @param string $method HTTP verb.
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Optional. Object data (for POST and PUT).
	 * @return array
	 */
	static protected function processRequest($controller, $method, $parameters = array(), $data = array()) {
		$request_data = static::getRequestData($controller, $method, $parameters);

		$curl_options = array(
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 1,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_URL => $request_data['url'],
		);

		$request_body = http_build_query(array_merge($data, $request_data['auth_data']), '', '&');
		switch ($method) {
			case self::METHOD_GET:
				break;
			case self::METHOD_POST:
				$curl_options[CURLOPT_POSTFIELDS] = $request_body;
				$curl_options[CURLOPT_POST] = true;
				break;
			case self::METHOD_PUT:
				if (self::$put_memory_stream) {
					$fh = fopen('php://memory', 'rw');
					fwrite($fh, $request_body);
					rewind($fh);

					$curl_options[CURLOPT_INFILE] = $fh;
					$curl_options[CURLOPT_INFILESIZE] = strlen($request_body);
					$curl_options[CURLOPT_PUT] = true;
				} else {
					$curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
					$curl_options[CURLOPT_POSTFIELDS] = $request_body;
				}
				break;
			case self::METHOD_DELETE:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
		}
var_dump($request_body);
		$curl_handle = curl_init();
		curl_setopt_array($curl_handle, $curl_options);
		$response = curl_exec($curl_handle);

		if ($response === false)
			throw new Exception(sprintf('CURL error: %s (%s)', curl_error($curl_handle), curl_errno($curl_handle)));

		$http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		if ($http_status != 200)
			throw new Exception(sprintf("HTTP error: %s", $http_status));

		curl_close($curl_handle);

		if ($method === self::METHOD_DELETE)
			return null;

		$result = ky_xml_to_array($response);
		if ($result === false)
			throw new Exception("Error parsing XML response.");

		if (count($result) === 1 && array_key_exists('_contents', $result) && strlen($result['_contents']) === 0)
			$result = array();

		return $result;
	}

	/**
	 * Sends GET request and returns parsed response.
	 *
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param string $controller Kayako controller to call. Null to use default controller defined for object.
	 * @return array
	 */
	static protected function _get($parameters = array(), $controller = null) {
		return static::processRequest($controller, self::METHOD_GET, $parameters);
	}

	/**
	 * Sends POST request and returns parsed response.
	 *
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Object data.
	 * @return array
	 */
	static protected function _post($parameters = array(), $data = array()) {
		return static::processRequest(static::$controller, self::METHOD_POST, $parameters, $data);
	}

	/**
	 * Sends PUT request and returns parsed response.
	 *
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Object data.
	 * @return array
	 */
	static protected function _put($parameters = array(), $data = array()) {
		return static::processRequest(static::$controller, self::METHOD_PUT, $parameters, $data);
	}

	/**
	 * Sends DELETE request.
	 *
	 * @param array $parameters List of additional parameters (like object identifiers or search parameters).
	 */
	static protected function _delete($parameters = array()) {
		static::processRequest(static::$controller, self::METHOD_DELETE, $parameters);
	}
}