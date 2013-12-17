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