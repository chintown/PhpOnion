<?php
    // 0 | 1
    define('DEV_MODE',0);
    if (DEV_MODE) {
        ini_set('display_errors', 'On');
    }
    // remote (for linode dev) | local (for Mac dev)
    define('ENV','remote');
    define('TRACE_NODE', false);
    define('MAX_UPLOADED_IMAGE_BYTE', 10000000); // 10,000kb
?>
