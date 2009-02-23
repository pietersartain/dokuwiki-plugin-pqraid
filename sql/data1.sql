DELETE FROM pqr_raids;
DELETE FROM pqr_signups;
DELETE FROM pqr_csc;

INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('a','a1',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('a','a2',1);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('a','',-1);


INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('b','b1',3);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('b','b2',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('b','',-1);


INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('c','c1',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('c','c2',-1);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('c','',-1);

DELETE FROM pqr_accesstokens;

INSERT INTO pqr_accesstokens(achievement_id,csc_id) VALUES(2,7);


INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('d','d1',1);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('d','d2',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('d','d3',3);


INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('e','e1',1);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('e','e2',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('e','e3',3);


INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('f','f1',1);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('f','f2',2);
INSERT INTO pqr_csc(player_id,character_name,role_id) VALUES('f','f3',3);

DELETE FROM pqr_ranks;

INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('a',1,0,0,0);
INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('b',1,0,0,0);
INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('c',2,0,0,0);
INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('d',2,0,0,0);
INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('e',2,0,0,0);
INSERT INTO pqr_ranks(player_id,rank_id,count,total,last) VALUES('f',2,0,0,0);

