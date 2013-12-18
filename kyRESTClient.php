<?php
/**
 * Default REST client implementation.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @package Common\REST
 */
class kyRESTClient implements kyRESTClientInterface {
	/**
	 * Library configuration.
	 * @var kyConfig
	 */
	private $config;

	/**
	 * Injects library configuration.
	 *
	 * @see kyRESTClientInterface::setConfig()
	 */
	public function setConfig(kyConfig $config) {
		$this->config = $config;
	}

	/**
	 * Builds and returns POST/PUT request body.
	 * If files are provided the result is encoded using multipart/form-data otherwise application/x-www-form-urlencoded is used.
	 * Part of code are taken from https://github.com/fictivekin/Resty.php.
	 *
	 * @param array $data Data array with parameter name as key and parameter value as value.
	 * @param array $files Optional. Array of files in form of: array('<parameter name>' => array('file_name' => '<file name>', 'contents' => '<file contents>'), ...).
	 * @param array $headers Optional. Placeholder for headers.
	 * @return string
	 */
	private function buildPostBody($data, $files = array(), &$headers = array()) {
		if (is_array($files) && count($files) > 0) {
			$post_body = array();
			$boundary = substr(md5(rand(0,32000)), 0, 10);

			if (is_array($data) && count($data) > 0) {
				foreach ($data as $name => $value) {
					$post_body[] = sprintf("--%s", $boundary);
					$post_body[] = sprintf("Content-Disposition: form-data; name=\"%s\"\n\n%s", $name, $value);
				}
			}
			$post_body[] = sprintf("--%s", $boundary);

			foreach ($files as $name => $file_data) {
				$file_name = $file_data['file_name'];
				$file_contents = $file_data['contents'];
				$content_type = 'application/octet-stream';

				$post_body[] = sprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"", $name, $file_name);
				$post_body[] = sprintf("Content-Type: %s", $content_type);
				$post_body[] = "Content-Transfer-Encoding: binary\n";
				$post_body[] = $file_contents;
				$post_body[] = sprintf("--%s--\n", $boundary);
			}

			if (is_array($headers)) {
				$headers = array();
			}
			$headers[] = 'Content-Type: multipart/form-data; boundary='.$boundary;

			return implode("\n", $post_body);
		} else {
			return http_build_query($data, '', '&');
		}
	}

	/**
	 * Prepares URL (and returns it) and POST data (updates it via reference).
	 *
	 * @param string $controller Kayako controller to call. Null to use default controller defined for object.
	 * @param string $method HTTP verb.
	 * @param array $parameters List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Placeholder for POST/PUT data.
	 * @return string
	 */
	private function getRequestData($controller, $method, $parameters = array(), &$data = array()) {
		$salt = mt_rand();
		$signature = base64_encode(hash_hmac('sha256', $salt, $this->config->getSecretKey(), true));

		$parameters_str = '';
		foreach ($parameters as $parameter) {
			$parameters_str .= sprintf("/%s", $parameter);
		}

		if ($this->config->getIsStandardURLType()) {
			$url = sprintf("%sindex.php?%s%s", $this->config->getBaseURL(), $controller, $parameters_str);
		} else {
			$url = sprintf("%sindex.php?e=%s%s", $this->config->getBaseURL(), $controller, $parameters_str);
		}

		switch ($method) {
			case self::METHOD_POST:
			case self::METHOD_PUT:
 				$data['apikey'] = $this->config->getAPIKey();
 				$data['salt'] = $salt;
 				$data['signature'] = $signature;
				break;
			case self::METHOD_GET:
			case self::METHOD_DELETE:
				$url .= sprintf("&apikey=%s&salt=%s&signature=%s", $this->config->getAPIKey(), $salt, urlencode($signature));
				break;
		}

		return $url;
	}

	/**
	 * Sends the request to Kayako server and returns parsed response.
	 *
	 * @param string $controller Kayako controller to call. Null to use default controller defined for object.
	 * @param string $method HTTP verb.
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Optional. Data array with parameter name as key and parameter value as value.
	 * @param array $files Optional. Array of files in form of: array('<parameter name>' => array('file_name' => '<file name>', 'contents' => '<file contents>'), ...).
	 * @throws kyException
	 * @return array
	 */
	protected function processRequest($controller, $method, $parameters = array(), $data = array(), $files = array()) {
		$url = $this->getRequestData($controller, $method, $parameters, $data);

		$headers = array();
		$request_body = $this->buildPostBody($data, $files, $headers);

		$curl_options = array(
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_URL => $url
		);

		switch ($method) {
			case self::METHOD_GET:
				break;
			case self::METHOD_POST:
				$curl_options[CURLOPT_POSTFIELDS] = $request_body;
				$curl_options[CURLOPT_POST] = true;
				break;
			case self::METHOD_PUT:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
				$curl_options[CURLOPT_POSTFIELDS] = $request_body;
				break;
			case self::METHOD_DELETE:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
		}

		$curl_options[CURLOPT_HTTPHEADER] = $headers;

		if ($this->config->isDebugEnabled()) {
			error_log('Sending REST request to Kayako:');
			error_log(sprintf('  %s: %s', $method, $curl_options[CURLOPT_URL]));
			if ($method === self::METHOD_POST || $method === self::METHOD_PUT) {
				error_log(sprintf('  Body: %s', $request_body));
			}
		}

		$curl_handle = curl_init();
		curl_setopt_array($curl_handle, $curl_options);

		$response = curl_exec($curl_handle);

		if ($this->config->isDebugEnabled()) {
			error_log('Response from Kayako server:');
			error_log($response);
		}

		//removing any output prior to proper XML response (ex. Kayako notices)
		$xml_start_pos = stripos($response, "<?xml");
		if ($xml_start_pos > 0) {
			$response = substr($response, $xml_start_pos);
		}

		if ($response === false)
			throw new kyException(sprintf('CURL error: %s (%s)', curl_error($curl_handle), curl_errno($curl_handle)));

		$http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		if ($http_status != 200)
			throw new kyException(sprintf("HTTP error: %s", $http_status));

		curl_close($curl_handle);

		if ($method === self::METHOD_DELETE)
			return null;

		$result = ky_xml_to_array($response);
		if ($result === false)
			throw new kyException("Error parsing XML response.");

		if (count($result) === 1 && array_key_exists('_contents', $result) && strlen($result['_contents']) === 0)
			$result = array();

		return $result;
	}

	/**
	 * Sends GET request to the server and returns parsed data.
	 *
	 * {@inheritdoc}
	 * @see kyRESTClientInterface::get()
	 */
	public function get($controller, $parameters = array()) {
		return $this->processRequest($controller, self::METHOD_GET, $parameters);
	}

	/**
	 * Creates object on the server by sending POST request and returns its data.
	 *
	 * {@inheritdoc}
	 * @see kyRESTClientInterface::post()
	 */
	public function post($controller, $parameters = array(), $data = array(), $files = array()) {
		return $this->processRequest($controller, self::METHOD_POST, $parameters, $data, $files);
	}

	/**
	 * Updates object on the server by sending PUT request and returns its new data.
	 *
	 * {@inheritdoc}
	 * @see kyRESTClientInterface::put()
	 */
	public function put($controller, $parameters = array(), $data = array(), $files = array()) {
		return $this->processRequest($controller, self::METHOD_PUT, $parameters, $data, $files);
	}

	/**
	 * Deletes object from server by sending DELETE request.
	 *
	 * {@inheritdoc}
	 * @see kyRESTClientInterface::delete()
	 */
	public function delete($controller, $parameters = array()) {
		$this->processRequest($controller, self::METHOD_DELETE, $parameters);
	}
}