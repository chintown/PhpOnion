<?php

require_once 'common/process.php';
require_once 'common/util.php';

/**
 * need to
 * 1. setup IMG_REPO_ROOT with server ownership/permission
 * 2. enable GD library or ImageMagick
 */
class RestSourceImage extends BaseNode {
    function __construct() {
    }
    public function execute(&$req, &$res) {
        switch ($req->getVerb()) {
            case 'GET':
                $params = $req->getParams();
                $w = $params['thumb_w'];
                $h = $params['thumb_h'];

                $path_in = IMG_REPO_ROOT . '/raw/' . $params['filename'];
                if (empty($params['filename'])) {
                    $res->setStatus(400); // bad request
                    $res->addLog('given empty image');
                    return;
                } else if (!file_exists($path_in)) {
                    $res->setStatus(400); // bad request
                    $res->addLog('image not found: ' . $path_in);
                    return;
                }
                if ($w === 0 && $h === 0) {
                    // no need to make thumbnail
                    $path_output = $path_in;
                    $path_out = '';
                } else {
                    if  ($w === 0) {
                        $w = $h;
                    } else if  ($h === 0) {
                        $h = $w;
                    }
                    $path_out = $this->getThumbFilename(IMG_REPO_ROOT . '/thumb/' . $params['filename'], "${w}x${h}");
                    $process_info = gen_thumbnail($path_in, $path_out, $w, $h, 'GD');
                    $res->addLog($process_info);
                    if (!$process_info['success']) {
                        $path_output = null;
                        $res->setStatus(500); // bad request
                        $res->addLog('can not resize image: ' . $path_in);
                        return;
                    } else {
                        $path_output = $path_out;
                    }
                }
                $res->addLog(array(
                    'path_in'=> $path_in,
                    'path_out'=> $path_out,

                ));
                $res->setFormat('image');
                $res->setResult(array('path'=> $path_output));
                $res->setContentType('image/' . get_image_type_name($path_output));
                break;
            case 'POST':
                $file_info = $req->getFile();
                $result = array('success'=> true, 'path'=> '', 'msg'=>'',
                                'file_info'=> $file_info, 'user'=> get_process_user());

                // check
                // 1. write to temp 2. allowed type 3. size 4. actual type
                $valid_info = validate_uploaded_image($file_info);
                if (!$valid_info['valid']) {
                    $result['success'] = false;
                    $result['msg'] = $valid_info['msg'];
                    $res->setStatus(400); // bad request
                    $res->addLog($result);
                    return;
                }

                // purify: clone a image without exif meta
                $valid_info = purify_image($file_info['tmp_name'], $file_info);
                if (!$valid_info['success']) {
                    $result['success'] = false;
                    $result['msg'] = $valid_info['msg'];
                    $res->setStatus(400); // bad request
                    $res->addLog($result);
                    return;
                }

                // save: normalize the given name as file name
                $valid_info = $this->saveFile($file_info);
                if (!$valid_info['success']) {
                    $result['success'] = false;
                    $result['msg'] = $valid_info['msg'];
                    $res->setStatus(400); // bad request
                    $res->addLog($result);
                    return;
                } else {
                    $result['path'] = $valid_info['path'];
                }

                $res->setResult(array(
                    'success'=> true,
                    'path'=> $result['path']
                ));
                $res->addLog($result);
                break;
            case 'PUT':
            case 'DELETE':
            default:
                break;
        }
    }

    protected function getDestinationFileName($raw) {
        return normalize_filename($raw);
    }

    private function saveFile($file_info) {
        $return = array('success'=> true, 'msg'=> '', 'path'=> '');

        $path_repo = $this->getRepoPath();
        $fn_upload = $this->getDestinationFileName($file_info['name']);
        $path_upload = $path_repo . '/' . $fn_upload;
        if (!@move_uploaded_file($file_info['tmp_name'], $path_upload)) {
            $return['success'] = false;
            $return['msg'] = 'can not move file. ' . $file_info['tmp_name'] . ' -> ' . $path_upload;
        } else {
            $return['path'] = WEB_ROOT . '/image/' . $fn_upload;
        }
        return $return;
    }
    private function getRepoPath() {
        $path_repo = IMG_REPO_ROOT . '/raw';
        if (!file_exists($path_repo)) {
            mkdir($path_repo, 0777, true);
        }
        return $path_repo;
    }
    private function getThumbFilename($filename, $size) {
        $parts = explode('.', $filename);
        $ext = array_pop($parts);
        $pre = implode('.', $parts);
        return "$pre.$size.$ext";
    }
}