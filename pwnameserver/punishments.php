<?php
function getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

function check_player_name_actions()
{
  if (!filter_has_var(INPUT_POST, "ids")) return;
  $ids = $_POST["ids"];
  if (!is_array($ids)) return;
  $remove_names = filter_has_var(INPUT_POST, "remove_names");
  $set_permissions = filter_has_var(INPUT_POST, "set_permissions");
  foreach ($ids as $id)
  {
    $id = filter_var($id, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0)));
    if ($remove_names)
    {
      $result = mysql_query("DELETE FROM past_punishments WHERE id = '$id';");
      if (!$result) return echo_database_error();
    }
  }
  if ($set_permissions)
  {
    echo('<script type="text/javascript">window.location = "?page=admin_permissions"</script>');
  }
}

function show_past_punishments()
{
  check_player_name_actions();

  $current_uri_no_start = htmlspecialchars(preg_replace('/&start=[^=&]*/', '', $_SERVER["REQUEST_URI"]));
  echo("<form action=\"$current_uri_no_start\" method=\"get\"><div class=\"database_actions\">");
  echo('<input type="hidden" name="page" value="player_names"/>');
  if (isset($order_by)) echo("<input type=\"hidden\" name=\"order_by\" value=\"$order_by\"/>");
  echo('<label for="filter_text">Filter by:</label>');
  echo('<input type="text" name="filter" id="filter_text"/>');
  echo('<input type="submit" name="by_uid" value="unique id"/>');
  echo('<input type="submit" name="by_name" value="name"/>');
  echo("</div></form>\n");

  $query = "SELECT * FROM past_punishments";
  if (isset($_GET["by_uid"]))
  {
    $filter_unique_id = filter_input(INPUT_GET, "filter", FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1, "max_range"=>100000000)));
    if ($filter_unique_id) $query .= " WHERE p_uid = '$filter_unique_id' AND server_id = '$_SESSION[server_id]'"; else $query .= " WHERE server_id = '$_SESSION[server_id]'";
  }
  else if (isset($_GET["by_name"]))
  {
    $filter_name = filter_input(INPUT_GET, "filter", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
    if ($filter_name)
    {
      $filter_name = mysql_real_escape_string($filter_name);
      $query .= " WHERE p_name LIKE '%$filter_name%' AND server_id = '$_SESSION[server_id]'";
    }
    else
    {
      $query .= " WHERE server_id = '$_SESSION[server_id]'";
    }
  }
  else $query .= " WHERE server_id = '$_SESSION[server_id]'";


  $desc = "";
  if (isset($_GET["order_by"]))
  {
    $order_words = explode("_", $_GET["order_by"]);
    $order_by = $order_words[0];
    if ($order_by == "uid") $query .= " ORDER BY p_uid";
    else if ($order_by == "name") $query .= " ORDER BY p_name";
    else if ($order_by == "punishment") $query .= " ORDER BY punishment";
    else if ($order_by == "aname") $query .= " ORDER BY a_name";
    else if ($order_by == "auid") $query .= " ORDER BY a_uid";
    else if ($order_by == "date") $query .= " ORDER BY ban_time";
    if (count($order_words) <= 1)
    {
      $desc = "_desc";
    }
    else
    {
      $query .= " DESC";
    }
  }
  $current_uri_no_order = htmlspecialchars(preg_replace('/&(order_by|start)=[^=&]*/', '', $_SERVER["REQUEST_URI"]));

  $filter_start = filter_input(INPUT_GET, "start", FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0)));
  if ($filter_start)
  {
    $query .= " LIMIT $filter_start, " . pw_name_server_config::player_names_per_page;
  }
  else
  {
    $query .= " LIMIT " . pw_name_server_config::player_names_per_page;
  }

  $current_uri = htmlspecialchars($_SERVER["REQUEST_URI"]);

  $query .= ";";
  $result = mysql_query($query);
  if (!$result) return echo_database_error();

  $page_links = '<div class="database_actions">';
  if ($filter_start && $filter_start > 0)
  {
    $prev_page_start = max($filter_start - pw_name_server_config::player_names_per_page, 0);
    $page_links .= "<a href=\"$current_uri_no_start&amp;start=$prev_page_start\">Previous page</a>&nbsp;";
  }
  if (mysql_num_rows($result) >= pw_name_server_config::player_names_per_page)
  {
    $next_page_start = $filter_start + pw_name_server_config::player_names_per_page;
    $page_links .= "<a href=\"$current_uri_no_start&amp;start=$next_page_start\">Next page</a>&nbsp;";
  }
  $page_links .= '</div>';
  echo($page_links);

  echo("<form action=\"$current_uri\" method=\"post\">");
  echo('<table class="database_view"><thead><tr><th/>');
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=uid$desc\">unique id</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=name$desc\">name</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=punishment$desc\">punishment</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=aname$desc\">invoker</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=auid$desc\">invoker uid</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=date$desc\">time</a></th>");
  echo('</tr></thead><tbody>');
  while ($row = mysql_fetch_assoc($result))
  {
    $cb_id = "cb_$row[id]";
    echo("<tr><td><input type=\"checkbox\" name=\"ids[]\" value=\"$row[id]\" id=\"$cb_id\"/></td>");

    echo("<td><label for=\"$cb_id\">$row[p_uid]</label></td>");
    $player_name = htmlspecialchars($row["p_name"]);
    echo("<td><label for=\"$cb_id\">$player_name</label></td>");
    echo("<td><label for=\"$cb_id\">$row[punishment]</label></td>");
    echo("<td><label for=\"$cb_id\">$row[a_name]</label></td>");
    echo("<td><label for=\"$cb_id\">$row[a_uid]</label></td>");
    $ban_time = date('d-m-Y H:i:s', $row['ban_time']);
    echo("<td><label for=\"$cb_id\">$ban_time</label></td>");
    echo("</tr>\n");
  }
  echo("</tbody></table>\n");
  echo($page_links);
  echo('<div class="database_actions">');
  $user_ip = getUserIP();
  if ($_SESSION['account_id'] == 1) {
  	echo('<input type="submit" name="remove_names" value="Remove names"/>');
  }
  echo("</div></form>\n");
}
?>
