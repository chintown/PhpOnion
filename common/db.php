<?
    // NEED path.php
    require('common/crypt.php');
    require('config/pw.php');

    // TODO: use mysqli
    function dbq($sql, $return=false) {
        if ($return) return $sql;
        $link = mysql_connect('',AUTH_USER,AUTH_PASS);
        $response = db_check('mysql_connect', $link);
        if ($response !== DB_VALID) { return $response; }

        mysql_query( "SET NAMES utf8" );
        $is_ok = mysql_select_db(AUTH_DB, $link);
        $response = db_check('mysql_select_db', $is_ok, $link);
        if ($response !== DB_VALID) { return $response; }

        $resource = mysql_query($sql, $link);
        $response = db_check('mysql_query', $resource, $link);
        if ($response !== DB_VALID && DEV_MODE) {
            if (function_exists('de')) {
                de($sql);
            } else {
                echo $sql;
            }
        }
        if (DEV_MODE) {
            error_log('[INFO]: '.$sql);
        }
        return (isSelect($sql)) ? $resource : $link;
        //return $resource;
    }
    function isSelect($sql) {
        return (strpos(strtolower(trim($sql)), 'select') === 0);
    }

    function db2val($resource, $attr) {
        $arr = db2arr($resource);
        if (count($arr) > 0) {
            return $arr[0][$attr];
        }else {
            return false;
        }
    }
    function db2arr($resource) {
        $result = array();
        for ($i = 0; ($tuple = mysql_fetch_array($resource)) != NULL; $i++) {
            $result[] = $tuple;
        }
        for ($i = 0; $i < count($result); $i++) {
            for ($j = 0; $j < count($result[$i]); $j++) {
                unset($result[$i][$j]);
            }
        }
        return $result;
    }
    function db2arr2($resource) {
        $result = array();
        for ($i = 0; ($tuple = mysql_fetch_array($resource)) != NULL; $i++) {
            foreach( $tuple as $key => $val)    {
                if (!isset($result[$key])) { $result[$key] = array(); }
                $result[$key][] = $val;
            }
        }
        for ($i = 0; $i < count($result); $i++) {
            unset($result[$i]);
        }
        return $result;
    }
    function db2arr3($resource, $attr) {
        $result = array();
        $str_eval = '$result = array(';
        $tempArr = db2arr($resource);
        for ($i = 0; $i <count($tempArr) ; $i++) {
            $str_eval .= "\$tempArr[$i]['$attr'] => \$tempArr[$i],";
        }
        $str_eval = trim($str_eval, ",") . ');';
        eval($str_eval);
        foreach ($result as $k => &$v) {
            unset($v[$attr]);
        }
        return $result;
    }

    function _db_auth($user, $pass) {
        // can not support random salt
        $link = mysql_connect(DB_HOST,AUTH_USER,AUTH_PASS);
        $response = db_check('mysql_connect', $link);
        if ($response !== DB_VALID) { return $response; }

        mysql_query( "SET NAMES utf8" );
        $is_ok = mysql_select_db(AUTH_DB, $link);
        $response = db_check('mysql_select_db', $is_ok, $link);
        if ($response !== DB_VALID) { return $response; }

        $safe_user = addslashes($user);
        $crypted_pass = get_auth_hash($pass);
        $safe_pass = addslashes($crypted_pass);

        $query = "SELECT COUNT(".AUTH_FIELD_USER.") as num FROM `".AUTH_TABLE."` ".
                    " WHERE ".AUTH_FIELD_USER." = '$safe_user'".
                    " AND ".AUTH_FIELD_PASS." = '$safe_pass'";
        //de($query);
        $resource = mysql_query($query, $link);
        $response = db_check('mysql_query', $resource, $link);
        if ($response !== DB_VALID) { return $response; }
        mysql_close($link);

        $num = null;
        for ($i=0; ($tuple=mysql_fetch_array($resource)) != NULL; $i++) {
            $num = intval($tuple['num']);
        }
        return ($num === 1) ? DB_VALID : DB_INVALID;
    }
    function db_auth($id, $pass) {
        // can support random salt
        $link = mysql_connect(DB_HOST,AUTH_USER,AUTH_PASS);
        $response = db_check('mysql_connect', $link);
        if ($response !== DB_VALID) { return $response; }

        mysql_query( "SET NAMES utf8" );
        $is_ok = mysql_select_db(AUTH_DB, $link);
        $response = db_check('mysql_select_db', $is_ok, $link);
        if ($response !== DB_VALID) { return $response; }

        $safe_id = addslashes($id);

        $query = "SELECT ".AUTH_FIELD_PASS." as crypted_w_meta FROM `".AUTH_TABLE."` ".
                    " WHERE ".AUTH_FIELD_USER." = '$safe_id'"; // numeric value is not allowed for comparison
        if (DEV_MODE) {error_log('[INFO] DB_AUTH: '.$query);}
        $resource = mysql_query($query, $link);
        $response = db_check('mysql_query', $resource, $link);
        mysql_close($link);
        if ($response !== DB_VALID) { return $response; }

        $crypted_w_meta = null;
        for ($i=0; ($tuple=mysql_fetch_array($resource)) != NULL; $i++) {
            $crypted_w_meta = $tuple['crypted_w_meta'];
        }

        // KEY POINT
        return (validate($pass, $crypted_w_meta)) ? DB_VALID : DB_INVALID;
    }
    function db_auth_update($id, $pass) {
        $link = mysql_connect(DB_HOST,AUTH_USER,AUTH_PASS);
        $response = db_check('mysql_connect', $link);
        if ($response !== DB_VALID) { return $response; }

        mysql_query( "SET NAMES utf8" );
        $is_ok = mysql_select_db(AUTH_DB, $link);
        $response = db_check('mysql_select_db', $is_ok, $link);
        if ($response !== DB_VALID) { return $response; }

        $safe_id = addslashes($id);
        //$crypted_pass = get_auth_hash($pass); // can not support random salt
        // KEY POINT
        $crypted_pass = get_crypted_w_meta($pass); // can support random salt
        $safe_pass = addslashes($crypted_pass);

        $query = "UPDATE  `".AUTH_TABLE."` AS a
                    SET  `".AUTH_FIELD_PASS."` =  '$safe_pass'
                    WHERE  a.`".AUTH_FIELD_USER."` =  '$safe_id'";
        if (DEV_MODE) {error_log('[INFO] DB_AUTH_UPDATE: '.$query);}

        $resource = mysql_query($query, $link);
        $response = db_check('mysql_query', $resource, $link);
        mysql_close($link);
        if ($response !== DB_VALID) { return $response; }

        return DB_VALID;
    }
    function db_check($what, $ok, $dblink=null) {
        if (!$ok) {
            if (DEV_MODE) {
                $error_msg = ($dblink) ? mysql_error($dblink) : '';
                $msg = "Error: $error_msg while $what.";
                if (function_exists('de')) {
                    de($msg);
                } else {
                    echo $msg;
                }
            } else {
                header('Location: static/error/500.html');
            }
            return DB_ERROR;
        } else {
            return DB_VALID;
        }
    }
    function db_sel($tables, $cols, $conds, $return=false) {
        $qs = array();
        $qs[] = "SELECT $cols";
        $qs[] = "FROM $tables";
        $qs[] = "WHERE $conds";
        $qs = implode(' ', $qs);
        if (!$return) {
            return dbq($qs);
        } else {
            return $qs;
        }
    }
    function db_ins($table, $pairs, $return=false) {
        $qs = array();
        $qs[] = "INSERT INTO `$table`";
        $keys = array();
        $vals = array();
        foreach ($pairs as $key => $val) {
            $keys[] = "`$key`";
            $vals[] = db_val($val);
        }
        $qs[] = "(".implode(', ', $keys).")";
        $qs[] = "VALUES (".implode(', ', $vals).")";
        $qs = implode(' ', $qs);
        if (!$return) {
            return dbq($qs);
        } else {
            return $qs;
        }
    }
    function db_upd($table, $sets, $conds, $return=false) {
        $qs = array();
        $qs[] = "UPDATE `$table`";
        $qs[] = "SET $sets";
        $qs[] = "WHERE $conds";
        $qs = implode(' ', $qs);
        if (!$return) {
            return dbq($qs);
        } else {
            return $qs;
        }
    }
    function db_del($table, $conds, $return=false) {
        $qs = array();
        $qs[] ="DELETE FROM `$table`";
        $qs[] = "WHERE $conds";
        $qs = implode(' ', $qs);
        if (!$return) {
            return dbq($qs);
        } else {
            return $qs;
        }
    }
    function from($table, $alias='') {
        $from = array("`$table`");
        $from[] = (!empty($alias)) ? "AS $alias" : '';
        return implode(' ', $from);
    }
    function froms() {
        $args = func_get_args();
        $args = (count($args) === 1 && is_array($args[0])) ? $args[0] : $args;
        return implode(', ', $args);
    }
    function col($seletor, $alias='') {
        $col = array("$seletor");
        $col[] = (!empty($alias)) ? "AS $alias" : '';
        return implode(' ', $col);
    }
    function cols() {
        $args = func_get_args();
        $args = (count($args) === 1 && is_array($args[0])) ? $args[0] : $args;
        return implode(', ', $args);
    }
    function set($key, $val) {
        return cond($key, $val);
    }
    function sets() {
        $args = func_get_args();
        $args = (count($args) === 1 && is_array($args[0])) ? $args[0] : $args;
        return implode(', ', $args);
    }
    function cond($key, $val, $op='=') {
        return "$key $op ".db_val($val);
    }
    function conds() {
        $args = func_get_args();
        $args = (count($args) === 1 && is_array($args[0])) ? $args[0] : $args;
        return implode(' ', $args);
    }
    function db_val($val) {
        if (is_int($val)) {
            return "$val";
        } else if (strtolower($val) === 'null') {
            return "NULL";
        } else {
            return "'".purify($val, 'sql')."'";
        }
    }
