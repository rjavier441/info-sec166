DROP DATABASE IF EXISTS infosec166;
CREATE DATABASE infosec166;
USE infosec166;

CREATE TABLE user (
	userid integer NOT NULL AUTO_INCREMENT,
	username varchar(50) NOT NULL,
	birthdate datetime,
	password varchar(100) NOT NULL,
	salt varchar(21) NOT NULL,
	bio varchar(250),
	PRIMARY KEY (userid),
	UNIQUE (username)
);

CREATE TABLE admin (
	userid integer NOT NULL,
	adminid integer NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (adminid),
	FOREIGN KEY (userid) REFERENCES user(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE session (
	sessionid integer NOT NULL AUTO_INCREMENT,
	userid integer NOT NULL,
	login datetime NOT NULL,
	token varchar(100),
	PRIMARY KEY (sessionid),
	FOREIGN KEY (userid) REFERENCES user(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE post (
	postid integer NOT NULL AUTO_INCREMENT,
	userid integer NOT NULL,
	content text CHARACTER SET utf8,
	posttime timestamp DEFAULT CURRENT_TIMESTAMP,
	title varchar(200),
	PRIMARY KEY (postid),
	FOREIGN KEY (userid) REFERENCES user(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE topic (
	topicid integer NOT NULL AUTO_INCREMENT,
	name varchar(50) NOT NULL,
	description varchar(200),
	PRIMARY KEY (topicid)
);

CREATE TABLE posttopic (
	postid integer NOT NULL,
	topicid integer NOT NULL,
	PRIMARY KEY (postid, topicid),
	FOREIGN KEY (postid) REFERENCES post(postid) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (topicid) REFERENCES topic(topicid) ON DELETE CASCADE ON UPDATE CASCADE
);
