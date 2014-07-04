<?php

/**
 * /ping/message?debug=2
 */
class PongNode extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        $res->setStatus(200);
        $res->addLog('looks good...');
        $res->setFormat('debug');
        $res->setResult(array(
            'pong'=>$req->params['msg']
        ));
    }
}