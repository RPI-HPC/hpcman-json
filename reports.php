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

switch($_GET['q']){
  case 'projects_missing_parent':
    $sql="select projname from projects WHERE projects.projid NOT IN (SELECT projid from project_parents)";
    $res = _pg_query($sql);
    $row = pg_fetch_row($res);

    $projects = array();

    while ($row = pg_fetch_assoc($res)) {
      array_push($projects, $row['projname']);
    }

    echo json_encode($projects);
    break;
  case 'master_project_counts':
    $sql="
select 
  root_projects.projname,
  master_projects.projname,
  count(projects.projname) as count,
  count(case when projects.projsector='A' then 1 else NULL end) as acad_cnt,
  count(case when projects.projsector='C' then 1 else NULL end) as comm_cnt,
  count(case when projects.projsector='G' then 1 else NULL end) as govt_cnt
from
  project_parents as master_project_parents,
  projects as root_projects,
  projects as master_projects
left join
  project_parents on project_parents.projparentid = master_projects.projid
left join
  projects on projects.projid = project_parents.projid
where
  master_project_parents.projparentid=root_projects.projid AND
  master_projects.projid = master_project_parents.projid AND
  root_projects.projname='users'
group by
  root_projects.projname,
  master_projects.projname
  ;
    ";
    $res = _pg_query($sql);

    $projects = array('cols' => array(
                       array('type'=>'string','label'=>'Master Project'),
                       array('type'=>'number','label'=>'Total Count'),
                       array('type'=>'number','label'=>'Academic'),
                       array('type'=>'number','label'=>'Commercial'),
                       array('type'=>'number','label'=>'Government')
                       ),
                       'rows' => array()
                     );

    while ($row = pg_fetch_assoc($res)) {
      array_push($projects['rows'], array($row['projname'],
                                          intval($row['count']),
                                          intval($row['acad_cnt']),
                                          intval($row['comm_cnt']),
                                          intval($row['govt_cnt'])
                                         ));
    }

    echo json_encode($projects);
    break; 
  }
?>
