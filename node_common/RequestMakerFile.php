<?php

require_once 'common/image.php';

/*
 * parse information of uploaded file
 */
class RequestMakerFile extends BaseNode {
    var $expected_field_name = 'user_uploaded';
    var $UPLOAD_ERR = array(
        'UPLOAD_ERR_OK', 'UPLOAD_ERR_INI_SIZE', 'UPLOAD_ERR_FORM_SIZE',
        'UPLOAD_ERR_PARTIAL', 'UPLOAD_ERR_NO_FILE', 'UPLOAD_ERR_NO_TMP_DIR',
        'UPLOAD_ERR_CANT_WRITE', 'UPLOAD_ERR_EXTENSION'
    );
    // deprecated. use image.php
    // http://php.net/manual/en/function.exif-imagetype.php
    var $IMAGE_TYPE = array(
        1 => "gif", 2 => "jpeg",    3 => "png", 4 => "swf", 5 => "psd", 6 => "bmp",
        7 => "tiff_ii", 8 => "tiff_mm", 9 => "jpc", 10=> "jp2", 11=> "jpx",
        12=> "jb2", 13=> "swc", 14=> "iff",     15=> "wbmp",    16=> "xbm", 17=> "ico"
    );
    function __construct() {

    }
    public function execute(&$req, &$res) {

        $file_info = array();
        if (!empty($_FILES)) {
            if (isset($_FILES[$this->expected_field_name])) {
                $file_info = $_FILES[$this->expected_field_name];
                $ext = strtolower(end(explode(".", $file_info["name"])));
                $file_info['_extension'] = $ext;
                $file_info['_upload_msg'] = $this->UPLOAD_ERR[$file_info["error"]];
                if ($file_info['_upload_msg'] === 'UPLOAD_ERR_OK') {
                    $file_info['_image_type'] = get_image_type_name($file_info['tmp_name']);
                }
            } else {
                $res->addLog(array(
                    'msg_file_upload'=> 'you need to use valid field name.'
                ));
            }
        }

        $req->setFile($file_info);
        $this->next($req, $res);
    }

    // deprecated. use image.php
    function getImageTypeName($file) {
        $type_code = exif_imagetype($file);
        $type_name = (isset($this->IMAGE_TYPE[$type_code]))
            ? $this->IMAGE_TYPE[$type_code]
            : 'N/A'
        ;
        return $type_name;
    }
}