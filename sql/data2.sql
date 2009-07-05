DELETE FROM pqr_achievements;

INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 50','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 60','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 70','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 80','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 90','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 80','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 80','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 80','l80.jpg');
INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('lvl80','Achieve level 80','l80.jpg');

DELETE FROM pqr_raids;

INSERT INTO pqr_raids(name,info,icon,raid_oclock) VALUES('Kara','Some info here','mystery.png',DATE('2008-12-02 18:00:00'));

-- INSERT INTO pqr_raidaccess(achievement_id,raid_id) VALUES(2,1);

DELETE FROM pqr_raidroles;

INSERT INTO pqr_raidroles(raid_id,role_id,quantity) VALUES(1,1,3);
INSERT INTO pqr_raidroles(raid_id,role_id,quantity) VALUES(1,2,5);
INSERT INTO pqr_raidroles(raid_id,role_id,quantity) VALUES(1,3,2);

DELETE FROM pqr_signups;

INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','Eritha','DPS','CCFFFF');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaA','DPS','CCFFFF');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaB','Tank','999966');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaC','Healer','FFFF66');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaD','DPS','CCFFFF');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaY','DPS','CCFFFF');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaS','DPS','CCFFFF');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaE','Healer','FFFF66');
INSERT INTO pqr_signups(raid_id,player_id,csc_name,csc_role,csc_role_colour) VALUES(1,'eritha','ErithaI','Tank','999966');
