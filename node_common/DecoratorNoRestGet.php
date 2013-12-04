<?php

class DecoratorNoRestDeleteGet extends BaseNode {
    public function execute($req, $res) {
        $verb = $req->getVerb();

        if (strtolower($verb) !== 'get') {
            $this->next($req, $res);
        }
    }
}