<?php

class BaseNode {
    var $nextNode;
    function __construct() {
    }
    public function setNext($nextNode) {
        $this->nextNode = $nextNode;
            // echo "assign    \n";var_dump($nextNode);
            // echo "to \n";var_dump($this);
    }
    public function execute(&$req, &$res) {
        // do something to process request

        $this->next($req, $res);

        // do something to process response
    }
    public function next(&$req, &$res) {
        $this->addChainLog(get_class($this));
        if (!isset($this->nextNode)) {
            $this->addChainLog("chain ends at ".get_class($this));
            return; // 1. exit if no next node
        }
        $this->addChainLog("beginning of ".get_class($this->nextNode));
        $this->nextNode->execute($req, $res); // 2. keep going for next node
        $this->addChainLog("end of ".get_class($this->nextNode));
    }

    private function addChainLog($msg) {
        if (!TRACE_NODE) {
            return;
        }
        $start_tag = "";
        $end_tag = "\n";
        echo $start_tag.$msg.$end_tag;
    }
}