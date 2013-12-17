<?php

function is_valid_mongo_id($raw) {
    return ctype_alnum($raw) && strlen($raw) === 24  ;
}

function get_mongo_id($raw) {
    return new MongoId((string) $raw);
}

function serialize_mongo_id($raw) {
    return strval($raw);
}

function serialize_mongo_id_from($dict) {
    $dict['_id'] = serialize_mongo_id($dict['_id']);
    return $dict;
}

function serialize_mongo_id_from_if_needed($dict) {
    if (!isset($dict['_id'])) {
        return $dict;
    } else {
        return serialize_mongo_id_from($dict);
    }
}

