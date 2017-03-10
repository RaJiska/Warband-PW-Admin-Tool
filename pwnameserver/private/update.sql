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
  PRIMARY KEY (id),
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

INSERT INTO welcome_messages (welcome_message, banned_message, server_id) VALUES ('Welcome on the server.', 'You are banned from this server.', '1');
INSERT INTO web_accounts (unique_id, name, passwd, permission_pp, permission_bl, permission_pn, permission_ap, permission_wp, permission_lb, server_id) VALUES ('0', 'root', SHA1('helloworld'), '1', '1', '1', '1', '1', '1', '0');
