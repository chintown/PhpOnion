<?php

require_once 'core/Request.php';

/*
 * parse from php system var to Request object with
 * verb, target, params and payload
 */
class RequestMaker extends BaseNode {
    var $res;
    function __construct() {

    }
    public function execute(&$req, &$res) {
        $this->res = $res;

        // de($_GET);
        $verb = $this->parseVerb(); // GET, POST, PUT or DELETE
        $target = $this->parseTarget();
        $params = $this->parseParamsBy($verb);
        $payload = $this->parsePayload();
        $debug_info = $this->parseDebug();
        $xdebug_info = $this->parseXdebug();

        $res->addChainLog(array("target_parts"=> explode('/', $_GET['target'], 2)));
        $res->addChainLog(array("get params"=> $_GET));

        if (!isset($req)) {
            $req = new Request();
            $req->setParams($params);
        } else {
            $req->updateParams($params);
        }
        $req->setVerb($verb);
        $req->setTarget($target);

        $req->setPayload($payload);
        $req->setHttpAccept(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null);
        $req->setDebug($debug_info);
        $req->setXdebug($xdebug_info);

        if (!$req->getXdebug()) {
            ini_set('xdebug.default_enable', FALSE);
            ini_set('html_errors', FALSE);
        }

        $this->next($req, $res);
    }

    private function parseVerb() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }
    private function parseTarget() {
        $parts = explode('/', $_GET['target'], 2);
        return $parts[0];
    }
    protected function parseParamsBy($verb) {
        $params = $_GET;
        if (isset($params['debug'])) {
            unset($params['debug']);
        }
        return $params;
    }
//    protected function parseParamsBy($verb) {
//        // 1. $_POST does not work
//        // 2. $_GET
//        // array(1) {
//        //     ["target"]=>
//        //     string(33) "meta/key1=a"
//        //      ["key2"]=>
//        //     string(1) "b"
//        // }
//
//        $params = array();
//        $parts = explode('/', $_GET['target'], 2);
//        if (count($parts) == 2) {
//            $param_str = $parts[1];
//            parse_str($param_str, $params);
//        }
//
//        $params = array_merge($params, $_GET);
//        unset($params['target']);
//        if (isset($params['debug'])) {
//            unset($params['debug']);
//        }
//        return $params;
//    }
    private function parsePayload() {
        return file_get_contents('php://input');
    }
    private function parseDebug() {
        return isset($_GET['debug']);
    }
    private function parseXdebug() {
        return ($_GET['debug'] === '2') ? false : true;
    }

    private function filterTarget($v) {
        return $v !== 'target';
    }

}