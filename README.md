# Warband PW Punishments Manager
Original Thread: https://forums.taleworlds.com/index.php/topic,310983.0.html

Addon for the Persistent World module of Warband allowing admins to benefit from web-based tools.
WARNING: This tool was made a few years ago using the insecure mysql_* functions in its PHP files, which may not be working in your current PHP version (>= 7.0). You may nonetheless want to alter the code in order to use a secure SQL access engine such as mysqli_* functions or PDO.

# Features
* Web Hosted ban list
* No waiting time on unban
* Custom Ban Time (1 Hour, 3 Hours, 12 Hours, 1 Day, 3 Days, 1 Week, 2 Weeks, 3 Weeks, 1 Month)
* Punishment History
* Welcome message on join (different if the player is banned) with variables (such as player uid, unban date, player name etc..)
* Account Permissions Managing (can give or remove access to tabs of the name server / punishments manager)
* Player Unban Logs

# Welcome / Ban Message Variables
1. Welcome Message:
	* `\*`name`\*` = Display Player Name
	* `\*`uid`\*` = Display Player UID
2. Banned Message:
	* `\*`name`\*` = Display Player Name
	* `\*`uid`\*` = Display Player UID
	* `\*`invoker`\*` = Display Ban Invoker
	* `\*`invoker_uid`\*` = Display Ban Invoker UID
	* `\*`ban_date`\*` = Display date the player got banned on
	* `\*`unban_date`\*` = Display date the player will be unbanned on

# Installation
NOTES: If you already got the name server installed, it's advise to do files & database backup before editing anything. The Punishment manager in this version can only handle ONE server connected to the name server.
To install, you need a web server with PHP and MySQL installed; I use Linux and Apache (LAMP) so the instructions will be tailored to that. 
Lines starting with "$" represent commands to run in the shell prompt, and lines starting with "mysql>" represent queries to run in the mysql command line tool.

To start off, you will need to uncompress 'pwnameserver' into the web directory of your server. 
If you already got the name server installed, replace its folder. Make sure to set the name server as the directory you are in before connecting as root to your MySQL Server:

$ cd /var/www/pwnameserver/
$ mysql -u root -p

If you do not have the name server already installed:

mysql> CREATE DATABASE pw_player_names;
mysql> GRANT ALL PRIVILEGES ON pw_player_names.* TO 'pw_name_server'@'localhost' IDENTIFIED BY 'FX3nQGY5Hdqc';
mysql> USE pw_player_names;
mysql> SOURCE private/create_database.sql;
mysql> UPDATE warband_servers SET password = SHA1('N6XcqAXD') WHERE name = 'Server';

The last command set the connection password between your game server and the web server.
If you do have the name server installed:

mysql> USE pw_player_names;
mysql> SOURCE private/update.sql;

Now, the database tables and basic informations were created, you need to set the root user password:

mysql> UPDATE web_accounts SET passwd = SHA1('Qy7rQf9n') WHERE name = 'root';
mysql> exit;

Now the database is ready, make sure to edit the file 'private/config.php' to fit with your database informations.
Edit the constant lines (if needed) 'database_server_name', 'database_username', 'database_password', 'database_name' & 'server_id'.

We are almost done, web side, now, connect to the name server as root (in this tutorial, username:password = root:Qy7rQf9n).
Head to the tab 'Web Settings' create a new account with your UID (you can find it in your game server logs when you join it).
Now, make sure to always use the account you just created for yourself, you should only use the root account in case you loose the 'Web Settings' permissions.
You can also create an account for each of the admins you got in your team.

Now, you'll need to set a cronjob. It'll be a script that will be triggered each X minutes and that will check if the ban time is over. 
If it is, then, the player is removed from the ban list and is able to join the server, else, he'll remain banned untill his ban time is over. To do this, you must ensure cURL is installer.

$ crontab -e

Go at the bottom of the file and add a line:

*/10 * * * * curl -L localhost/pwnameserver/server-requests/auto_unban.php

Save and quit. This line means the link 'localhost/pwnameserver/server-requests/auto_unban.php' will be triggered each 10 minutes. 
You can find more informations about cronjobs by following this link.

Last step on the webserver. Every unbans are saved in a file 'unban.log'. 
However, this file display IP of the admin who unbanned the player (in case the unban was not done by the cronjob). 
This file is stored in the folder 'private', this folder is protected by the file '.htaccess', only listed IPs in this file can access the folder via a web interface. 
In order to grant yourself the access, at the end of the file, add the line: 'Allow from xx.xx.xx.xx' (replace the X values by your IP).

Now, game scripts need to be edited in order to link game server & webserver. 
To do so, open the folder 'scripts' from the archive you downloaded and search for 'str_name_server' & 'str_name_server_password'.
Make sure those four strings fit correctly with what you have set on your server (str_name_server_password correspond to the password for the server set in the database, 
in our tutorial, the password is 'N6XcqAXD'). Once done, you can upload the content of 'scripts' inside the folder 'PW_4.4' of your server.

You will also need to have the content of the scripts folder in your PW_4.4 module folder in your client directory in order to be able to temp ban players and have punishments recorded. 
Every admins using this tool will also need it. WARNING: Make sure to edit the four strings listed above with incorrect values to prevent security holes.
