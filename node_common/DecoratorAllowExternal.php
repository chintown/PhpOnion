<?php
class DecoratorAllowExternal extends BaseNode {
    function __construct() {
    }
    public function execute($req, $res) {
        $res->setBlockExternal(false);
        $this->next($req, $res);
    }
}