<?php

class Renderer extends BaseNode {
    function __construct() {
    }
    public function execute($req, $res) {
        $this->next($req, $res);

        $debug = DEV_MODE && $req->getDebug();

        if ($debug) {
            header("Content-Type: text/html; charset=UTF-8");
            $r_request = $res->dumpRequest();
            $r_response = $res->dumpResponse();
            $r_log = $res->dumpLogs();
            $r_chain = $res->dumpChainLogs();
            require 'core/debug.php';
        } else {
            $format = $res->getFormat();
            $format = ($debug) ? 'debug' : $format;
            $res->render($format);
        }
    }
}