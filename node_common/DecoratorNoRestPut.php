<?php

class DecoratorNoRestPut extends BaseNode {
    public function execute($req, $res) {
        $verb = $req->getVerb();

        if (strtolower($verb) !== 'put') {
            $this->next($req, $res);
        }
    }
}