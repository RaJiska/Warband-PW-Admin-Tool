<?php
function show_log_ban()
{
  echo("<html><body><center><fieldset><br>");
  echo("<form action=\"?page=log_ban\" method='post'>");
  echo("Length: <select name=\"ban_time\">");
  echo("<option value=\"1h\">1 Hour</option>");
  echo("<option value=\"3h\">3 Hours</option>");
  echo("<option value=\"12h\">12 Hours</option>");
  echo("<option value=\"1d\">1 Day</option>");
  echo("<option value=\"3d\">3 Days</option>");
  echo("<option value=\"1w\">1 Week</option>");
  echo("<option value=\"2w\">2 Weeks</option>");
  echo("<option value=\"3w\">3 Weeks</option>");
  echo("<option value=\"1m\">1 Month</option>");
  echo("<option value=\"perm\">Permanent</option>");
  echo("</select>");
  echo("&nbsp&nbsp&nbspPlayer UID: <input type=\"text\" name=\"player_uid\">&nbsp&nbsp&nbsp");
  echo("<input type=\"submit\" name=\"submit_butt\" value=\"Ban Player\" /><br>");
  echo("</form>");

  $row = mysql_fetch_array(mysql_query("SELECT unique_id, name FROM web_accounts WHERE id = '$_SESSION[account_id]';"));
  date_default_timezone_set("Europe/Paris");
  $admin_name = $row['name'];
  $admin_uid = $row['unique_id'];

  if ($_POST['submit_butt'])
  {
    if (htmlspecialchars($_POST['ban_time']))
    {
      if (htmlspecialchars($_POST['player_uid']))
      {
        if (is_numeric (htmlspecialchars($_POST['player_uid'])))
        {
          $current_epoch = time();
          $ban_time = htmlspecialchars($_POST['ban_time']);
          $player_uid = htmlspecialchars($_POST['player_uid']);
  	  $search_uid = mysql_fetch_array(mysql_query("SELECT p_uid FROM ban_list WHERE p_uid = '$player_uid' AND server_id = '$_SESSION[server_id]';"));
    	  $db_player_uid = $search_uid['p_uid'];

          if ($ban_time == "1h") { $unban_epoch = strtotime("+1 hour"); $punishment_type = "Temporary Ban (1 Hour)"; }
          elseif ($ban_time == "3h") { $unban_epoch = strtotime("+3 hours"); $punishment_type = "Temporary Ban (3 Hours)"; }
          elseif ($ban_time == "12h") { $unban_epoch = strtotime("+12 hours"); $punishment_type = "Temporary Ban (12 Hours)"; }
          elseif ($ban_time == "1d") { $unban_epoch = strtotime("+1 day"); $punishment_type = "Temporary Ban (1 Day)"; }
          elseif ($ban_time == "3d") { $unban_epoch = strtotime("+3 days"); $punishment_type = "Temporary Ban (3 Days)"; }
          elseif ($ban_time == "1w") { $unban_epoch = strtotime("+1 week"); $punishment_type = "Temporary Ban (1 Week)"; }
          elseif ($ban_time == "2w") { $unban_epoch = strtotime("+2 weeks"); $punishment_type = "Temporary Ban (2 Weeks)"; }
          elseif ($ban_time == "3w") { $unban_epoch = strtotime("+3 weeks"); $punishment_type = "Temporary Ban (3 Weeks)"; }
          elseif ($ban_time == "1m") { $unban_epoch = strtotime("+1 month"); $punishment_type = "Temporary Ban (1 Month)"; }
          elseif ($ban_time == "perm") { $unban_epoch = strtotime("+2 years"); $punishment_type = "Permanent Ban"; }

          if ($db_player_uid)
          {
            mysql_query("UPDATE ban_list SET punishment_end = '$unban_epoch' WHERE p_uid = '$player_uid' AND server_id = '$_SESSION[server_id]';");
            echo '<span style="color:#088A08;text-align:center;">Unban date updated.</span>';
          }
          else
          {
            $row = mysql_fetch_array(mysql_query("SELECT name FROM player_names WHERE unique_id = '$player_uid';"));
            $db_player_name = $row['name'];
            if (!$db_player_name) $db_player_name = "NO NAME";
            mysql_query("INSERT INTO ban_list (p_uid, p_name, a_uid, a_name, punishment_begin, punishment_end, server_id) VALUES ('$player_uid', '$db_player_name', '$admin_uid', '$admin_name', '$current_epoch', '$unban_epoch', '$_SESSION[server_id]');");
            mysql_query("INSERT INTO past_punishments (p_uid, p_name, punishment, a_uid, a_name, ban_time, server_id) VALUES ('$player_uid', '$db_player_name', '$punishment_type', '$admin_uid', '$admin_name', '$current_epoch', '$_SESSION[server_id]');");
            echo '<span style="color:#088A08;text-align:center;">Player successfully banned.</span>';
          }
        }
        else
        {
          echo '<span style="color:#FF0000;text-align:center;">ERROR: Players UID must be a numeric value.</span>';
        }
      }
      else
      { 
        echo '<span style="color:#FF0000;text-align:center;">ERROR: Field player UID is empty.</span>';
      }
    }
    else
    {
      echo '<span style="color:#FF0000;text-align:center;">ERROR: Invalid ban type.</span>';
    }
    echo("</center></body></html></fieldset>");
  }
}
?>
