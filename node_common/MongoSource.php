<?php

require_once 'common/mongo.php';

class MongoSource extends BaseNode {
    var $mongo, $db, $doc, $model;

    function __construct() {
        $this->mongo = new Mongo('mongodb://localhost');
        $this->db = $this->mongo->{$this->db};
        $this->model = $this->db->{$this->doc};
    }

    protected function selectOne($which, $fields) {
        if (!empty($fields)) {
            $r = $this->model->findOne($which, $fields);
        } else {
            $r = $this->model->findOne($which);
        }
        return $r;
    }
    protected function select($which, $fields, $options=array()) {
        $side_val = -1;
        if (!empty($fields)) {
            $cursor = $this->model->find($which, $fields);
        } else {
            $cursor = $this->model->find($which);
        }
        foreach ($options as $key => $value) {
            $key = substr($key, 1);
            $side_val = $cursor->$key($value);
        }
        $r = iterator_to_array($cursor, true);
        // TODO revamp the output structure
        if (array_key_exists('$count', $options)) {
            $r['count'] = $side_val;
        }
        return $r;
    }

    protected function insert($what) {
        $cursor = $this->model->insert($what, array("w" => 1));
        return isset($what['_id']) ? strval($what['_id']) : $what['_id'];
    }

    protected function composeIdQuery($raw) {
        return array('_id'=> get_mongo_id($raw));
    }
    protected function composePagingQuery($offset, $num) {
        $offset = ($offset !== 0 && empty($offset)) ? 0 : $offset;
        $num = ($num !== 0 && empty($num)) ? 10 : $num;
        return array(
            '$skip'=> intval($offset),
            '$limit'=> intval($num)
        );
    }
    protected function composeFieldsQuery($raw) {
        $result = array();
        foreach($raw as $field) {
            $result[$field] = true;
        }
        return $result;
    }
}