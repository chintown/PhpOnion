<?php
    function get_auth_hash($raw) {
        // can not support random salt
        //$method = '$2a$07$'; // Blowfish
        $method = '$5$rounds=5000$'; // SHA-256
        $salt = $method.AUTH_SALT.'$';
        $crypted = crypt($raw, $salt);
        //de($salt);
        //de($crypted);
        return $crypted;
    }

    // ----- ----- ----- ----- ----- -----
    function validate($raw, $crypted_w_meta) {
        $info = parse_crypted_w_meta($crypted_w_meta);
        $raw_crypted = get_crypted($raw, $info['salt']);
        //de("RAW:____________________ ".$raw);
        //de("RAW_CRYPTED (MAYBE):____ ".$raw_crypted);
        //de("RAW_CRYPTED_META :______ ".get_crypted_w_meta($raw, $info['salt']));
        //de("CRYPTED_W_META:_________ ".$crypted_w_meta);
        //de("CRYPTED (TRUTH):________ ".$info['crypted']);
        //de("SALT:___________________ ".$info['salt']);
        return ($raw_crypted == $info['crypted']);
    }

    // ----- ----- ----- ----- ----- -----
    function get_random_salt() {
        $salt = array();
        for ($i = 0; $i < 22; $i++) {
            $valid_chars = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $salt[] = substr($valid_chars, mt_rand(0, strlen($valid_chars)-1), 1);
        }
        $salt = join('', $salt);
        if (DEV_MODE) {error_log('RANDOM SALT: '.$salt);}
        return $salt;
    }
    function get_crypted($raw, $salt=null) {
        $method = '2a'; // Blowfish
        $strength = "08";
        $salt = (isset($salt)) ? $salt : get_random_salt(); // $salt is reference
        $std_salt = '$'.$method.'$'.$strength.'$'.$salt;
        $crypted = crypt($raw, $std_salt);
        return $crypted;
    }
    function get_crypted_w_meta($raw, $salt=null) {
        // "meta" meanings appending duplicated salt at the end of crypted result
        // for validator easily extract the salt part later
        $crypted = get_crypted($raw, &$salt); // pass reference! to get salt if is not set
        $crypted_w_meta = compose_crypted_w_meta($crypted, $salt);
        return $crypted_w_meta;
    }

    // ----- ----- ----- ----- ----- -----
    function compose_crypted_w_meta($crypted, $salt) {
        return $crypted.$salt;
    }
    function parse_crypted_w_meta($crypted_w_meta) {
        $crypted = substr($crypted_w_meta, 0, 60);
        $salt = substr($crypted_w_meta, 60);
        return array('crypted' => $crypted, 'salt' => $salt);
    }
