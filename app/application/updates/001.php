<?php


CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_user (
			id int(11) auto_increment PRIMARY KEY,
			email varchar(200) UNIQUE KEY,
 			password varchar(200),
 			nom varchar(200),
 			prenom varchar(200),
 			actif BOOL,
 			created datetime)");

CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_home (
			id int(11) auto_increment PRIMARY KEY,
			owner int(11) NOT NULL,
			title varchar(200),
			mode int,
			confort_temp float,
			absence_temp float)");

CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_room (
			id int(11) auto_increment PRIMARY KEY,
			home int(11) NOT NULL,
			title varchar(200),
			mode int)");

CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_room_program (
			id int(11) auto_increment PRIMARY KEY,
			room int(11) NOT NULL,
			weekday int default 128,
			start time,
			temp float)");


CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_device (
			id int(11) auto_increment PRIMARY KEY,
			uuid varchar(200),
			title varchar(200),
			room int(11) default null,
			temp int(1),
			kind varchar(200))");

CDatabase::DB()->query("CREATE TABLE IF NOT EXISTS t_device_program (
			id int(11) auto_increment PRIMARY KEY,
			room int(11) NOT NULL,
			start time,
			pin varchar(10),
			state int)");
