<?php

require_once 'common/util.php';

class TestNode extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        $exec = array(
            'user' => get_process_user()
        );
        $server = array(
            'REMOTE_ADDR'=> $_SERVER['REMOTE_ADDR'],
            'REMOTE_HOST'=> $_SERVER['REMOTE_HOST'],
            'HTTP_REFERER'=> $_SERVER['HTTP_REFERER'],
        );
        $info = array_merge($exec, $server);
        var_dump($info);
    }
}