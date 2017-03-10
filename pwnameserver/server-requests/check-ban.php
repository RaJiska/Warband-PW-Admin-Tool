<?php
if (isset($_GET['uid'])) $get_player_uid = mysql_real_escape_string($_GET['uid']); else exit;
if (isset($_GET['id'])) $get_player_id = mysql_real_escape_string($_GET['id']); else exit;
if (isset($_GET['name'])) $get_player_name = mysql_real_escape_string($_GET['name']); else exit;
if (isset($_GET['pwd'])) $get_passwd = mysql_real_escape_string($_GET['pwd']); else exit;

require("../private/config.php");

$config = new pw_name_server_config();
if (!$config->connect_database()) exit;

$row_srv_id = mysql_fetch_array(mysql_query("SELECT id FROM warband_servers WHERE password = SHA1('$get_passwd');"));
$server_id = $row_srv_id['id'];
$row = mysql_fetch_array(mysql_query("SELECT p_uid, a_name, a_uid, punishment_begin, punishment_end FROM ban_list WHERE p_uid = '$get_player_uid' AND server_id = '$server_id';"));
$ban_date = date('d-m-Y H:i:s', $row['punishment_begin']);
$unban_date = date('d-m-Y H:i:s', $row['punishment_end']);

$get_messages = mysql_fetch_array(mysql_query("SELECT welcome_message, banned_message FROM welcome_messages WHERE server_id = '$server_id' LIMIT 1;"));
$coded_welcome_msg = $get_messages['welcome_message'];
$coded_banned_msg = $get_messages['banned_message'];

$welcome_msg_vars = array('*uid*' => "$get_player_uid", '*name*' => "$get_player_name");
$banned_msg_vars = array('*uid*' => "$get_player_uid", '*name*' => "$get_player_name", '*invoker*' => "$row[a_name]", '*invoker_uid*' => "$row[a_uid]", '*ban_date*' => "$ban_date", '*unban_date*' => "$unban_date");

foreach ($welcome_msg_vars as $variable_name => $variable_content)
{
  $coded_welcome_msg = str_replace("$variable_name","$variable_content","$coded_welcome_msg");
}

foreach ($banned_msg_vars as $variable_name => $variable_content)
{
  $coded_banned_msg = str_replace("$variable_name", "$variable_content", "$coded_banned_msg");
}

$identifier = pw_name_server_config::pm_identifier;
if (!$coded_welcome_msg) { $coded_welcome_msg = "Welcome on the server."; }
if (!$coded_banned_msg) { $coded_banned_msg = "You are banned from this server.."; }
if ($row['p_uid'])
{
  echo "$identifier|1|$coded_banned_msg|";
}
else
{
  echo "$identifier|0|$coded_welcome_msg|";
}
echo "$get_player_id";
?>
