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

function init_chart_data($title, $columns)
{
  return array('cols'=>$columns, 'rows'=>array(), 'title'=>$title);
}

# return JSON encoded chart data with rows populated
# accepts SQL that will give 2 columns (label, count)
function encode_default($chart_data, $sql)
{
   $res = _pg_query($sql);
   while($row = pg_fetch_row($res)) {
    $tmp = array($row[0],
                 floatval($row[1])
                 );
    array_push($chart_data['rows'], $tmp);
  }

  return json_encode($chart_data);
}

# return json encoded chart data with rows populated
# accepts 2 SQL statements
function encode_two_opposing_slices($chart_data, $sql1, $label1, $sql2, $label2) {
  $res = _pg_query($sql1);
  $row = pg_fetch_row($res);
  array_push($chart_data['rows'], array($label1,floatval($row[0])));

  $res = _pg_query($sql2);
  $row = pg_fetch_row($res);
  array_push($chart_data['rows'], array($label2,floatval($row[0])));

  return json_encode($chart_data);
}

switch($_GET['q']){
  case 'accounts_by_state':
    $columns = array(array('type'=>'string','label'=>'State'), array('type'=>'number','label'=>'Accounts'));
    $chart_data = init_chart_data('Accounts by State', $columns);

    $sql = "select useraccountstatedesc,count(*) from user_accounts,user_accounts_states where user_accounts.useraccountstate=user_accounts_states.useraccountstate group by useraccountstatedesc;";
    echo encode_default($chart_data, $sql);
    break;
  case 'accounts_created_12mo':
    $columns = array(array('type'=>'string','label'=>'Month'), array('type'=>'number','label'=>'Accounts'));
    $chart_data = init_chart_data('Accounts Created - Last 12 Months', $columns);

    # must supply timestamp of query or there is a race condition at the end of a month
    $sql="select
            extract(epoch from now()),
            count(CASE WHEN created <= NOW() and created > NOW()-interval '1 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '1 month' and created > NOW() - interval '2 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '2 month' and created > NOW() - interval '3 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '3 month' and created > NOW() - interval '4 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '4 month' and created > NOW() - interval '5 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '5 month' and created > NOW() - interval '6 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '6 month' and created > NOW() - interval '7 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '7 month' and created > NOW() - interval '8 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '8 month' and created > NOW() - interval '9 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '9 month' and created > NOW() - interval '10 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '10 month' and created > NOW() - interval '11 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '11 month' and created > NOW() - interval '12 month' then 1 else NULL END)
            from user_accounts;";
    $res = _pg_query($sql);
    $row = pg_fetch_row($res);

    for ($i=12;$i>=1;$i--) {
      $t = strtotime("now - ".($i-1)." months", $row[0]);
      $t_str = strftime('%b',$t);
      array_push($chart_data['rows'], array($t_str,intval($row[$i])));
    }

    echo json_encode($chart_data);
    break;
  case 'principals_by_state':
    $columns = array(array('type'=>'string','label'=>'State'), array('type'=>'number','label'=>'Principals'));
    $chart_data = init_chart_data('Principals by State', $columns);

    $sql = "select principalstatename,count(*) from principals,principalstates where principals.principalstate=principalstates.principalstate group by principalstatename;";
    echo encode_default($chart_data, $sql);
    break;
  case 'principals_approved_12mo':
    $columns = array(array('type'=>'string','label'=>'Month'), array('type'=>'number','label'=>'Principals'));
    $chart_data = init_chart_data('Principals Approved - Last 12 Months', $columns);

    # must supply timestamp of query or there is a race condition at the end of a month
    $sql="select
            extract(epoch from now()),
            count(CASE WHEN created <= NOW() and created > NOW()-interval '1 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '1 month' and created > NOW() - interval '2 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '2 month' and created > NOW() - interval '3 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '3 month' and created > NOW() - interval '4 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '4 month' and created > NOW() - interval '5 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '5 month' and created > NOW() - interval '6 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '6 month' and created > NOW() - interval '7 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '7 month' and created > NOW() - interval '8 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '8 month' and created > NOW() - interval '9 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '9 month' and created > NOW() - interval '10 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '10 month' and created > NOW() - interval '11 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '11 month' and created > NOW() - interval '12 month' then 1 else NULL END)
            from principals where principalstate='A';";
    $res = _pg_query($sql);
    $row = pg_fetch_row($res);

    for ($i=12;$i>=1;$i--) {
      $t = strtotime("now - ".($i-1)." months", $row[0]);
      $t_str = strftime('%b',$t);
      array_push($chart_data['rows'], array($t_str,intval($row[$i])));
    }

    echo json_encode($chart_data);
    break;
  case 'principals_by_email':
    $columns = array(array('type'=>'string','label'=>'Email'), array('type'=>'number','label'=>'Principals'));
    $chart_data = init_chart_data('Principals by Email', $columns);

    $sql = "select 
              count(*),
              count(case when emailaddress like '%@cs.rpi.edu' then 1 else NULL END),
              count(case when emailaddress like '%@scorec.rpi.edu' then 1 else NULL END),
              count(case when emailaddress like '%@rpi.edu' then 1 else NULL END),
              count(case when emailaddress like '%@%ibm.com' then 1 else NULL END),
              count(case when emailaddress like '%@%nyu.edu' then 1 else NULL END),
              count(case when emailaddress like '%@%cornell.edu' then 1 else NULL END),
              count(case when emailaddress like '%@%buffalo.edu' then 1 else NULL END),
              count(case when emailaddress like '%@%columbia.edu' then 1 else NULL END)
              from principals";

    $labels = array('RPI CS','RPI SCOREC','Other RPI','IBM','NYU','Cornell','Buffalo','Columbia');

    $res = _pg_query($sql);
    $row = pg_fetch_row($res);
    $total = intval($row[0]);
    foreach ($labels as $k => $v) {
      $total -= intval($row[($k+1)]);
      array_push($chart_data['rows'], array($v,intval($row[($k+1)])));
    }

    array_push($chart_data['rows'], array('Other',intval($total)));

    echo json_encode($chart_data);
    
    break;
  case 'principals_by_contactinfo_present':
    $columns = array(array('type'=>'string','label'=>'Contact Info'), array('type'=>'number','label'=>'Principals'));
    $chart_data = init_chart_data('Principals with Contact Info', $columns);

    $sql1 = "select count(*) from principals where contactinfo='' OR contactinfo IS NULL;";
    $sql2 = "select count(*) from principals where contactinfo!='' AND contactinfo IS NOT NULL;";

    echo encode_two_opposing_slices($chart_data, $sql1, 'Missing', $sql2, 'Present');
    break;
  case 'projects_by_sector':
    $columns = array(array('type'=>'string','label'=>'Sector'), array('type'=>'number','label'=>'Projects'));
    $chart_data = init_chart_data('Projects by Sector', $columns);

    $sql = "select projectsectordesc,count(*) from projects,projects_sectors where projects.projsector=projects_sectors.projectsector group by projectsectordesc;";
    echo encode_default($chart_data, $sql);
    break;
  case 'projects_created_12mo':
    $columns = array(array('type'=>'string','label'=>'Month'), array('type'=>'number','label'=>'Projects'));
    $chart_data = init_chart_data('Projects Created - Last 12 Months', $columns);

    # must supply timestamp of query or there is a race condition at the end of a month
    $sql="select
            extract(epoch from now()),
            count(CASE WHEN created <= NOW() and created > NOW()-interval '1 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '1 month' and created > NOW() - interval '2 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '2 month' and created > NOW() - interval '3 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '3 month' and created > NOW() - interval '4 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '4 month' and created > NOW() - interval '5 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '5 month' and created > NOW() - interval '6 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '6 month' and created > NOW() - interval '7 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '7 month' and created > NOW() - interval '8 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '8 month' and created > NOW() - interval '9 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '9 month' and created > NOW() - interval '10 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '10 month' and created > NOW() - interval '11 month' then 1 else NULL END),
            count(CASE WHEN created <= NOW() - interval '11 month' and created > NOW() - interval '12 month' then 1 else NULL END)
            from projects";
    $res = _pg_query($sql);
    $row = pg_fetch_row($res);

    for ($i=12;$i>=1;$i--) {
      $t = strtotime("now - ".($i-1)." months", $row[0]);
      $t_str = strftime('%b',$t);
      array_push($chart_data['rows'], array($t_str,intval($row[$i])));
    }

    echo json_encode($chart_data);
    break;
  case 'projects_by_parent':
    $columns = array(array('type'=>'string','label'=>'Parent'), array('type'=>'number','label'=>'Projects'));
    $chart_data = init_chart_data('Projects by Parent', $columns);
    $sql = 'select projname,COUNT(*) from projects,project_parents where projects.projid=project_parents.projparentid and projname!=\'users\' group by projname;';
    echo encode_default($chart_data, $sql);
    break;
  case 'root_project_shares':
    $columns = array(array('type'=>'string','label'=>'Project'), array('type'=>'number','label'=>'Share'));
    $chart_data = init_chart_data('Root Project Shares', $columns);
    $sql="SELECT projects.projname, project_parents.parentshare FROM project_parents,projects as parents,projects where parents.projid=project_parents.projparentid and projects.projid=project_parents.projid AND parents.projname='users';";
    echo encode_default($chart_data, $sql);
    break;
  }
?>
