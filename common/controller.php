<?php
    /*
    - load config
    - include and initiate node
    - start chain

    0. set up request object
    1. decide chain
    2. find node
    3. invoke chain
    4. call entry of node
    5. set up response content
    6. output response

    - checking hooks on nodes
    - erro handling
    */

    // load service configuration
    $cwd = dirname(__FILE__).'/';
    require $cwd.'../lib/spyc.php';
    $conf = spyc_load_file($cwd.'../config/services.yaml');

    // load base class
    require $cwd.'BaseNode.php';
    $parts = explode('/', $_GET['target']); // check .htaccess
    define('SUB_CHAIN', $parts[0]);
    if (SUB_CHAIN === '') {
        return;
    }

    // extract sub config
    $conf_main = (isset($conf[SUB_CHAIN.'_chain']))
                    ? $conf[SUB_CHAIN.'_chain']
                    : $conf['default_chain'];
    $conf_sub_chain = $conf[SUB_CHAIN];

    // initiate chain
    $default_instances = init_node_instances($conf_main);
    if (count($default_instances) === 0) {
        die("Error can not initiate default chain. ".var_export($conf_main));
    }
    $default_head = $default_instances[0];
    $default_tail = $default_instances[count($default_instances) - 1];

    $sub_chain_instances = init_node_instances($conf_sub_chain);
    if (count($sub_chain_instances) === 0) {
        die("Error can not initiate sub_chain chain. ".var_export($conf_sub_chain));
    }
    $sub_chain_head = $sub_chain_instances[0];

    $default_tail->setNext($sub_chain_head);

    // prepare req/res
    require $cwd."../common/RestUtil.php";
    require $cwd."../common/Response.php";

    $req = null;
    $res = new Response();

    // invoke chain
    $default_head->execute($req, $res);


    function init_node_instances($names) {
        global $cwd;
        $prev_name= null;
        $prev_instance = null;
        $instances = array();
        foreach ($names as $name) {
            // echo "start init $name \n";
            $node_file = $cwd.'../node_common/'.$name.'.php';
            if (file_exists($node_file)) {
                include $node_file;
            } else {
                $node_file = $cwd.'../node_business/'.$name.'.php';
                if (file_exists($node_file)) {
                    include $node_file;
                } else {
                    die("Error node ($name) not found. $node_file");
                }
            }
            $instance = new $name;
            if (isset($prev_instance)) {
                $prev_instance->setNext($instance);
            }
            // echo "after meet $name we update $prev_name \n";var_dump($prev_instance);
            $instances[] = $instance;

            $prev_name = $name;
            $prev_instance = $instance;
        }
        return $instances;
    }