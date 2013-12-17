<?php

function execute_external($executable, $arguments, $extra='', $exports=array(), $working_dir='') {
    $exports = map($exports, function($k, $v) {return "$k=$v";});
    $exports = (empty($exports)) ? '' : "export " . join(';', $exports);

    $arguments = map($arguments, function($v) {return '"'.$v.'"';}, $is_pair_para=false);
    $arguments = join(' ', $arguments);

    $cmd = "cd $working_dir; $exports $executable $arguments $extra 2>&1";
    $result = array();
    exec($cmd, $result, $return_code);
    return array(
        'out'=> $result,
        'code'=> $return_code,
        'cmd'=> $cmd
    );
}

function get_process_user() {
    $info = posix_getpwuid(posix_getuid());
    return $info['name'];
}