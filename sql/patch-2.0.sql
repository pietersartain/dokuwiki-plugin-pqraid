-- pqr raid calendar database setup patch from 1.0 to 2.0
-- author:	P E Sartain
-- date:	15/02/2009

-- Changelog:
-- 20090215 created

-- ######################
-- #  PQ Rank tables    #
-- ######################

-- Rank list
DROP TABLE IF EXISTS pqr_rank_list;
CREATE TABLE pqr_rank_list (
	rank_id integer auto_increment primary key,
	rank_name varchar(128),
	rank_desc varchar(512));

-- Default ranks
INSERT INTO pqr_rank_list(rank_name,rank_desc) VALUES('Lead Raider','The guy or gal giving the orders. Has the final say in where the raid group goes, and is responsible for the fun.');
INSERT INTO pqr_rank_list(rank_name,rank_desc) VALUES('Event Organiser','The guy or gal doing the invites. A purely functional role, is responsible for sorting out the invites and the initial in-raid promotions, including main tank lists.');

-- Player / Rank join
-- DROP TABLE IF EXISTS pqr_ranks;
CREATE TABLE pqr_ranks (
	id integer auto_increment primary key,
	player_id varchar(32),
	rank_id integer DEFAULT 2,
	count integer DEFAULT 0,
	total integer DEFAULT 0,
	last integer DEFAULT 0);

-- #################################################
-- #  Patch some old tables for new functionality  #
-- #################################################

-- Raid storage signup table, pqr_signups
ALTER TABLE pqr_signups
	ADD static_raid_organiser varchar(32),
	ADD static_lead_raider varchar(32);

-- New class list table, basically links class to image for a CSC
ALTER TABLE pqr_csc 
	ADD csc_class varchar(32);

