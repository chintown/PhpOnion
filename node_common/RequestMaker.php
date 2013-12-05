<?php
$cwd = dirname(__FILE__).'/';
require $cwd.'../common/Request.php';

/*
 * parse from php system var to Request object with
 * verb, target, params and payload
 */
//
class RequestMaker extends BaseNode {
    var $res;
    function __construct() {

    }
    public function execute($req, $res) {
        $this->res = $res;

        // de($_GET);
        $verb = $this->parseVerb(); // GET, POST, PUT or DELETE
        $target = $this->parseTarget();
        $params = $this->parseParamsBy($verb);
        $payload = $this->parsePayload();
        $debug_info = $this->parseDebug();

        $req = new Request();
        $req->setVerb($verb);
        $req->setTarget($target);
        $req->setParams($params);
        $req->setPayload($payload);
        $req->setHttpAccept($_SERVER['HTTP_ACCEPT']);
        $req->setDebug($debug_info);

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
        // 1. $_POST does not work
        // 2. $_GET
        // array(1) {
        //     ["target"]=>
        //     string(33) "meta/key1=a&key2=b"
        // }
        $params = array();
        $parts = explode('/', $_GET['target'], 2);
        if (count($parts) == 2) {
            $param_str = $parts[1];
            $param_str = trim($param_str, '?');
            parse_str($param_str, $params);
        }
        return $params;
    }
    private function parsePayload() {
        return file_get_contents('php://input');
    }
    private function parseDebug() {
        return isset($_GET['debug']);
    }

    private function filterTarget($v) {
        return $v !== 'target';
    }

}