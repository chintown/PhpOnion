<?php

class DecoratorNoRestDelete extends BaseNode {
    public function execute(&$req, &$res) {
        $verb = $req->getVerb();

        if (strtolower($verb) !== 'delete') {
            $this->next($req, $res);
        }
    }
}