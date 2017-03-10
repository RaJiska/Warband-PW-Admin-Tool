<?php
function permissions_overview()
{
  echo("<fieldset><legend>Account Permissions</legend>");
  $count_result = mysql_query("SELECT id, name, unique_id, server_id, permission_pp, permission_bl, permission_pn, permission_ap, permission_wp, permission_lb FROM web_accounts WHERE id <> 1 LIMIT 1;");
  if (!$count_result) return;
  $field_count = mysql_num_fields($count_result);

  foreach ($_POST as $key => $value)
  {
    if (substr_compare($key, "id_", 0, 3) == 0)
    {
      $id = filter_var($key, FILTER_SANITIZE_NUMBER_INT, array("options"=>array("min_range"=>0)));
      if (!isset($id) || !is_array($value) || count($value) <= 0) continue;
      $query = "UPDATE web_accounts SET ";
      $valid = false;
      foreach ($value as $field_no)
      {
        if ($field_no[0] == 'n') $setting = '0';
        else $setting = '1';
        $field_no = filter_var($field_no, FILTER_SANITIZE_NUMBER_INT, array("options"=>array("min_range"=>0)));
        if (!isset($field_no) || $field_no >= $field_count) continue;
        $permission_name = mysql_field_name($count_result, $field_no);
        if ($valid) $query .= ", ";
        $query .= "$permission_name = '$setting'";
        $valid = true;
      }
      if (!$valid) continue;
      $query .= " WHERE id = '$id';";
      $result = mysql_query($query);
    }
  }

  $result = mysql_query("SELECT id, name, unique_id, server_id, permission_pp, permission_bl, permission_pn, permission_ap, permission_wp, permission_lb FROM web_accounts WHERE id <> 1;");
  if (!$result) return echo_database_error();
  $current_uri = htmlspecialchars($_SERVER["REQUEST_URI"]);
  echo("<form action=\"$current_uri\" method=\"post\">");
  echo('<table class="database_view"><thead><tr><th>account name</th><th>unique id</th><th>server</th>');
  for ($i = pw_name_server_config::punishments_manager_first_field_no; $i < $field_count; ++$i)
  {
    $real_p_name = mysql_field_name($count_result, $i);
    if ($real_p_name == "permission_pp"){$real_p_name = "punishments";}elseif($real_p_name == "permission_bl"){$real_p_name = "ban list";}elseif($real_p_name == "permission_pn"){$real_p_name = "player names";}elseif($real_p_name == "permission_ap"){$real_p_name = "admin permissions";}elseif($real_p_name == "permission_wp"){$real_p_name = "web permissions";}elseif($real_p_name == "permission_lb"){$real_p_name = "log ban";}
    echo("<th>$real_p_name</th>");
  }
  echo("</tr></thead>\n<tbody>");

  while ($row = mysql_fetch_row($result))
  {
    $id = $row[0];
    $account_name = $row[1];
    $account_uid = $row[2];
    $server_id = $row[3];
    $srv_id = mysql_fetch_array(mysql_query("SELECT name FROM warband_servers WHERE id = '$server_id';"));
    $id_name = "id_$id"."[]";
    $admin_name = '';
    $name_result = mysql_query("SELECT name FROM player_names WHERE unique_id = '$unique_id' ORDER BY last_used_time DESC LIMIT 1;");
    if ($name_result && $name_row = mysql_fetch_assoc($name_result)) $admin_name = htmlspecialchars($name_row["name"]);
    echo("<tr><td>$account_name</td>");
    echo("<td>$account_uid</td>");
    echo("<td>$srv_id[name]</td>");
    for ($i = pw_name_server_config::punishments_manager_first_field_no; $i < $field_count; ++$i)
    {
      if ($row[$i] != 0)
      {
        $set = ' class="set"';
        $n = 'n';
      }
      else
      {
        $set = '';
        $n = '';
      }
      echo("<td$set><input type=\"checkbox\" name=\"$id_name\" value=\"$n$i\"/></td>");
    }
    echo("</tr>\n");
  }
  echo("</tbody></table>\n");
  echo('<div class="database_actions"><input type="submit" name="apply" value="Apply changes"/></div>');
  echo("</form>\n");
  echo("</fieldset>");
}

function show_web_settings()
{
  if ($_POST['submit_butt_create'])
  {
    if ($_POST['acc_name_create'] && $_POST['acc_pass_create'] && $_POST['acc_uid_create'] && $_POST['srv_id'])
    {
      if (is_numeric($_POST['acc_uid_create']))
      {
        $account_name = mysql_real_escape_string(htmlspecialchars($_POST['acc_name_create']));
        $account_pass = mysql_real_escape_string(htmlspecialchars($_POST['acc_pass_create']));
        $account_uid = mysql_real_escape_string(htmlspecialchars($_POST['acc_uid_create']));
        $account_srv_id = mysql_real_escape_string(htmlspecialchars($_POST['srv_id']));
        $row_name = mysql_fetch_array(mysql_query("SELECT name FROM web_accounts WHERE name = '$account_name';"));
        $row_uid = mysql_fetch_array(mysql_query("SELECT unique_id FROM web_accounts WHERE unique_id = '$account_uid';"));
        if ($row_name['name'])
        {
          mysql_query("UPDATE web_accounts SET passwd = SHA1('$account_pass') WHERE name = '$account_name';");
          echo '<span style="color:#088A08;text-align:center;">Account already exists. Password updated.</span>';
        }
        elseif ($row_uid['unique_id'])
        {
          mysql_query("UPDATE web_accounts SET passwd = SHA1('$account_pass') WHERE unique_id = '$account_uid';");
          echo '<span style="color:#088A08;text-align:center;">Account already exists. Password updated.</span>';
        }
        else
        {
          mysql_query("INSERT INTO web_accounts (name, passwd, unique_id, server_id) VALUES ('$account_name', SHA1('$account_pass'), '$account_uid', '$account_srv_id');");
        }
      }
      else { echo '<span style="color:#FF0000;text-align:center;">ERROR: Account UID must be a numeric value.</span>'; }
    }
    else { echo '<span style="color:#FF0000;text-align:center;">ERROR: One or several fields were left empty.</span>'; }
  }
  elseif ($_POST['submit_butt_del'])
  {
    if ($_POST['acc_name_del'])
    {
      $account_name = htmlspecialchars($_POST['acc_name_del']);
      $row_name = mysql_fetch_array(mysql_query("SELECT name FROM web_accounts WHERE name = '$account_name';"));
      if ($row_name['name'])
      {
        if ($account_name != "root")
        {
          mysql_query("DELETE FROM web_accounts WHERE name = '$account_name';");
        }
        else { echo '<span style="color:#FF0000;text-align:center;">ERROR: You cannot delete root account.</span>'; }
      }
      else { echo '<span style="color:#FF0000;text-align:center;">ERROR: Account not found.</span>'; }
    }
    else { echo '<span style="color:#FF0000;text-align:center;">ERROR: The account name field was left empty.</span>'; }
  }
  elseif ($_POST['submit_butt_msg'])
  {
    if ($_POST['msg_welcome'])
    {
      $welcome_msg = mysql_real_escape_string(htmlspecialchars($_POST['msg_welcome']));
      mysql_query("UPDATE welcome_messages SET welcome_message = '$welcome_msg' WHERE server_id = '$_SESSION[server_id]';");
    }
    if ($_POST['msg_banned'])
    {
      $banned_msg = mysql_real_escape_string(htmlspecialchars($_POST['msg_banned']));
      mysql_query("UPDATE welcome_messages SET banned_message = '$banned_msg' WHERE server_id = '$_SESSION[server_id]';");
    }
    if (!$_POST['msg_welcome'] && !$_POST['msg_banned'])
    {
      echo '<span style="color:#FF0000;text-align:center;">ERROR: Both message fields were left empty.</span>';
    }
  }
  elseif ($_POST['submit_butt_srv_create'])
  {
    if ($_POST['srv_add_name'] && $_POST['srv_add_passwd'])
    {
      $server_name = mysql_real_escape_string(htmlspecialchars($_POST['srv_add_name']));
      $server_passwd = mysql_real_escape_string(htmlspecialchars($_POST['srv_add_passwd']));
      $row = mysql_fetch_array(mysql_query("SELECT name FROM warband_servers WHERE password = '$server_passwd';"));
      if (!$row['name'])
      {
        mysql_query("INSERT INTO warband_servers (name, password) VALUES ('$server_name', SHA1('$server_passwd'));");
        mysql_query("INSERT INTO welcome_messages (welcome_message, banned_message, server_id) VALUES ('Welcome on the server.', 'You are banned from this server.', '1');");        
        echo '<span style="color:#088A08;text-align:center;">Server successfully created.</span>';
      }
      else { echo '<span style="color:#FF0000;text-align:center;">ERROR: Password already exists.</span>'; }
    }
    else { echo '<span style="color:#FF0000;text-align:center;">ERROR: A field has been left empty.</span>'; }
  }
  elseif ($_POST['submit_butt_srv_delete'])
  {
    if ($_POST['srv_id'])
    {
      $server_id = mysql_real_escape_string(htmlspecialchars($_POST['srv_id']));
      mysql_query("DELETE FROM warband_servers WHERE id = '$server_id';");
      echo '<span style="color:#088A08;text-align:center;">Server successfully deleted.</span>';
    }
    else { echo '<span style="color:#FF0000;text-align:center;">ERROR: What are you trying to do here eh ?!</span>'; }
  }
  permissions_overview();

  $row = mysql_fetch_array(mysql_query("SELECT * FROM welcome_messages;"));
  echo("<fieldset><legend>Server Welcomes Messages</legend>");
  echo("<form action=\"?page=web_settings\" method='post'>");
  echo('<center><label id="web_settings">Welcome Message: </label><input type="text" autocomplete="off" style="width: 65%;" name="msg_welcome" placeholder="' . $row[welcome_message] .'" /><br>');
  echo('<label id="web_settings">Banned Message: </label><input type="text" autocomplete="off" style="width: 65%" name="msg_banned" placeholder="' . $row[banned_message] .'" /><br><br>');
  echo("<input type=\"submit\" name=\"submit_butt_msg\" value=\"Save Messages\" /><br>");
  echo("</center></fieldset>");

  echo("<fieldset><legend>Create Account</legend>");
  echo("<form action=\"?page=web_settings\" method='post'>");
  echo("<center><label id=\"web_settings\">Account Name: </label><input type=\"text\" style=\"width: 20%;\" autocomplete=\"off\" name=\"acc_name_create\"  /><br>");
  echo("<label id=\"web_settings\">Account UID: </label><input type=\"text\" style=\"width: 20%;\" autocomplete=\"off\" name=\"acc_uid_create\"  /><br>");
  echo("<label id=\"web_settings\">Account Pass: </label><input type=\"password\" style=\"width: 20%;\" name=\"acc_pass_create\" /><br>");
  echo("<label id=\"web_settings\">Server Name: </label><select style=\"width: 20%;\" name=\"srv_id\">");
  $result = mysql_query("SELECT id, name FROM warband_servers;");
  while ($row = mysql_fetch_assoc($result)) echo("<option value=\"$row[id]\">$row[name]</option>");
  echo("</select><br><br>");
  echo("<input type=\"submit\" name=\"submit_butt_create\" value=\"Create Account\" /><br>");
  echo("</center></fieldset></form>");

  echo("<fieldset><legend>Delete Account</legend>");
  echo("<form action=\"?page=web_settings\" method='post'>");
  echo("<center><label id=\"web_settings\">Account Name: </label><select name=\"acc_name_del\" style=\"width: 20%;\">");//<input type=\"text\" style=\"width: 20%\" autocomplete=\"off\" name=\"acc_name_del\"  /><br><br>");
  $result = mysql_query("SELECT id, name FROM web_accounts WHERE name <> 'root';");
  while ($row = mysql_fetch_assoc($result)) echo("<option value=\"$row[name]\">$row[name]</option>");
  echo("</select><br><br>");
  echo("<input type=\"submit\" name=\"submit_butt_del\" value=\"Delete Account\" /><br>");
  echo("</center></fieldset></form>");

  echo("<fieldset><legend>Add Server</legend>");
  echo("<form action=\"?page=web_settings\" method='post'>");
  echo("<center><label id=\"web_settings\">Server Name: </label><input type=\"text\" style=\"width: 20%;\" autocomplete=\"off\" name=\"srv_add_name\"><br>");
  echo("<label id=\"web_settings\">Server Pass: </label><input type=\"password\" style=\"width: 20%;\" autocomplete=\"off\" name=\"srv_add_passwd\"><br><br>");
  echo("<input type=\"submit\" name=\"submit_butt_srv_create\" value=\"Create Server \" /><br>");
  echo("</center></form></fieldset>");

  echo("<fieldset><legend>Delete Server</legend>");
  echo("<form action=\"?page=web_settings\" method='post'>");
  echo("<center><label id=\"web_settings\">Server Name: </label><select style=\"width: 20%;\" name=\"srv_id\">");
  $result = mysql_query("SELECT id, name FROM warband_servers;");
  while ($row = mysql_fetch_assoc($result)) echo("<option value=\"$row[id]\">$row[name]</option>");
  echo("</select><br>");
  echo("<input type=\"submit\" name=\"submit_butt_srv_delete\" value=\"Delete Server \" /><br>");
  echo("</center></form></fieldset>");
}
?>
