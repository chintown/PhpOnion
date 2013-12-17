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
- error handling
*/

$cwd = dirname(__FILE__).'/';
require $cwd . 'BaseNode.php';
require $cwd . 'Response.php';
require $cwd . '../common/RestUtil.php';
define('BASE_NODE_REPO', $cwd.'../node_common/');
define('BUSINESS_NODE_REPO', FOLDER_ROOT.'/node_business/');

$services = load_services_manifest($cwd);
$entry = parse_target_entry_name($_GET['target'], $services)
                or die("Error: invalid routing entry: [".$_GET['target']."]");
list($main_chain_nodes, $sub_chain_nodes) = load_services_nodes($services, $entry);
$chain_nodes = array_merge($main_chain_nodes, $sub_chain_nodes);
$node_paths = find_node_paths($chain_nodes, $error) or die($error);
$node_instance = init_node_instances($node_paths, $error) or die($error);

// prepare req/res and invoke chain
$req = null;
$res = new Response();
$node_instance[0]->execute($req, $res);

// -----------------------------------------------------------------------------

function load_services_manifest($cwd) {
    require $cwd . '../lib/spyc/spyc.php';
    $conf = spyc_load_file(FOLDER_ROOT . 'config/services.yaml');
    return $conf;
}

function parse_target_entry_name($route_path, $services) {
    $parts = explode('/', $route_path); // check .htaccess
    $entry = $parts[0];
    if ($entry === '') {
        return false;
    } else if (!isset($services[$entry])) {
        return false;
    } else {
        return $entry;
    }
}

function load_services_nodes($services, $entry) {
    $main_chain_nodes = (isset($services[$entry.'_chain']))
        ? $services[$entry.'_chain']
        : $services['default_chain'];
    $sub_chain_nodes = $services[$entry];
    return array($main_chain_nodes, $sub_chain_nodes);
}

function find_node_paths($node_names, &$error) {
    $node_paths = array();
    foreach ($node_names as $name) {
        $node_file = BASE_NODE_REPO . $name . '.php';
        if (file_exists($node_file)) {
            $node_paths[$name] = $node_file;
        } else {
            $node_file = BUSINESS_NODE_REPO . $name . '.php';
            if (file_exists($node_file)) {
                $node_paths[$name] = $node_file;
            } else {
                $error = "Error: node ($name) not found. $node_file";
                return false;
            }
        }
    }
    return $node_paths;
}

function init_node_instances($node_paths, &$error) {
    $prev_instance = null;
    $instances = array();
    foreach ($node_paths as $name => $path) {
        require $path;
        $instance = new $name;
        if (isset($prev_instance)) {
            $prev_instance->setNext($instance);
        }
        $instances[] = $instance;
        $prev_instance = $instance;
    }
    return $instances;
}