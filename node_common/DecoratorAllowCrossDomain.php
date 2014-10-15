<?php

class DecoratorAllowCrossDomain extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        $res->addHeaders(array(
            'Access-Control-Allow-Origin'=> '*',
            'Access-Control-Allow-Methods'=> 'GET, PUT, POST, DELETE',
            'Access-Control-Allow-Headers'=> 'Origin, X-Requested-With, Content-Type, Accept',
        ));
        $this->next($req, $res);
    }
}