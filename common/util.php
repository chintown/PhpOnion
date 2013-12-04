<?php
    function validate_uploaded_file($file_info, $allowed_exts) {
        $msg = '';
        if ($file_info["error"] > 0) {
            $msg = $file_info["_upload_msg"];
        } else if (!array($file_info['_extension'], $allowed_exts)){
            $msg = 'invalid file type.';
        } else if ($file_info["size"] > MAX_UPLOADED_IMAGE_BYTE) {
            $msg = 'please use smaller file. ('. MAX_UPLOADED_IMAGE_BYTE / 1000 .' KB)';
        } else {
            // valid!
        }
        $valid = ($msg === '') ? true : false;
        return array(
            'valid'=> $valid,
            'msg'=> $msg
        );
    }
    function validate_uploaded_image($file_info) {
        $allowed_exts = array("jpg", "jpeg", "gif", "png");
        $return =  validate_uploaded_file($file_info, $allowed_exts);

        if ($return['valid']) {
            $msg = '';
            $ext = ($file_info['_extension'] === 'jpg') ? 'jpeg' : $file_info['_extension'];
            if ($ext !== $file_info['_image_type']) {
                $msg = 'unmatched content type. [' . $ext . '] [' . $file_info['_image_type'] . ']';
            } else {
                // valid;
            }
            $valid = ($msg === '') ? true : false;
            $return = array(
                'valid'=> $valid,
                'msg'=> $msg
            );
        }
        return $return;
    }

    function execute_pyflow($script, $arguments, $extra='', $exports=array()) {
        // export HOME=/Users/chintown;
        // PYTHONPATH=/Users/chintown/src/python/PyCharmer/src
        // /usr/bin/python
        // "/Users/chintown/src/python/epub/workflow/db_to_epub.py"
        // "50df26849b82090538b3c760" "50df26849b82090538b3c760.5jr0clqchh9h0310vppf49o7a0.epub"
        // 2>&1
        $working_dir = HOME."/src/python/epub/";

        $script = $working_dir . '/' . $script;
        array_unshift($arguments, $script);
        $executable = exec('which python');
        $exports = array(
            'HOME'=> HOME,
            'PATH'=> '$PATH:/usr/local/bin/',
            'PYTHONPATH'=> HOME.'/src/python/PyCharmer/src'
        );
        return execute_external($executable, $arguments, $extra, $exports, $working_dir);
    }
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
    function normalize_filename($text, $prefix_prevent_empty='fn_') {
        // (space) ~!@#$%^&*=\;'/()[]{}<>`+|:"?
        if ($prefix_prevent_empty === 'fn_') {
            $prefix_prevent_empty .= ''.time();
        }
        $text = preg_replace('/[ ~!@#$%\^&*=\\\\;\'\/(){}<>\\[\\]`+|:"]/', '_', $text);
        if (preg_replace('/_/', '', $text) === '') {
            $text = $prefix_prevent_empty . $text;
        }
        return $text;
    }
    //print_r(normalize_filename('0~1!2@3#4$5%6^7&8*9=0'));
    //print_r(normalize_filename('a\\b;c\'d/e(f)g[h]i{j}k<l>m`n+o|p:q"rstuvwxyx'));
    //print_r(normalize_filename('!@#$@#%#^#$%^'));
    //print_r(normalize_filename('5/5/5'));

    function get_process_user() {
        $info = posix_getpwuid(posix_getuid());
        return $info['name'];
    }
