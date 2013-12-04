<?php
$cwd = dirname(__FILE__).'/';
require $cwd.'../common/MongoRequest.php';

/*
 * parse from mongo-specific syntax
 */
class RequestMakerMongo extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, $res) {
        $mongoReq = new MongoRequest($req);
        $params = $req->params;
        $payload = json_decode($req->payload);

        if ($params === null) {
            $res->setStatus(400);
            $res->setFormat('text');
            $res->setResult('');
            return;
        }

        $expecteds = array('$limit', '$skip', '$sort', '$count');
        $actuals = array();
        foreach ($expecteds as $expected) {
            if (array_key_exists($expected, $params)) {
                $actuals[$expected] = $params[$expected];
                unset($mongoReq->params[$expected]);
            }
        }
        $expecteds = array('upsert');
        if (isset($payload)) {
            foreach ($expecteds as $expected) {
                if (isset($payload->{$expected})) {
                    $actuals[$expected] = $payload->{$expected};
                    unset($payload->{$expected});
                }
            }
            $mongoReq->payload = json_encode($payload);
        }
        $mongoReq->setOptions($actuals);

        $expected = '$fields';
        if (array_key_exists($expected, $params)) {
            $mongoReq->setFields($params[$expected]);
            unset($mongoReq->params[$expected]);
        }

        if (isset($mongoReq->params['_id'])) {
            $mongoReq->params['_id'] = new MongoId((string) $mongoReq->params['_id']);
            $mongoReq->setFindOne();
        }


        $req = $mongoReq;

        $this->next($req, $res);
    }
}