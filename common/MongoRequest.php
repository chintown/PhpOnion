<?php
/*
 * extend Request to Mongo-awareness
 */
class MongoRequest extends Request {
    var $options = array();
    var $fields = array();
    var $find_one = false;

    function __construct(Request $parentInstance=null) {
        parent::__construct();
        if ($parentInstance !== null) {
            foreach( get_object_vars($parentInstance) as $name => $value) {
                $this->$name = $value;
            }
        }
    }
    public function setOptions($options) {
        $this->options = $options;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setFindOne() {
        return $this->find_one = true;
    }
    public function isFindOne() {
        return $this->find_one;
    }
}