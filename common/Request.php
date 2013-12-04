<?php
class Request {
    var $verb;
    var $target;
    var $params;
    var $payload;
    var $http_accept;
    var $file;
    var $debug;

    public function __construct() {
        $this->verb        = 'get';
        $this->target      = '';
        $this->params      = array();
        $this->payload     = '';
        $this->http_accept = 'json';
        $this->debug       = false;
    }

    public function setVerb($verb) {
        $this->verb = $verb;
    }
    public function setTarget($target) {
        $this->target = $target;
    }
    public function setParams($params) {
        $this->params = $params;
    }
    public function setPayload($payload) {
        $this->payload = $payload;
    }
    public function setHttpAccept($accept) {
        $this->http_accept = $accept;
    }
    public function setFile($file) {
        $this->file = $file;
    }
    public function setDebug($debug) {
        $this->debug = $debug;
    }

    public function getVerb() {
        return $this->verb;
    }
    public function getTarget() {
        return $this->target;
    }
    public function getParams() {
        return $this->params;
    }
    public function getPayload() {
        return $this->payload;
    }
    public function getHttpAccept() {
        return $this->http_accept;
    }
    public function getFile() {
        return $this->file;
    }    public function getDebug() {
        return $this->debug;
    }

}