<?php

/*
 * parse array memeber of Request to internal model.
 *
 * params
 *    - map to condition
 *    - comprises of list of conditions or actions
 * payload
 *    - maps to action (CREATE, UPDATE)
 *    - comprises of key-value pairs
 *    - modeled as array
 */
class RequestParserForm extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        $params = $req->getParams();
        $params = $this->modelParams($params);
        $req->setParams($params);

        $payload = $req->getPayload();
        $payload = $this->modelPayload($payload, $res);
        $req->setPayload($payload);

        $this->next($req, $res);
    }

    private function modelParams($params) {
        return $params;
    }
    private function modelPayload($payload, &$res) {
        parse_str($payload, $fields);
        return $fields;
    }
}