<?php

class Foo extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        $this->next($req, $res);
    }
}