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
        $r = serialize_mongo_id_from($r);
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
        $r = map($r, 'serialize_mongo_id_from_if_needed', false);
        return $r;
    }

    protected function insert($what) {
        $cursor = $this->model->insert($what, array("w" => 1));
        return serialize_mongo_id_from_if_needed($what);
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
    protected function composeFieldsQuery() {
        $result = array();
        $arg_list = func_get_args();
        foreach($arg_list as $field) {
            $result[$field] = true;
        }
        return $result;
    }
    protected function composeTurnOnQuery() {
        $result = array();
        $arg_list = func_get_args();
        foreach($arg_list as $field) {
            $result[$field] = true;
        }
        return $result;
    }
    protected  function update($which, $what, $option) {
        if (empty($option)) {
            return $this->model->update($which, $what);
        } else {
            return $this->model->update($which, $what, $option);
        }
    }
    protected  function delete($which) {
        return $this->model->remove($which);
    }

    protected function logMongoError($res) {
        $last_err = $this->db->lastError();
        if ($last_err['err'] !== null) {
            $res->addLog(array(
                'mongo'=> var_export($last_err, true)
            ));
        }
    }
}