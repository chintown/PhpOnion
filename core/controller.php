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
require $cwd . '../lib/spyc/spyc.php';
require $cwd . 'Router.php';
require $cwd . 'Request.php';
require $cwd . 'BaseNode.php';
require $cwd . 'Response.php';
require $cwd . '../common/RestUtil.php';
define('BASE_NODE_REPO', $cwd.'../node_common/');
define('BUSINESS_NODE_REPO', FOLDER_ROOT.'/node_business/');

// prepare req/res and invoke chain
$req = new Request();
$res = new Response();

$services = load_services_manifest();
$router = new Router(load_routing_manifest());
$request_uri = preg_replace('@'.SITE_CODE.'/@', '', $_SERVER['REQUEST_URI']);
$request_uri_tmp = explode('?', $request_uri);
$request_uri = array_shift($request_uri_tmp);
////$request_uri = $_GET['target']; // deprecated
$entry = $router->parse($request_uri, $rest_path_params)
            or die("Error: invalid routing entry: [".$request_uri."]");
validate_target_entry($entry, $services)
            or die("Error: invalid service entry: [".$request_uri."]");
list($main_chain_nodes, $sub_chain_nodes) = load_services_nodes($services, $entry);
$chain_nodes = array_merge($main_chain_nodes, $sub_chain_nodes);
$node_paths = find_node_paths($chain_nodes, $error) or die($error);
$node_instance = init_node_instances($node_paths, $error) or die($error);

$req->setParams($rest_path_params);
$res->addChainLog($chain_nodes);
$node_instance[0]->execute($req, $res);
// -----------------------------------------------------------------------------

function load_routing_manifest() {
    return spyc_load_file(FOLDER_ROOT . 'config/routes.yaml');
}

function load_services_manifest() {
    return spyc_load_file(FOLDER_ROOT . 'config/services.yaml');
}

function validate_target_entry($entry, $services) {
    if (empty($entry)) {
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