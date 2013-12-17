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