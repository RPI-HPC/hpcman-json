<?php
require_once("json_functions.inc.php");

if (!isset($_GET['q']))
{
  _fatal_error("Not enough parameters");
}

require_once('json_config.php');

if (!isset($hpcman_webui_path)) {
  _fatal_error("HPCman path unknown");
}

require_once($hpcman_webui_path . '/globals.inc.php');
require_once($hpcman_webui_path . '/hpcpasswd.inc.php');
require_once($hpcman_webui_path . '/functions.inc.php');

$dbh = pg_connect($_dbconnstr) or _fatal_error("Could not connect to DB.");

# CHEAT
$snuuid=1;

require_once('get-functions.php');

function _term() {
    return (isset($_GET['term'])?$_GET['term']:'');
}

$ret = "";

// Note: please keep in alpha order, except default
switch($_GET['q']) {
  case 'defaultusername-used':
    $ret = get_defaultusername_used(_term());
    break;
  case 'email-used':
    $ret = get_email_used(_term());
    break;
  case 'partial-principal-list':
    $ret = get_partial_principal_list($snuuid, _term());
    break;
  case 'partial-project-list':
    $ret = get_partial_project_list($snuuid, _term());
    break;
  case 'partial-username-list':
    $ret = get_partial_username_list($snuuid, _term());
    break;
  case 'projname-used':
    $ret = get_projname_used($snuuid, _term());
    break;
  case 'vsite-list':
    $ret = get_vsite_list($snuuid);
    break;
  default:
    $ret = "error";
}

echo json_encode($ret);

?>
