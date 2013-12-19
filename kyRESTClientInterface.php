<?php
/**
 * Interface of REST client.
 *
 * If you want to use another REST client than the default, make a class
 * implementing this interface and pass its instance to
 * @see kyConfig::setRESTClient() method.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @package Common\REST
 */
interface kyRESTClientInterface {
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
	 * Configuration injector.
	 *
	 * @param kyConfig $config Library configuration.
	 */
	public function setConfig(kyConfig $config);

	/**
	 * Should send GET request to the server and return parsed data.
	 *
	 * @param string $controller Kayako controller to call.
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @return array XML parsed to array in @see ky_xml_to_array() style.
	 */
	public function get($controller, $parameters = array());

	/**
	 * Should create object on the server by sending POST request and return its data.
	 *
	 * Format of $files parameter:
	 * <pre>
	 * array(
	 * 	'<parameter name>' =>
	 * 		array('file_name' => '<file name>', 'contents' => '<file contents>'),
	 * 	...repeat...
	 * )
	 * </pre>
	 *
	 * @param string $controller Kayako controller to call.
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Optional. Data array with parameter name as key and parameter value as value.
	 * @param array $files Optional. Array of files.
	 * @return array XML parsed to array in @see ky_xml_to_array() style.
	 */
	public function post($controller, $parameters = array(), $data = array(), $files = array());

	/**
	 * Should update object on the server by sending PUT request and return its new data.
	 *
	 * Format of $files parameter:
	 * <pre>
	 * array(
	 * 	'<parameter name>' =>
	 * 		array('file_name' => '<file name>', 'contents' => '<file contents>'),
	 * 	...repeat...
	 * )
	 * </pre>
	 *
	 * @param string $controller Kayako controller to call.
	 * @param array $parameters Optional. List of additional parameters (like object identifiers or search parameters).
	 * @param array $data Optional. Data array with parameter name as key and parameter value as value.
	 * @param array $files Optional. Array of files in form of: array('<parameter name>' => array('file_name' => '<file name>', 'contents' => '<file contents>'), ...).
	 * @return array XML parsed to array in @see ky_xml_to_array() style.
	 */
	public function put($controller, $parameters = array(), $data = array(), $files = array());

	/**
	 * Should delete object from server by sending DELETE request.
	 *
	 * @param string $controller Kayako controller to call.
	 * @param array $parameters List of additional parameters (like object identifiers or search parameters).
	 */
	public function delete($controller, $parameters = array());
}