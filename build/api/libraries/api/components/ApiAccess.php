<?php


class ApiAccess extends ApiComponentAbstract {

    const ONLY_OWNER = 'only_owner';
    const NEED_LOGIN = 'need_login';
    const ONLY_AJAX  = 'only_ajax';
    const ACTION     = 'action';
    const UNDEFINED  = 'api_undefined';
    const IP_WHITE_LIST  = 'ip_white_list';
    const IP_BLACK_LIST  = 'ip_black_list';


    private $_accesses = array();

    function init(){
        $this->_accesses = $this->api->get('access');
    }

    function check () {
        $this->checkOnlyAjax();
        $this->checkIpWhiteList();
        $this->checkApi();
        $this->checkNeedLogin();
        $this->checkContextToCall();
    }

    function checkIpWhiteList () {
        if (empty($this->_accesses[ApiAccess::IP_WHITE_LIST])) {
            return;
        }

        $whiteList = isset($this->_accesses[ApiAccess::IP_WHITE_LIST]) ? $this->_accesses[ApiAccess::IP_WHITE_LIST] : array();
        $whiteList[] = '0.0.0.0';
        $whiteList[] = '127.0.0.1';
        foreach ($whiteList as &$ip) {
            $ip = trim($ip);
        }
        if (!in_array($this->api->server->ip, $whiteList)) {
			$this->api->output->status(401);
        }
    }

    protected function checkIpBlackList () {
        if (empty($this->_accesses[ApiAccess::IP_BLACK_LIST])) {
            return;
        }

        $blacklist = $this->_accesses[ApiAccess::IP_BLACK_LIST];
        foreach ($blacklist as &$ip) {
            $ip = trim($ip);
        }
        if (in_array($this->api->server->ip, $blacklist)) {
            $this->error(null, 401, true);
        }
    }

    function checkApi(){
        if(empty($this->api->apiData)){
            $this->error(ApiAccess::UNDEFINED, 405, true);
            $this->api->output->send();
        }
    }

    function checkNeedLogin(){
        if(!empty($this->_accesses[ApiAccess::NEED_LOGIN]) && !$this->api->context->user->isLogged()){
            $this->error(ApiAccess::NEED_LOGIN, 401, true);
            $this->api->output->send();
        }
    }

    function checkOnlyAjax(){
        if (!empty($this->_accesses[ApiAccess::ONLY_AJAX]) && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
            $this->error(ApiAccess::ONLY_AJAX, 403);
            $this->api->output->send();
        }
    }

    function checkContextToCall () {
        $controllerName = get_class($this->api->context);
        $hasAccess = $this->api->context->hasAccess($this->api->getLaunchParam('method'), $controllerName, $this->api->getLaunchParam('action_to_call'));
        if(!$hasAccess){
            $this->error(ApiAccess::ACTION, 403);
            $this->api->output->send();
        }
        return $hasAccess;
    }

    function onlyOwner(){
        return !empty($this->_accesses[ApiAccess::ONLY_OWNER]);
    }
}