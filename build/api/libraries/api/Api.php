<?php

restore_error_handler();
restore_exception_handler();

$apiAvl = ENVIRONMENT === "development" || ENVIRONMENT === "testing";
define('_API_TESTING_MODE_', $apiAvl && !empty($_GET['_testing']));
define('_API_DEBUG_MODE_', _API_TESTING_MODE_ || ($apiAvl && !empty($_GET['_debug'])));



class Api extends ApiAbstract {

	const TEST_MODE = _API_TESTING_MODE_;

	const DEBUG_MODE = _API_DEBUG_MODE_;

	const ERROR_CELL = 'system';

	const RESPONSE = 'response';

	const REQUEST = 'request';

	/** @var ApiController */
	public $context;

	/** @var Api */
	public $api;

	/** @var ApiInput */
	public $input;

	/** @var ApiOutput */
	public $output;

	/** @var ApiAccess */
	public $access;

	/** @var ApiServer */
	public $server;

	/** @var ApiFilter */
	public $filter;

	/** @var ApiFormat */
	public $format;

	/** @var ApiValidation */
	public $validation;

	protected $_parts = array();

	public $apiData = array();

	public $methodsMap = array();

	private $_launched = false;

	private $_launchParams = array();

	function __construct ($method, $uri, array $callParams = array(), array $inputData = array()) {
		$this->api = $this;

		$this->_launchParams['method'] = strtoupper($method);

		if (!preg_match('/^GET|HEAD|OPTIONS|POST|PUT|DELETE|TRACE|PATCH$/', $this->_launchParams['method'])) {
			throw new Exception('invalid method name "'.$method.'" !');
		}

		$this->_launchParams['uri']    = preg_replace('/^([^\?]+)\?(.*)$/', '$1', $uri);
		$this->_launchParams['search'] = preg_replace('/^([^\?]+)\?(.*)$/', '$2', $uri);

		$this->_launchParams['ip']            = ApiUtils::get($inputData, 'ip', '0.0.0.0');
		$this->_launchParams['ssl']           = (bool) ApiUtils::get($inputData, 'ssl', false);
		$this->_launchParams['port']          = ApiUtils::get($inputData, 'port', 80);
		$this->_launchParams['scheme']        = ApiUtils::get($inputData, 'shcme', 'http');
		$this->_launchParams['host']          = ApiUtils::get($inputData, 'host', 'localhost');
		$this->_launchParams['input/body']    = ApiUtils::get($inputData, 'body', array());
		$this->_launchParams['input/args']    = ApiUtils::get($inputData, 'args', array());
		$this->_launchParams['input/query']   = ApiUtils::get($inputData, 'query', array());
		$this->_launchParams['input/headers'] = ApiUtils::get($inputData, 'headers', array());

		$this->_launchParams['controller'] = ApiUtils::get($callParams, 'controller', null);
		$this->_launchParams['action']     = ApiUtils::get($callParams, 'action', null);

		$this->_launchParams['uri_insensitive_case'] = ApiUtils::get($callParams, 'uri_insensitive_case', false);

		if ($this->_launchParams['uri_insensitive_case']) {
			$this->_launchParams['uri'] = strtolower($this->_launchParams['uri']);
		}

		$cellName = $this->_compileCellName($this->_launchParams['method'], $this->_launchParams['uri'], $this->_launchParams['input/args']);

		$apiData = array();
		$methodsMap = array();

		$apiFile =     VAR_DIR.DS.'system'.DS.sha1($cellName).'.php';
		$methodsFile = VAR_DIR.DS.'system'.DS.sha1('methods').'.php';

		if (is_file($apiFile)) {
			$apiData = include($apiFile);
		}
		if (is_file($methodsFile)) {
			$methodsMap = include($methodsFile);
		}

		$this->apiData = $apiData;
		$this->methodsMap = $methodsMap;

		$this->context = $this->_launchParams['controller'];

		$response = $this->api->get(self::RESPONSE);

		$this->_launchParams['action_to_call'] = $this->context->compileMethodName($this->_launchParams['action'], $this->_launchParams['method'], $this->methodsMap, $response['type']);

	}

	public function getLaunchParam ($name) {
		return $this->_launchParams[$name];
	}

	protected function _compileCellName ($method, $uriCall, array $arguments) {
		$uriCall = preg_replace('/\?(.+)$/', '', $uriCall);
		$uriCall = str_replace('\\', '/', $uriCall);
		$uriCall = preg_replace('#(/+)$#', '', $uriCall);

		if ($arguments) {
			$implArgs = implode('|', $arguments);
			$uriCall = preg_replace('#^('.$implArgs.')$#', '<param>', $uriCall);
			$uriCall = preg_replace('#^('.$implArgs.')/#', '<param>/', $uriCall);
			$uriCall = preg_replace('#/('.$implArgs.')/#', '/<param>/', $uriCall);
			$uriCall = preg_replace('#/('.$implArgs.')$#', '/<param>', $uriCall);
		}

		$uriCall = preg_replace('/^\/+/i', '', $uriCall);
		$cellName = $method.":".$uriCall;

		return $cellName;
	}

	function launch () {
		if ($this->_launched) {
			return;
		}
		$this->_launched = true;

		$this->setPart('input',      new ApiInput($this));
		$this->setPart('filter',     new ApiFilter($this));
		$this->setPart('output',     new ApiOutput($this));
		$this->setPart('access',     new ApiAccess($this));
		$this->setPart('validation', new ApiValidation($this));
		$this->setPart('format',     new ApiFormat($this));
		$this->setPart('server',     new ApiServer($this));

		// INIT
		foreach ($this->_parts as $part) {
			/** @var ApiPartAbstract $part */
			$part->init();
		}

		if (!method_exists($this->context, $this->getLaunchParam('action_to_call')) || !$this->apiData) {
			$this->error('Method Not Allowed', 405, true);
			return;
		}

		// CHECK
		foreach ($this->_parts as $part) {
			/** @var ApiPartAbstract $part */
			$part->check();
		}

		// PREPARE
		foreach ($this->_parts as $part) {
			/** @var ApiPartAbstract $part */
			$part->prepare();
		}

		$result = $this->context->callMethod($this->getLaunchParam('action_to_call'));

		if (isset($result)) {
			$this->api->output->data($result);
		}

		$this->api->output->send();
	}

	protected function &setPart ($name, &$part) {
		$this->$name = $part;
		$this->_parts[$name] = & $part;
		return $part;
	}

	function getErrors () {
		$errors = array();
		foreach ($this->_parts as $name => $part) {

			/** @var ApiPartAbstract $part */

			$err = $part->getErrors();
			if (!empty($err)) {
				$errors[$name] = $err;
			}
		}
		$err = parent::getErrors();
		if (!empty($err)) {
			$errors[self::ERROR_CELL] = $err;
		}
		return $errors;
	}

	function valid () {
		$vars = get_object_vars($this);
		foreach ($vars as $var) {
			if ($var instanceof ApiPartAbstract) {
				if (!$var->valid()) {
					return false;
				}
			}
		}
		if ($this->getErrors()) {
			return false;
		}
		return true;
	}

	function get ($name = null, $default = null) {
		if (is_null($name)) {
			return $this->apiData;
		}

		return isset($this->apiData[$name]) ? $this->apiData[$name] : $default;
	}

}