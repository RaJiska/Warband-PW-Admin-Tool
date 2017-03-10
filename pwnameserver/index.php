<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="main.css" />
<title>Persistent World name server administration</title>
</head>
<body>
<div id="header">
<h1>Persistent World name server administration</h1>
</div>
<div id="menu">
<hr/>
<a href="?page=player_names">Player names</a>
&nbsp;&nbsp;<a href="?page=admin_permissions">Admin permissions</a>
&nbsp;&nbsp;<a href="?page=servers">Servers</a>
&nbsp;&nbsp;<a href="?page=punishments">Punishments</a>
&nbsp;&nbsp;<a href="?page=ban_list">Ban List</a>
&nbsp;&nbsp;<a href="?page=log_ban">Log Ban</a>
&nbsp;&nbsp;<a href="?page=web_settings">Web Settings</a>
&nbsp;&nbsp;<a href="?page=log_out">Log out</a>
<hr/>
</div>
<div id="page">

<?php
require("private/config.php");
$config = new pw_name_server_config();
if (!$config->connect_database()) die("Could not connect to database.");

$result = mysql_query("SELECT permission_pp, permission_bl, permission_pn, permission_ap, permission_wp, permission_lb FROM web_accounts WHERE id = '$_SESSION[account_id]';");
$row = mysql_fetch_array($result);
require("log_in.php");
if (!check_log_in())
{
  show_log_in();
}
else if (isset($_GET['page']))
{
  switch ($_GET['page'])
  {
  case 'log_out':
    session_destroy();
    show_log_in();
    break;
  case 'web_settings':
    require("web_settings.php");
    if ($row['permission_wp']) { show_web_settings(); } 
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    break;
  case 'log_ban':
    require("log_ban.php");
    if ($row['permission_lb']) { show_log_ban(); }
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    echo("<p style=\" position: absolute; bottom: 0; left: 0; width: 100%; text-align: center;\">Punishments Manager Addon Proudly Made by <a href=\"http://forums.taleworlds.com/index.php?action=profile;u=172833\">Ra'Jiska</a> / <a href=\"http://steamcommunity.com/id/dodowololo/\">Dodo</a>.</p>");
    break;
  case 'ban_list':
    require("ban_list.php");
    if ($row['permission_bl']) { show_ban_list(); }
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    break;
  case 'punishments':
    require("punishments.php");
    if ($row['permission_pp']) { show_past_punishments(); }
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    break;
  case 'player_names':
    require("player_names.php");
    if ($row['permission_pn']) { show_player_names(); }
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    break;
  case 'admin_permissions':
    require("admin_permissions.php");
    if ($row['permission_ap']) { show_admin_permissions(); }
    else { echo('<div class="database_error">You do not have enough permissions to access this page.</div>'); }
    echo("<p style=\" position: absolute; bottom: 0; left: 0; width: 100%; text-align: center;\">Punishments Manager Addon Proudly Made by <a href=\"http://forums.taleworlds.com/index.php?action=profile;u=172833\">Ra'Jiska</a> / <a href=\"http://steamcommunity.com/id/dodowololo/\">Dodo</a>.</p>");
    break;
  case 'servers':
    require("servers.php");
    show_servers();
    echo("<p style=\" position: absolute; bottom: 0; left: 0; width: 100%; text-align: center;\">Punishments Manager Addon Proudly Made by <a href=\"http://forums.taleworlds.com/index.php?action=profile;u=172833\">Ra'Jiska</a> / <a href=\"http://steamcommunity.com/id/dodowololo/\">Dodo</a>.</p>");
    break;
  default:
    echo('<div class="database_error">No such page.</div>');
    echo("<p style=\" position: absolute; bottom: 0; left: 0; width: 100%; text-align: center;\">Punishments Manager Addon Proudly Made by <a href=\"http://forums.taleworlds.com/index.php?action=profile;u=172833\">Ra'Jiska</a> / <a href=\"http://steamcommunity.com/id/dodowololo/\">Dodo</a>.</p>");
  }
}
?>

</div>
</body>
</html>
