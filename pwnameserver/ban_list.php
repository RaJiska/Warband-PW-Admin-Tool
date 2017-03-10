<?php
function check_player_name_actions()
{
  date_default_timezone_set("Europe/Paris");
  if (!filter_has_var(INPUT_POST, "ids")) return;
  $ids = $_POST["ids"];
  if (!is_array($ids)) return;
  $remove_names = filter_has_var(INPUT_POST, "remove_names");
  foreach ($ids as $id)
  {
    $id = filter_var($id, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0)));
    $search_player = mysql_fetch_array(mysql_query("SELECT * FROM ban_list WHERE id = '$id' AND server_id = '$_SESSION[server_id]';"));
    if ($remove_names)
    {
      if ($search_player['id'])
      {
        $ip = $_SERVER["REMOTE_ADDR"];
        $admin_name = $_SESSION['account_name'];

        $db_player_uid = $search_player['p_uid'];
        $db_player_name = $search_player['p_name'];
        $db_admin_name = $search_player['a_name'];

        date_default_timezone_set("Europe/Paris");
        $current_date = date("d-m-Y H:i:s");
        $file = "private/unban.log";
        $fh = fopen($file, 'a+');
        $stringData = $current_date . ": '" . $db_player_name . "' (" . $db_player_uid . ") has been unbanned by '$admin_name' ($ip).\n";
        fwrite($fh, $stringData);
        fclose($fh);

        $result = mysql_query("DELETE FROM ban_list WHERE id = '$id' AND server_id = '$_SESSION[server_id]';");
        if (!$result) return echo_database_error();
      }
    }
  }
}

function show_ban_list()
{
  check_player_name_actions();
  date_default_timezone_set("Europe/Paris");

  $current_uri_no_start = htmlspecialchars(preg_replace('/&start=[^=&]*/', '', $_SERVER["REQUEST_URI"]));

  echo("<form action=\"$current_uri_no_start\" method=\"get\"><div class=\"database_actions\">");
  echo('<input type="hidden" name="page" value="ban_list"/>');
  if (isset($order_by)) echo("<input type=\"hidden\" name=\"order_by\" value=\"$order_by\"/>");
  echo('<label for="filter_text">Filter by:</label>');
  echo('<input type="text" name="filter" id="filter_text"/>');
  echo('<input type="submit" name="by_uid" value="unique id"/>');
  echo('<input type="submit" name="by_name" value="name"/>');
  echo("</div></form>\n");

  $query = "SELECT * FROM ban_list";
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
    else $query .= " WHERE server_id = '$_SESSION[server_id]'";
  } 
  else $query .= " WHERE server_id = '$_SESSION[server_id]'";

  $desc = "";
  if (isset($_GET["order_by"]))
  {
    $order_words = explode("_", $_GET["order_by"]);
    $order_by = $order_words[0];
    if ($order_by == "puid") $query .= " ORDER BY p_uid";
    else if ($order_by == "pname") $query .= " ORDER BY p_name";
    else if ($order_by == "aname") $query .= " ORDER BY a_name";
    else if ($order_by == "auid") $query .= " ORDER BY a_uid";
    else if ($order_by == "begin") $query .= " ORDER BY punishment_begin";
    else if ($order_by == "end") $query .= " ORDER BY punishment_end";
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
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=puid$desc\">unique id</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=pname$desc\">name</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=aname$desc\">invoker</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=auid$desc\">invoker uid</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=begin$desc\">ban date</a></th>");
  echo("<th><a href=\"$current_uri_no_order&amp;order_by=end$desc\">unban date</a></th>");
  echo('</tr></thead><tbody>');
  while ($row = mysql_fetch_assoc($result))
  {
    $cb_id = "cb_$row[id]";
    echo("<tr><td><input type=\"checkbox\" name=\"ids[]\" value=\"$row[id]\" id=\"$cb_id\"/></td>");

    echo("<td><label for=\"$cb_id\">$row[p_uid]</label></td>");
    $player_name = htmlspecialchars($row["p_name"]);
    echo("<td><label for=\"$cb_id\">$player_name</label></td>");
    echo("<td><label for=\"$cb_id\">$row[a_name]</label></td>");
    echo("<td><label for=\"$cb_id\">$row[a_uid]</label></td>");
    $punish_begin_epoch = htmlspecialchars($row["punishment_begin"]);
    $punish_begin = date('d-m-Y H:i:s', $punish_begin_epoch);
    echo("<td><label for=\"$cb_id\">$punish_begin</label></td>");
    $punish_end_epoch = htmlspecialchars($row["punishment_end"]);
    $punish_end = date('d-m-Y H:i:s', $punish_end_epoch);
    $check_perma_epoch = $punish_begin_epoch + 63072000;
    if ($punish_end_epoch >= $check_perma_epoch) {
        echo("<td><label for=\"$cb_id\">Permanant</label></td>");
    } else {
        echo("<td><label for=\"$cb_id\">$punish_end</label></td>");
    }
    echo("</tr>\n");
  }
  echo("</tbody></table>\n");
  echo($page_links);
  echo('<div class="database_actions">');
  $ip = $_SERVER["REMOTE_ADDR"];
  echo('<input type="submit" name="remove_names" value="Remove from ban list"/>');
  echo("</div></form>\n");
}

?>
