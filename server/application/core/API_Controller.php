<?php

require_once(SERVER_DIR."/".APPPATH.'/libraries/REST_Controller.php');
require_once(SERVER_DIR."/".APPPATH.'/libraries/api/Api.php');
require_once(SERVER_DIR."/".APPPATH.'/libraries/data_transfer/DataTransfer.php');

abstract class API_Controller extends REST_Controller {

    /**
     * @var DataTransfer
     */
    private $_transfer = null;
    /**
     * @var Api
     */
    private $_api = null;
    /**
     * @var ApiCurrent
     */
    private $_currentApi = null;

    public function __construct(){
        parent::__construct();
        if(is_null($this->_transfer)){
            $this->_transfer = new DataTransfer($this);
        }
        $this->_api = new Api();
    }

    protected $_inputData = array();

    public function api(){
        return $this->_currentApi;
    }

    /**
     * @var array|string $dataName [optional]
     * @var mixed $dataValue [optional]
     * @return DataTransfer
     */
    public function transfer($dataName = null, $dataValue = null){
        if(!is_null($dataName)){
            $this->_transfer->data($dataName, $dataValue);
        }
        return $this->_transfer;
    }

    protected function _initCurrentApi($method, $url, $args){
        $currApi = $this->_api->getCurrentApi($method, $url, $args);
        if(empty($currApi)){
            $this->transfer()->error(405);
            return null;
        }
        $this->_currentApi = $currApi;
        return $currApi;
    }

    public function _remap($call, $arguments){
        $this->_initCurrentApi($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"], $arguments);
        parent::_remap($call, $arguments);
    }

    protected function _fire_method($call, $arguments){
        $method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $controllerName = get_class($call[0]);
        $methodName = $call[1];
        $callPath = strtolower($controllerName)."/".preg_replace('/_'.$method.'$/i', '', strtolower($methodName));
        if (!$this->_checkHandlerAccess($method, $callPath)) {
            $this->transfer()->error(403);
        }
        if (!$this->transfer()->hasError()) {
            if ($this->api()->checkInputFieldErrors($this->_args, $arguments, $_GET)) {
                $this->transfer()->error(400);
            }
        }
        if (!$this->transfer()->hasError()) {
            parent::_fire_method($call, $arguments);
        }

        $this->_sendStatus();

        // SEND RESPONSE
        $response = $this->transfer()->getAllData();
        if($this->transfer()->hasError()){
            $response["data"] = array();
        }else{
            if(isset($response["data"])){
                $data = $this->api()->prepareResponseData($response["data"]);
                if(is_null($data)){
                    $response["data"] = array();
                    $this->transfer()->error(404);
                } else {
                    $response["data"] = $data;
                }
            } else {
                $this->transfer()->error(500);
            }
        }

        // DEBUG DATA (only for development or testing mode
        if(ENVIRONMENT == "development" || ENVIRONMENT == "testing"){
            $input = array();
            $input["url"] = $_SERVER['REQUEST_URI'];
            $input["method"] = $_SERVER['REQUEST_METHOD'];
            $input["input"] = array();
            if($this->api()){
                $input["api_name"] = $this->api()->getName();
                $input["data"] = array(
                    "source" => INPUT_DATA,
                    "params"    => $this->api()->param(),
                    "arguments" => $this->api()->argument(),
                    "filters"   => $this->api()->filter()
                );
            }
            $response["debug"] = $input;
        }

        $this->response($response, $this->transfer()->getCode());
        exit("");
    }

    protected function _sendStatus(){
        $method  = strtoupper($_SERVER["REQUEST_METHOD"]);
        // SMART STATUS CODES
        if (!$this->transfer()->hasError()) {
            if ($method == "POST") {
                if ($this->transfer()->data()->getResult()) {
                    $this->transfer()->code(201); // created new resource
                } else {
                    if(!$this->transfer()->hasError()){
                        $this->transfer()->error(400); // empty GET result
                    }
                }
            }else if($method == "PUT"){
                if($this->transfer()->data()->getResult()){
                    $this->transfer()->code(200); // updated resource
                }else{
                    if(!$this->transfer()->hasError()){
                        $this->transfer()->error(400); // empty GET result
                    }
                }
            }else if($method == "GET"){
                // ONLY 200 or SOMETHING CUSTOM
            }else if($method == "DELETE"){
                // ONLY 200 or SOMETHING CUSTOM
                if(!$this->transfer()->hasError()){
                    if(!$this->transfer()->data()->getResult()){
                        $this->transfer()->error(500); // you must send Boolean response
                    }
                }
            }
        }
    }

    protected function _checkHandlerAccess($method, $callPath){
        $callName = $method." ".$callPath;
        $callNameAny = "ANY ".$callPath;

        // TODO: must use ACCESS MODEL and USER MODEL to create Access-array
        $accesses = array();

        if(isset($accesses[$callNameAny]) && !$accesses[$callNameAny]){
            return false;
        }
        if(isset($accesses[$callName]) && !$accesses[$callName]){
            return false;
        }
        return true;
    }

}