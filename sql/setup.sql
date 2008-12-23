-- pqr raid calendar database setup
-- author:	P E Sartain
-- date:	20/10/2008

-- Changelog:
-- 20081104	security logs & raid access token information
-- 20081020 created

-- ######################
-- #  Character tables  #
-- ######################

-- Role list
DROP TABLE IF EXISTS pqr_roles;
CREATE TABLE pqr_roles (
	role_id integer auto_increment primary key,
	name varchar(128),
	colour varchar(6));

-- CSC list: character/role join table
DROP TABLE IF EXISTS pqr_csc;
CREATE TABLE pqr_csc (
	csc_id integer auto_increment primary key,
	character_name varchar(64),
	role_id integer,
	player_id varchar(32),
	csc_possible integer DEFAULT 0,
	csc_attended integer DEFAULT 0);

-- ########################
-- #  Achievement tables  #
-- ########################

-- Achievement list (all of 'em)
DROP TABLE IF EXISTS pqr_achievements;
CREATE TABLE pqr_achievements (
	achievement_id integer auto_increment primary key,
	short_name varchar(64),
	long_name varchar(512),
	icon varchar(64));

-- Accesstoken list: CSC/achievement join table
DROP TABLE IF EXISTS pqr_accesstokens;
CREATE TABLE pqr_accesstokens (
	achievement_id integer,
	csc_id integer,
	set_by integer,
	set_when datetime);

-- #################
-- #  Raid tables  #
-- #################

-- Raid list
DROP TABLE IF EXISTS pqr_raids;
CREATE TABLE pqr_raids (
	raid_id integer auto_increment primary key,
	name varchar(256),
	info varchar(512),
	wwslink varchar(512),
	icon varchar(64),
	raid_oclock datetime
	);

-- Raid restrictions list: raid/achievement join table
DROP TABLE IF EXISTS pqr_raidaccess;
CREATE TABLE pqr_raidaccess (
	achievement_id integer,
	raid_id integer);

-- Role numbers for a given raid: raid/role join table
DROP TABLE IF EXISTS pqr_raidroles;
-- CREATE TABLE pqr_raidroles (
--	raid_id integer,
--	role_id integer,
--	quantity integer);

-- Week information
DROP TABLE IF EXISTS pqr_weeks;
CREATE TABLE pqr_weeks (
	week_num integer,
	info varchar(512));

-- #######################
-- #  Scheduling tables  #
-- #######################

-- Main CSC order table
-- DROP TABLE IF EXISTS pqr_cscorder;
-- CREATE TABLE pqr_cscorder (
--	order_id integer auto_increment primary key,
--	player_id varchar(32),
--	csc_id integer,
--	cscorder integer);

-- Signup list. Static to save problems with changing CSC ids
DROP TABLE IF EXISTS pqr_signups;
CREATE TABLE pqr_signups (
	raid_id integer,
	player_id varchar(32),
	csc_name varchar(64),
	csc_role varchar(64),
	csc_role_colour varchar(64)
	);

-- Unavailable list
DROP TABLE IF EXISTS pqr_unavail;
CREATE TABLE pqr_unavail (
	player_id varchar(32),
	unavail datetime);

-- Autopsy tables
DROP TABLE IF EXISTS pqr_log;
-- CREATE TABLE pqr_log (
--	raid_id integer,
--	csc_id integer);

-- Default data
INSERT INTO pqr_roles(name,colour) VALUES('Healer','FFFFCC');
INSERT INTO pqr_roles(name,colour) VALUES('DPS','CCFFFF');
INSERT INTO pqr_roles(name,colour) VALUES('Tank','999966');
