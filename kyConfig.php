<?php
/**
 * Class holding library configuration.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @package Common
 */
class kyConfig {
	/**
	 * Base URL of Kayako REST API.
	 * @var string
	 */
	private $base_url = null;

	/**
	 * Kayako REST API key.
	 * @var string
	 */
	private $api_key = null;

	/**
	 * Kayako REST API secret key.
	 * @var string
	 */
	private $secret_key = null;

	/**
	 * REST client.
	 * @var kyRESTClientInterface
	 */
	private $rest_client = null;

	/**
	 * Default format of datetime object properties used in getters and setters.
	 * @var string
	 */
	private $datetime_format = 'Y-m-d H:i:s';

	/**
	 * Default format of date object properties used in getters and setters.
	 * @var string
	 */
	private $date_format = 'Y-m-d';

	/**
	 * Request URL construction type.
	 * True for standard URL (ex. http://example.domain.com/api/index.php?/Module/Controller/Action&parameter=1&).
	 * False to use "e" parameter in URL (ex. http://example.domain.com/api/index.php?e=/Module/Controller/Action&parameter=1&).
	 * @see http://wiki.kayako.com/display/DEV/Kayako+REST+API#KayakoRESTAPI-RequestURLtype
	 * @var bool
	 */
	private $is_standard_url_type = true;

	/**
	 * True to enable outputing REST requests and responses to error log.
	 * @var bool
	 */
	private $is_debug_enabled = false;

	/**
	 * Current configuration.
	 * @var kyConfig
	 */
	static private $current_config = null;

	/**
	 * Initializes client configuration object.
	 *
	 * @param string $base_url Base URL of Kayako REST API.
	 * @param string $api_key Kayako REST API key.
	 * @param string $secret_key Kayako REST API secret key.
	 */
	function __construct($base_url, $api_key, $secret_key) {
		$this->setBaseURL($base_url);
		$this->setAPIKey($api_key);
		$this->setSecretKey($secret_key);
	}

	/**
	 * Returns current library configuration.
	 *
	 * @throws kyException
	 * @return kyConfig
	 */
	static public function get() {
		if (self::$current_config === null)
			throw new kyException('Kayako PHP API Library is not initialized. Use kyConfig::set() to initialize it.');

		return self::$current_config;
	}

	/**
	 * Sets the current library configuration.
	 *
	 * Should be called before before contacting the API.
	 *
	 * @param kyConfig $config Configuration.
	 * @return kyConfig
	 */
	static public function set(kyConfig $config) {
		self::$current_config = $config;
		return self::$current_config;
	}

	/**
	 * Returns base URL of Kayako REST API.
	 *
	 * @return string
	 */
	public function getBaseURL() {
		return $this->base_url;
	}

	/**
	 * Sets the base URL of Kayako REST API.
	 *
	 * @param string $base_url Base URL of Kayako REST API.
	 * @return kyConfig
	 */
	public function setBaseURL($base_url) {
		//URL can't end with PHP file (for compatibility with $controller_as_query = false) and can't contain any query parameters
		$to_remove = basename(parse_url($base_url, PHP_URL_PATH));

		$to_remove_pos = false;
		if (strlen($to_remove) > 0 && stripos($to_remove, '.php')) {
			$to_remove_pos = stripos($base_url, $to_remove);
		}

		if ($to_remove_pos !== false) {
			$base_url = substr($base_url, 0, $to_remove_pos);
		}

		$this->base_url = rtrim($base_url, '/').'/';

		return $this;
	}

	/**
	 * Returns Kayako REST API key.
	 *
	 * @return string
	 */
	public function getAPIKey() {
		return $this->api_key;
	}

	/**
	 * Sets Kayako REST API key.
	 *
	 * @param string $api_key Kayako REST API key.
	 * @return kyConfig
	 */
	public function setAPIKey($api_key) {
		$this->api_key = $api_key;
		return $this;
	}

	/**
	 * Returns Kayako REST API secret key.
	 *
	 * @return string
	 */
	public function getSecretKey() {
		return $this->secret_key;
	}

	/**
	 * Sets Kayako REST API secret key.
	 *
	 * @param string $secret_key Kayako REST API secret key.
	 * @return kyConfig
	 */
	public function setSecretKey($secret_key) {
		$this->secret_key = $secret_key;
		return $this;
	}

	/**
	 * Returns REST client instance.
	 *
	 * @return kyRESTClientInterface
	 */
	public function getRESTClient() {
		if ($this->rest_client === null) {
			$this->rest_client = new kyRESTClient();
			$this->rest_client->setConfig($this);
		}

		return $this->rest_client;
	}

	/**
	 * Sets REST client.
	 *
	 * @param kyRESTClientInterface $rest_client REST client instance.
	 * @return kyConfig
	 */
	public function setRESTClient(kyRESTClientInterface $rest_client) {
		$this->rest_client = $rest_client;
		return $this;
	}

	/**
	 * Returns default format of datetime object properties used in getters and setters.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @return string
	 */
	public function getDatetimeFormat() {
		return $this->datetime_format;
	}

	/**
	 * Sets default format of datetime object properties used in getters and setters.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $datetime_format Default format of datetime object properties used in getters and setters.
	 * @return kyConfig
	 */
	public function setDatetimeFormat($datetime_format) {
		$this->datetime_format = $datetime_format;
		return $this;
	}

	/**
	 * Returns default format of date object properties used in getters and setters.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @return string
	 */
	public function getDateFormat() {
		return $this->date_format;
	}

	/**
	 * Sets default format of date object properties used in getters and setters.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $date_format Default format of date object properties used in getters and setters.
	 * @return kyConfig
	 */
	public function setDateFormat($date_format) {
		$this->date_format = $date_format;
		return $this;
	}

	/**
	 * Returns request URL construction type.
	 * Returns True for standard URL and False to use "e" parameter in URL.
	 * @see http://wiki.kayako.com/display/DEV/Kayako+REST+API#KayakoRESTAPI-RequestURLtype
	 *
	 * @return bool
	 */
	public function getIsStandardURLType() {
		return $this->is_standard_url_type;
	}

	/**
	 * Sets request URL construction type.
	 * True for standard URL (ex. http://example.domain.com/api/index.php?/Module/Controller/Action&parameter=1&).
	 * False to use "e" parameter in URL (ex. http://example.domain.com/api/index.php?e=/Module/Controller/Action&parameter=1&).
	 * @see http://wiki.kayako.com/display/DEV/Kayako+REST+API#KayakoRESTAPI-RequestURLtype
	 *
	 * @param bool $is_standard_url_type True for standard URL. False to use "e" parameter in URL.
	 * @return kyConfig
	 */
	public function setIsStandardURLType($is_standard_url_type) {
		$this->is_standard_url_type = $is_standard_url_type;
		return $this;
	}

	/**
	 * Returns whether debug mode is enabled.
	 * When enabled, REST requests and responses are logged using error_log.
	 *
	 * @return bool
	 */
	public function isDebugEnabled() {
		return $this->is_debug_enabled;
	}

	/**
	 * Enables or disables debug mode.
	 * When enabled, REST requests and responses are logged using error_log.
	 *
	 * @param bool $is_debug_enabled
	 * @return kyConfig
	 */
	public function setDebugEnabled($is_debug_enabled) {
		$this->is_debug_enabled = $is_debug_enabled;
		return $this;
	}
}
