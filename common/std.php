<?php

/* data manipulation */
function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}
/**
 * get key-value pairs of given keys from the associated array
 * and set empty string value if key does not exist
 * @param array $stack <p>
 * The raw array.
 * </p>
 * @param string $key1, $key2, ... <p>
 * a list of expected keys
 * </p>
 * @return array filtered array.
 */
function pickup() {
    $picked = array();
    $arg_list = func_get_args();
    $actuals = array_shift($arg_list);
    foreach($arg_list as $i=>$expected) {
        if (isset($actuals[$expected])) {
            $picked[$expected] = $actuals[$expected];
        } else {
            $picked[$expected] = '';
        }
    }
    return $picked;
}

/**
 * @param array $stack <p>
 * The raw array.
 * </p>
 * @param string|array $expectations <p>
 * a list of expectation condition
 * </p>
 * @see expect
 *
 * @throws Exception if any expectaction is mismatched
 */
function expect_dict($givens, $expectations) {
    foreach ($expectations as $field=> $methods) {
        $actual = $givens[$field];

        if (is_string($methods)) {
            $methods = explode('|', $methods);
        }
        foreach ($methods as $raw_method) {
            expect($actual, $raw_method, $field);
        }
    }
}

/**
 * @param $given <p>
 * Value to be checked
 * </p>
 * @param string|callable $expectation <p>
 *  string - [!]?(empty|string|int|float|numeric|array|json)[?]?
 *  callable - a predicator which return whether the $given matches expectation
 * </p>
 * @param string $given_name
 * @throws Exception
 */
function expect($given, $expectation, $given_name='') {
    $is_match = false;
    if (is_string($expectation)) {
        if (substr($expectation, 0, 1) === '!') {
            $should_inverse = true;
            $method = substr($expectation, 1);
        } else {
            $should_inverse = false;
            $method = $expectation;
        }

        if (substr($method, -1) === '?') {
            $method = substr($method, 0, -1);
            $skip_if_empty = true;
        } else {
            $skip_if_empty = false;
        }

        if ($skip_if_empty && empty($given)) {
            return;
        }

        if ($method == 'empty') {
            $is_match = empty($given);
        } else if ($method == 'string') {
            $is_match = is_string($given);
        } else if ($method == 'int') {
            $is_match = is_int($given);
        } else if ($method == 'float') {
            $is_match = is_float($given);
        } else if ($method == 'numeric') {
            $is_match = is_numeric($given);
        } else if ($method == 'array') {
            $is_match = is_array($given);
        } else if ($method == 'json') {
            $is_match = json_decode($given) !== null;
        }
        $is_match = $should_inverse ? !$is_match : $is_match;
    } else if (is_callable($expectation)) {
        $is_match = $expectation($given);
    }

    if (!$is_match) {
        $field_name = $given_name === '' ? 'input' : $given_name;
        $expectation_name = is_callable($expectation) ? '(customized predicator)' : $expectation;
        throw new Exception("given $field_name, ".var_export($given, true).", does not match expectation  ".$expectation_name."  .");
    }
}
function not_empty($val) { return !empty($val); }
function map($arr, $callback, $is_pair_para=true) {
    $res = array();
    foreach ($arr as $k => $v) {
        $res[$k] = ($is_pair_para)
                    ? $callback($k,$v)
                    : $callback($v)
                    ;
    }
    return $res;
}
function filter($arr, $predicator, $is_pair_para=true) {
    $res = array();
    foreach ($arr as $k => $v) {
        $is_match = ($is_pair_para)
            ? $predicator($k,$v)
            : $predicator($v)
        ;
        if (!$is_match) {
            continue;
        }
        $res[$k] = $v;
    }
    return $res;
}
function pickup_prefix($haystack, $prefix) {
    $matched_keys = preg_grep('/^'.$prefix.'/', array_keys($haystack));
    $key_val_filpped = array_flip($matched_keys);
    return array_intersect_key($haystack, $key_val_filpped);
}
/* data manipulation */

/* template */
$HEADER_EXTRA = '';
$FOOTER_EXTRA = '';
$MODERNIZR = '';
function add_extra_header($template_name) {
    global $HEADER_EXTRA;
    $HEADER_EXTRA = $template_name;
}
function add_extra_footer($template_name) {
    global $FOOTER_EXTRA;
    $FOOTER_EXTRA = $template_name;
}
function add_modernizr($template_name) {
    global $MODERNIZR;
    $MODERNIZR = $template_name;
}
function amend_nojs_controller_name($controller_name) {
    $needle = '_nojs';
    $pos = strrpos($controller_name, $needle);
    return ($pos === false)
                ? $controller_name
                : substr_replace($controller_name, '', $pos, strlen($needle));
}
function serialize_vars_as_js($global_var_for_js) {
    $vars = array();
    foreach ($global_var_for_js as $k => $v) {
        if (gettype($v) == "integer" ) {
            $vars[] = "$k = $v";
        } else {
            $vars[] = "$k = '$v'";
        }
    }
    $vars = '<script type="text/javascript">var '.join(',', $vars).';</script>';
    return $vars;
}
function render_back_link($target=array("path"=>"index.php", "text"=>"Home"), $is_return=false) {
    $s = array();
    $s[] = '<ul class="nav nav-tabs nav-stacked">';
    $s[] = '<li><a href="'.$target['path'].'">';
    $s[] = '<i class="icon-circle-arrow-left"></i>';
    $s[] = '&nbsp;'.$target['text'].'</a>';
    $s[] = '</li></ul>';
    $s = implode('', $s);
    if (!$is_return) {
        echo $s;
        return true;
    } else {
        return $s;
    }
}
/* template */

/* security */
function is_csrf_request() {
    $expected_uri = WEB_ROOT;
    $comparing_max_pos = strlen($expected_uri);
    $r = (strncmp(@$_SERVER['HTTP_REFERER'], $expected_uri, $comparing_max_pos));
    error_log('ERROR: CSRF found');
    return $r;
}
function is_login() {
    return (isset($_SESSION['ID']) && trim($_SESSION['ID']) != '');
}
function purify($input, $methods) {
    // skip non-string input
    if (gettype($input) != 'string') {
        $type = gettype($input);
        de("invalid  purify input type [$type] (should be string).", $backtrace_depth=2);
        return false;                                               // exit
    }

    $purifying = $input;
    $methods = explode('|', $methods);
    foreach ($methods as $method) {
        if ($method == 'html') {
            $purifying = htmlspecialchars($purifying, ENT_QUOTES);
        } else if ($method == 'url') {
            $purifying = rawurlencode($purifying);
        } else if ($method == 'urldecode') {
            $purifying = rawurldecode($purifying);
        } else if ($method == 'sql') {
            $purifying = addslashes($purifying);
        } else if ($method == 'eol') {
            $purifying = preg_replace(array('/%0d/','/%0a/','/\\r/','/\\n/'),
                                      array('','','',''),
                                      $purifying);
        } else if ($method == 'null') {
            $purifying = str_replace("\0", '', $purifying);
        } else {
            de("invalid purify method [$method]. (rollback all)");
            return false;                                           // exit
        }
    }
    $purified = $purifying;
    return $purified;
}
function purify_values($dict, $methods) {
    $purifieds = array();
    foreach ($dict as $k => $v) {
        $purifieds[$k] = purify($v, $methods);
    }
    return $purifieds;
}
function mysql_escape_mimic($inp) {
    // http://php.net/manual/en/function.mysql-real-escape-string.php
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}
/* security */

/* debug */
function toggle_min_script($fpath) {
    $norm_path = $fpath;
    if (!DEV_MODE) {
        $info = pathinfo($fpath);
        $norm_path = $info['dirname'].'/'.$info['filename'].'.min.'.$info['extension'];
    }
    return $norm_path;
}
function plainify($obj) {
    //var_dump(gettype($obj));
    if (is_string($obj)) {
        $obj = htmlspecialchars($obj);
    } else if (is_bool($obj)) {
        $obj =    ($obj) ? 'true' : 'false';
    } else if (is_array($obj)) {
        foreach ($obj as $k => $v) {
            $obj[$k] = plainify($v);
        }
    } else {
        // $obj = $obj;
    }
    return $obj;
}
// NOTE:
// - use [] instead of "" in de() message
function de($arr, $backtrace_depth=1) {
    if (!DEV_MODE) {return; }

    //$arr = plainify(&$arr);
    $msg = array();
    $msg[] = "<div class='debug'><code>";
    $msg[] = get_caller($backtrace_depth);
    $msg[] = '<br/>';
    $var_name = get_var_name($arr);
    $msg[] = ($var_name !== false) ? $var_name.' ' : '';
    $msg[] = nl2br(var_export($arr, true));
    $msg[] = "</code></div>";
    echo join('', $msg);
}
function get_caller($backtrace_depth) {
    $trace = debug_backtrace();
    for($i=0; $i<$backtrace_depth; $i++) {
        array_shift($trace);
    }
    $caller_file_info = array_shift($trace);
    $caller_info = array_shift($trace);
    $parent_caller =    (empty($caller_info['function']))
                        ? ''
                        :"{$caller_info['function']}()";
    $msg = "$parent_caller { ... {$caller_file_info['function']}() ...} ".
            "at +{$caller_file_info['line']} {$caller_file_info['file']}";
    return $msg;
}
function get_var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if ($value === $var) {
            return $var_name;
        }
    }
    return false;
}
function bde($arr) {
    //if (!DEV_MODE) {return; }
    error_log(var_export($arr, true));
}
/* debug */

function is_utf8($str){
    $i = 0;
    $len = strlen($str);

    for($i = 0; $i < $len; $i++){
        $sbit = ord(substr($str,$i,1));
        if ($sbit < 128){
        } else if($sbit > 191 && $sbit < 224){
            $i++;
        } else if($sbit > 223 && $sbit < 240){
            $i+=2;
        } else if($sbit > 239 && $sbit < 248){
            $i+=3;
        } else{
            return 0;
        }
    }
    return 1;
}
function str_to_Hex($string) {
    $hex = array();
    for ($i=0; $i < strlen($string); $i++) {
        $hex[] .= $string[$i].'='.dechex(ord($string[$i]));
    }
    return join(' ', $hex);
}