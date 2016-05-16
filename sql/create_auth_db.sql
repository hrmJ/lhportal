CREATE TABLE majakka_users (
user_id int(11) NOT NULL auto_increment,
username varchar(20) NOT NULL,
password char(40) NOT NULL,
PRIMARY KEY (user_id),
UNIQUE KEY username (username)
);
