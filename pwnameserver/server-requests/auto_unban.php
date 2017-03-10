<?php
require("../private/config.php");
$config = new pw_name_server_config();
if (!$config->connect_database()) exit();

date_default_timezone_set("Europe/Paris");
$current_epoch = time();
$result = mysql_query("SELECT * FROM ban_list;");

while($row = mysql_fetch_assoc($result))
{
  $player_name = $row['p_name'];
  $player_uid = $row['p_uid'];
  $punish_begin = $row['punishment_begin'];
  $punish_end = $row['punishment_end'];
  $server_id = $row['server_id'];
  $calcul = $current_epoch - $punish_end;
  if ($calcul > 0)
  {
    mysql_query("DELETE FROM ban_list WHERE p_uid = '$player_uid' AND server_id = '$row[server_id]';");
    $current_date = date("d-m-Y H:i:s");
    $file = "../private/unban.log";
    $fh = fopen($file, 'a');
    $stringData = $current_date . ": '" . $player_name . "' (" . $player_uid . ") has been unbanned by 'SERVER'.\n";
    fwrite($fh, $stringData);
    fclose($fh);
  }
  echo mysql_error();
}
?>
