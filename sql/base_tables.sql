CREATE TABLE session
(
    ID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_ID VARCHAR(64),
    session VARCHAR(255),
    valid_until DATETIME
);
CREATE TABLE users
(
    ID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    username VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255),
    active TINYINT(1) DEFAULT '0'
);
CREATE TABLE configs
(
    ID INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(64),
    value VARCHAR(4000)
);
CREATE UNIQUE INDEX session_ID_uindex ON session (ID);
CREATE UNIQUE INDEX session_user_ID_uindex ON session (user_ID);
CREATE UNIQUE INDEX users_email_uindex ON users (email);
CREATE UNIQUE INDEX users_username_uindex ON users (username);
CREATE UNIQUE INDEX configs_ID_uindex ON configs (ID);
CREATE UNIQUE INDEX configs_key_uindex ON configs (`key`);
CREATE INDEX configs_key_index ON configs (`key`);