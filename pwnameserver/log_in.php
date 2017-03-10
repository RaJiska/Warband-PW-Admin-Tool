<?php
function show_log_in()
{
  if (isset($_GET["page"]) && $_GET["page"] == "log_out") $action = ".";
  else $action = htmlspecialchars($_SERVER["REQUEST_URI"]);
  echo("<form action=\"$action\" method=\"post\"><table id=\"log_in_box\"><tbody>");
  echo('<tr><td>Server name:</td><td><input type="text" name="server_name" id=\"login_pass\"/></td></tr>');
  echo('<tr><td>Server password:</td><td><input type="password" id="login_pass" name="server_password" /></td></tr>');
  echo('<tr><td></td><td><input type="submit" name="log_in" value="Log in"/></td></tr>');
  echo('</tbody></table></form>');
}

function check_log_in()
{
  if (isset($_POST["log_in"]))
  {
    $server_name = filter_input(INPUT_POST, "server_name", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
    $server_password = filter_input(INPUT_POST, "server_password", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    if ($server_name && $server_password)
    {
      $server_name = mysql_real_escape_string($server_name);
      $server_password = mysql_real_escape_string($server_password);
      $result = mysql_query("SELECT id, name, server_id FROM web_accounts WHERE name = '$server_name' AND passwd = SHA1('$server_password');");
      if ($result && $row = mysql_fetch_assoc($result))
      {
        session_regenerate_id(true);
        $_SESSION["account_id"] = $row["id"];
        $_SESSION["account_name"] = $row["name"];
        $_SESSION["server_id"] = $row["server_id"];
        $_SESSION["time"] = time();
        return true;
      }
    }
    echo('<div class="database_error">Invalid server name or password.</div>');
  }
  else if (isset($_SESSION["account_id"], $_SESSION["account_name"], $_SESSION["time"]))
  {
    $current_time = time();
    if ($current_time - $_SESSION["time"] < 3600)
    {
      $_SESSION["time"] = $current_time;
      return true;
    }
    else session_destroy();
  }
  return false;
}
?>
