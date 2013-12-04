<?php
    function readpath($dir, $level, $last, &$dirs, &$files){
        // recursively parsing the directory until $last depth
        // storing directory entries into $dirs, file entries into $files
        //print $dir." (DIR)<br/>\n";
        $dp = opendir($dir);
        while (false !== ($file=readdir($dp)) && $level <= $last){
            if ($file == "." || $file == "..") {continue;}

            if (is_dir($dir."/".$file)) {
                readpath($dir."/".$file, $level+1, $last, $dirs, $files);
                $dirs[] = "$dir/$file";
            }
            else{
                $files[] = "$dir/$file";
            }
        }
    }
    
    function bottom_up_path_by($fn, $level) {
        $path = realpath($fn);
        $info = pathinfo($path);
        
        $result_path = array($info['basename']);
        $remain_path = $info['dirname'];
        
        for ($i=0; $i < ($level-1); $i++) {
            
            $curr_info = pathinfo($remain_path);
            $curr_dir  = $curr_info['dirname'];
            $curr_base  = $curr_info['basename'];
            array_unshift($result_path, $curr_base);
            if ($remain_path == '/') break;
            $remain_path = $curr_dir;
        }
        
        $result_path = join('/', $result_path);
        return $result_path;
    }
    // echo bottom_up_path_by('static/css/index.css', 10); // test
    
    function check_n_mkdir($path, $perm=0644) {
        $info = pathinfo($path);
        if (file_exists($info['dirname'])) {return;}
        
        mkdir($info['dirname'], $perm);
    }