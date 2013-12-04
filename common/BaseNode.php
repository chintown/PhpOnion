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
        // do something
        // echo "[override me from ".get_class($this)."]\n";
        $this->next($req, $res);
        // do something
    }
    public function next(&$req, &$res) {
        $this->set_trace(get_class($this));
        if (!isset($this->nextNode)) {
            $this->set_trace("chain ends at ".get_class($this));
            return;
        }
        $this->set_trace("beginning of ".get_class($this->nextNode));
        $this->nextNode->execute($req, $res);
        $this->set_trace("end of ".get_class($this->nextNode));
    }

    private function set_trace($msg) {
        if (!TRACE_NODE) {
            return;
        }
        $start_tag = "";
        $end_tag = "\n";
        echo $start_tag.$msg.$end_tag;
    }
}