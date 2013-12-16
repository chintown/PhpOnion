<?php

require_once 'RequestMaker.php';

class RequestMakerImage extends RequestMaker {
    function __construct() {
    }
    protected function parseParamsBy($verb) {
        $params = array();
        $parts = explode('/', $_GET['target'], 2);
        if (count($parts) === 2 && !empty($parts[1])) {
            $file_info = explode('/', $parts[1]);
            $params['filename'] = $file_info[0];
            $dimension = (count($file_info) === 2) ? $file_info[1] : '0x0'; // serve as original dimension
            list($params['thumb_w'], $params['thumb_h']) = explode('x', $dimension);
            $params['thumb_w'] = intval($params['thumb_w']);
            $params['thumb_h'] = intval($params['thumb_h']);
        }
        return $params;
    }
}