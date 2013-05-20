<?php

function _fatal_error($msg)
{
  error_log("HPCman: " . $msg);
  exit(1);
}

function _warn($msg)
{
  error_log('HPCman: ' . $msg);
}

function __pg_check_result($res)
{
  if (!$res) {
    _fatal_error("Bad query");
  }

  if(pg_num_rows($res) < 0) {
    _warn("No results for query.");
  }
}

function _pg_query($sql)
{
  $res = pg_query($sql);
  __pg_check_result($res);
  return $res;
}

function _pg_query_params($sql, $params)
{
  $res = pg_query_params($sql, $params);
  __pg_check_result($res);
  return $res;
}

function json_done($status, $details = '', $data = '')
{ 
  $output = array('status' => $status, 'details' => $details, 'data' => $data);
  echo json_encode($output);
  exit($status);
}

?>
