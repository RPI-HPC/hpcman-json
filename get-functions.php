<?php
  function get_partial_username_list($snuuid, $term) {
    $sql = "SELECT username FROM user_accounts
            WHERE user_accounts.snuuid=$1
            AND lower(user_accounts.username) LIKE $2";

    $res = _pg_query_params($sql, array($snuuid, '%'.(strtolower($term)).'%'));
    $ret = array();

    while($row = pg_fetch_assoc($res)) {
      $ret[] = array('id' => $row['username'],
                   'label' => $row['username'],
                   'value' => $row['username']
                   );
    }

    return $ret;
  }

  function get_partial_principal_list($snuuid, $term) {
    $sql = "SELECT name, defaultusername FROM principals, user_accounts
            WHERE principals.puuid=user_accounts.puuid
            AND user_accounts.snuuid=$1";
    $sql .= "AND lower(principals.name) LIKE $2";
    $sql .= " GROUP BY defaultusername, name";

    $res = _pg_query_params($sql, array($snuuid, '%'.(strtolower($term)).'%'));
    $ret = array();
  
    while($row = pg_fetch_assoc($res)) {
      $ret[] = array('id' => $row['name'],
                   'label' => $row['name'],
                   'value' => $row['name']
                   );
    }

    return $ret;
  }

  function get_partial_project_list($snuuid, $term) {
    $sql = "SELECT projname FROM projects WHERE snuuid=$1";
    $sql .= "AND lower(projname) LIKE $2";

    $res = _pg_query_params($sql, array($snuuid, '%'.(strtolower($term)).'%'));
    $ret = array();

    while($row = pg_fetch_assoc($res)) {
      $ret[] = array('id' => $row['projname'],
                   'label' => $row['projname'],
                   'value' => $row['projname']
                   );
    }

    return $ret;
  }

  function get_vsite_list($snuuid) {
    $sql = "SELECT vsname, vsid FROM virtual_sites WHERE snuuid=$1";

    $res = _pg_query_params($sql, array($snuuid));
    $ret = array();

    while($row = pg_fetch_assoc($res)) {
      $ret[] = array('vsid' => $row['vsid'], 'vsname' => $row['vsname']);
    }

    return $ret;
  }

  function get_email_used($email) {
    $sql = "SELECT emailaddress FROM principals WHERE lower(emailaddress)=$1";

    $res = _pg_query_params($sql, array((strtolower($email))));
    $ret = array();

    if (pg_num_rows($res) > 0) {
      $ret['used'] = true;
    } else {
      $ret['used'] = false;
    }

    return $ret;
  }

  function get_defaultusername_used($defaultusername) {
    $sql = "SELECT defaultusername FROM principals WHERE lower(defaultusername)=$1";

    $res = _pg_query_params($sql, array((strtolower($defaultusername))));
    $ret = array();
    
    if (pg_num_rows($res) > 0) {
      $ret['used'] = true;
    } else {
      $ret['used'] = false;
    }

    return $ret;
  }

  function get_projname_used($snuuid, $projname) {
    $sql = "SELECT projname FROM projects WHERE projects.snuuid=$1 AND lower(projname)=$2";

    $res = _pg_query_params($sql, array($snuuid, (strtolower($projname))));
    $ret = array();
    
    if (pg_num_rows($res) > 0) {
      $ret['used'] = true;
    } else {
      $ret['used'] = false;
    }

    return $ret;
  }
?>
