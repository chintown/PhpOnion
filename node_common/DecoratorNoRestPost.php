<?php

class DecoratorNoRestPost extends BaseNode {
    public function execute($req, $res) {
        $verb = $req->getVerb();

        if (strtolower($verb) !== 'post') {
            $this->next($req, $res);
        }
    }
}