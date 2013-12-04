<?php

function get_image_type_name($file) {
    $IMAGE_TYPE = array(
        1 => "gif", 2 => "jpeg",    3 => "png", 4 => "swf", 5 => "psd", 6 => "bmp",
        7 => "tiff_ii", 8 => "tiff_mm", 9 => "jpc", 10=> "jp2", 11=> "jpx",
        12=> "jb2", 13=> "swc", 14=> "iff",     15=> "wbmp",    16=> "xbm", 17=> "ico"
    );
    $type_code = exif_imagetype($file);
    $type_name = (isset($IMAGE_TYPE[$type_code]))
        ? $IMAGE_TYPE[$type_code]
        : 'N/A'
    ;
    return $type_name;
}

function imagecreatefrom_by_type($path_image) {
    $type = get_image_type_name($path_image);
    return call_user_func('imagecreatefrom'.$type, $path_image);
}
function image_by_type($type, $resource, $path_image) {
    call_user_func_array('image'.$type, array($resource, $path_image));
}
function purify_image($path_image, $file_info) {
    $return = array('success'=> true, 'msg'=> '');
    try {
        switch ($file_info['_image_type']) {
            case 'jpeg':
            case 'png':
            case 'gif':
                $res = imagecreatefrom_by_type($path_image);
                image_by_type($file_info['_image_type'], $res, $path_image);
                break;
            default:
                break;
        }
    } catch (Exception $e) {
        $return['success'] = false;
        $return['msg'] = $e->getMessage();
    }
    return $return;
}

/*
 * 1. if destination size is smaller
 *    keep ratio, and resize until one edge fit
 * 2. larger
 *    use original size
*/
function gen_thumbnail($path_in, $path_out, $maxW, $maxH, $method='ImageMagick') {
    $return = array('success'=> true, 'msg'=> '', 'output_w'=> $maxW, 'output_h'=> $maxH);

    $type = get_image_type_name($path_in);
    $src = imagecreatefrom_by_type($path_in);
    // get the source image's width and height
    $src_w = imagesx($src);
    $src_h = imagesy($src);

    $new_size = fit_size($src_w, $src_h, $maxW, $maxH);
    $thumb_w = $new_size['width'];
    $thumb_h = $new_size['height'];

//    $thumb_w = $maxW;
//    $thumb_h = $maxH;

    $return['output_w'] = $thumb_w;
    $return['output_h'] = $thumb_h;

    if ('GD' == $method) {
        $thumb = imagecreatetruecolor($thumb_w, $thumb_h);
        // start resize
        imagecopyresized($thumb, $src, 0, 0, 0, 0, $thumb_w, $thumb_h, $src_w, $src_h);
        // save thumbnail
        image_by_type($type, $thumb, $path_out);
    } else if ('ImageMagick' == $method) {
        $exports = array(
            'PATH'=> '$PATH:/opt/ImageMagick/bin/'
        );
        $exec_info = execute_external(
            '/opt/ImageMagick/bin/convert', array("-resize {$thumb_w}x{$thumb_h} '$path_in' '$path_out'"),
            '', $exports, '.'
        );
        if ($exec_info['code'] !== 0) {
            $return = array('success'=> false, 'msg'=> $exec_info['out']);
            return $return;
        }

//        $cmd = "convert -strip -interlace plane '$path_out' '$path_out'";
//        shell_exec($cmd);
//        $cmd = "convert -resize {$thumb_w}x{$thumb_h} '$path_in' '$path_out' 2>&1";
//        shell_exec($cmd);
    }
    return $return;
}
function fit_size($origW, $origH, $maxW, $maxH) {
    // resize the given dimension into $maxW x $maxH rectangle
    // but keep the original ratio of dimension
    $newH = $origH;
    $newW = $origW;
    $landscape = ($origW > $origH) ? true : false;

    if ($landscape) {
        if ($origW > $maxW) {
            $newW = $maxW;
            $newH = resizeHByW($origH, $maxW, $origW);
        }
    } else {
        // portrait
        if ($origH > $maxH) {
            $newH = $maxH;
            $newW = resizeWByH($origW, $maxH, $origH);
        }
    }
    return array('width' => round($newW), 'height' => round($newH));
}
function resizeHByW($origH, $newW, $origW) {
    return $origH * ($newW / $origW);
}
function resizeWByH($origW, $newH, $origH) {
    return $origW * ($newH / $origH);
}