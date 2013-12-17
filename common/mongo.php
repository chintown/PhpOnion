<?php

function is_valid_id($raw) {
    return ctype_alnum($raw) && strlen($raw) === 24  ;
}
