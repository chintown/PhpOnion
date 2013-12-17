<?php

class CharEncodingDetector extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, $res) {
        $this->next($req, $res);

        $payload = $req->payload;
        $text = $payload->text; //XXX payload
        $text = utf8_decode($text);
        $is_utf8 = is_utf8($text);
        $text = (!$is_utf8)
                ? mb_convert_encoding($text, "UTF-8", "BIG5")
                : $text;
        $result = array(
            'is_utf8'=> $is_utf8,
            'text'=> $text,
        );
        $res->setResult($result);
    }
}