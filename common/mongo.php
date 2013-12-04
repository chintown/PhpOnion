<?php

function is_valid_id($raw) {
    return ctype_alnum($raw) && strlen($raw) === 24  ;
}

function extract_cover_path($extra) {
    if (!isset($extra['archive']) || empty($extra['archive']['root']) || empty($extra['archive']['opf'])
        || !isset($extra['cover'])|| empty($extra['cover']['href'])) {
        return '';
    }
    return implode('/', array(
        ARCHIVE_ROOT,
        $extra['archive']['root'],
        $extra['archive']['opf'],
        $extra['cover']['href'],
    ));
}