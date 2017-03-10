<?php
if (isset($_GET['puid']) && isset($_GET['pname']) && isset($_GET['aname']) && isset($_GET['auid']) && isset($_GET['bantype']) && isset($_GET['pwd']))
{
  $get_player_uid = $_GET['puid'];
  $get_player_name = $_GET['pname'];
  $get_admin_name = $_GET['aname'];
  $get_admin_uid = $_GET['auid'];
  $get_ban_type = $_GET['bantype'];
  $get_passwd = $_GET['pwd'];
}
else
{
  exit;
}


require("../private/config.php");
$config = new pw_name_server_config();
if (!$config->connect_database()) exit();

date_default_timezone_set("Europe/Paris");
$current_epoch = time();

if ($get_ban_type == "slay") {
	$player_punishment = "Slay";
} elseif ($get_ban_type == "kick") {
	$player_punishment = "Kick";
} elseif ($get_ban_type == "1h") {
	$unban_epoch = strtotime("+1 hour");
	$player_punishment = "Temporary Ban (1 Hour)";
} elseif ($get_ban_type == "3h") {
	$unban_epoch = strtotime("+3 hours");
	$player_punishment = "Temporary Ban (3 Hours)";
} elseif ($get_ban_type == "12h") {
	$unban_epoch = strtotime("+12 hours");
	$player_punishment = "Temporary Ban (12 Hours)";
} elseif ($get_ban_type == "1d") {
	$unban_epoch = strtotime("+1 day");
	$player_punishment = "Temporary Ban (1 Day)";
} elseif ($get_ban_type == "3d") {
	$unban_epoch = strtotime("+3 days");
	$player_punishment = "Temporary Ban (3 Days)";
} elseif ($get_ban_type == "1w") {
	$unban_epoch = strtotime("+1 week");
	$player_punishment = "Temporary Ban (1 Week)";
} elseif ($get_ban_type == "2w") {
	$unban_epoch = strtotime("+2 weeks");
	$player_punishment = "Temporary Ban (2 Weeks)";
} elseif ($get_ban_type == "3w") {
	$unban_epoch = strtotime("+3 weeks");
	$player_punishment = "Temporary Ban (3 Weeks)";
} elseif ($get_ban_type == "1m") {
	$unban_epoch = strtotime("+1 month");
	$player_punishment = "Temporary Ban (1 Month)";
} elseif ($get_ban_type == "perm") {
        $unban_epoch = strtotime("+2 years");
	$player_punishment = "Permanent Ban";
}
$srv_id = mysql_fetch_array(mysql_query("SELECT id FROM warband_servers WHERE password = SHA1('$get_passwd');"));
$row = mysql_fetch_array(mysql_query("SELECT name FROM web_accounts WHERE unique_id = '$get_admin_uid';"));
if ($row['name']) $get_admin_name = $row['name']; 

if ($srv_id['id'])
{
  if ($get_ban_type != "kick" && $get_ban_type != "slay")
  {
    mysql_query("INSERT INTO ban_list (p_uid, p_name, a_name, a_uid, punishment_begin, punishment_end, server_id) VALUES ('$get_player_uid', '$get_player_name', '$get_admin_name', '$get_admin_uid', '$current_epoch', '$unban_epoch', '$srv_id[id]');");
  }
  mysql_query("INSERT INTO past_punishments (p_uid, p_name, a_uid, a_name, punishment, ban_time, server_id) VALUES ('$get_player_uid', '$get_player_name', '$get_admin_uid', '$get_admin_name', '$player_punishment', '$current_epoch', '$srv_id[id]');");
}
?>
