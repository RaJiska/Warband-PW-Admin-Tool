CREATE TABLE player_names (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  unique_id INT(8) UNSIGNED NOT NULL,
  name VARCHAR(28) NOT NULL,
  last_used_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  inserted_by_warband_server_id INT(5) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (unique_id),
  UNIQUE KEY (name)
);

CREATE TABLE clans (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(28) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name)
);

CREATE TABLE clan_tags (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  clan_id INT(5) UNSIGNED NOT NULL,
  tag VARCHAR(28) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (tag)
);

CREATE TABLE clan_players (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  clan_id INT(5) UNSIGNED NOT NULL,
  unique_id INT(8) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (clan_id, unique_id)
);

CREATE TABLE warband_servers (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(20) NOT NULL,
  password CHAR(40) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (password)
);

CREATE TABLE admin_permissions (
  id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  unique_id INT(8) UNSIGNED NOT NULL,
  server_id INT(5) UNSIGNED NOT NULL,
  panel BOOLEAN DEFAULT FALSE NOT NULL,
  gold BOOLEAN DEFAULT FALSE NOT NULL,
  kick BOOLEAN DEFAULT FALSE NOT NULL,
  temporary_ban BOOLEAN DEFAULT FALSE NOT NULL,
  permanent_ban BOOLEAN DEFAULT FALSE NOT NULL,
  kill_fade BOOLEAN DEFAULT FALSE NOT NULL,
  freeze BOOLEAN DEFAULT FALSE NOT NULL,
  teleport_self BOOLEAN DEFAULT FALSE NOT NULL,
  admin_items BOOLEAN DEFAULT FALSE NOT NULL,
  heal_self BOOLEAN DEFAULT FALSE NOT NULL,
  godlike_troop BOOLEAN DEFAULT FALSE NOT NULL,
  ships BOOLEAN DEFAULT FALSE NOT NULL,
  announce BOOLEAN DEFAULT FALSE NOT NULL,
  override_poll BOOLEAN DEFAULT FALSE NOT NULL,
  all_items BOOLEAN DEFAULT FALSE NOT NULL,
  mute BOOLEAN DEFAULT FALSE NOT NULL,
  animals BOOLEAN DEFAULT FALSE NOT NULL,
  factions BOOLEAN DEFAULT FALSE NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (server_id, unique_id)
);

CREATE TABLE past_punishments (
  id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
  p_uid INT(8) UNSIGNED NOT NULL,
  p_name VARCHAR(28) NOT NULL,
  punishment VARCHAR(30) NOT NULL,
  a_uid INT(8) UNSIGNED NOT NULL,
  a_name VARCHAR(28) NOT NULL,
  ban_time INT(30) NOT NULL,
  server_id INT(5) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE ban_list (
  id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
  p_uid INT(8) UNSIGNED NOT NULL,
  p_name VARCHAR(28) NOT NULL,
  a_uid INT(8) UNSIGNED NOT NULL,
  a_name VARCHAR(28) NOT NULL,
  punishment_begin INT(30) NOT NULL,
  punishment_end INT(30) NOT NULL,
  server_id INT(5) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE web_accounts (
  id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
  unique_id INT(8) UNSIGNED NOT NULL,
  name VARCHAR(20) NOT NULL,
  passwd CHAR(40) NOT NULL,
  permission_pp BOOLEAN DEFAULT FALSE NOT NULL,
  permission_bl BOOLEAN DEFAULT FALSE NOT NULL,
  permission_pn BOOLEAN DEFAULT FALSE NOT NULL,
  permission_ap BOOLEAN DEFAULT FALSE NOT NULL,
  permission_wp BOOLEAN DEFAULT FALSE NOT NULL,
  permission_lb BOOLEAN DEFAULT FALSE NOT NULL,
  server_id INT(5) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name, unique_id)
);

CREATE TABLE welcome_messages (
  id INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  welcome_message VARCHAR(300),
  banned_message VARCHAR(300),
  server_id INT(5) NOT NULL,
  PRIMARY KEY (id)
);

INSERT into warband_servers (name, password) VALUES ('Server', SHA1('password'));
INSERT INTO welcome_messages (welcome_message, banned_message, server_id) VALUES ('Welcome on the server.', 'You are banned from this server.', '1');
INSERT INTO web_accounts (unique_id, name, passwd, permission_pp, permission_bl, permission_pn, permission_ap, permission_wp, permission_lb, server_id) VALUES ('0', 'root', SHA1('helloworld'), '1', '1', '1', '1', '1', '1', '0');
