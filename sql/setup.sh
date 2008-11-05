#!/bin/bash

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# setup.sh - a shell script to automate setting up a new database with mysql
#
#	author: P E Sartain
#	date:   27/06/2008
#
#	Changelog:
#	20081020 changelog added
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# Verify these things with the command line:
# mysql -u localuser --password=localpasswd -P 3306 -D localdb -h 127.0.0.1
# 
# show databases;
# show tables;
# select * from user;
# select * from page;
# flush privileges;
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


function helpme {

echo "	usage:  $0 [COMMAND] [sql script]

	where COMMAND is one of:

		--help		this message

		--init		create database and user
		--deinit	destroy user and database

		--create [SQL]	create tables and populate them
		--destroy	destroy tables

		--retry [SQL]	clears all tables before attempting to 
				recreate them from script.
				
		--status	displays the current status of the dbs
		
		--run		execute abitrary SQL
"

}

rootpasswd="geoff"
rootuser="root"

localpasswd="ollyship02"
localuser="pesar2_pq"

localdb="pesar2_pq"

function create {
# Populate the database with tables (or content, or any arbitrary SQL script)
cat $1 | mysql --user=$localuser --password=$localpasswd $localdb
}

function destroy {
# Remove the database
echo "drop database "$localdb";" | mysql --user=$rootuser --password=$rootpasswd
# Create a new database:
echo "create database "$localdb";" | mysql --user=$rootuser --password=$rootpasswd
}

case x"$1" in

	x"--status")

echo "##############"
echo "show databases" | mysql --user=$rootuser --password=$rootpasswd
echo "##############"
echo "show tables from "$localdb"" | mysql --user=$rootuser --password=$rootpasswd
echo "##############"
echo "show grants for '"$localuser"'@'localhost'" | mysql --user=$rootuser --password=$rootpasswd
echo "##############"

	;;

	x"--init")
	
# Create a new database:
echo "create database "$localdb";" | mysql --user=$rootuser --password=$rootpasswd

# Create a new account:
echo "grant all on "$localdb".* to "$localuser"@'localhost' identified by '"$localpasswd"';" | mysql --user=$rootuser --password=$rootpasswd

;;
	x"--create")
	create $2
;;
	x"--destroy")
	destroy
;;
	x"--retry")
	destroy
	create $2
;;
	x"--deinit")

# Remove the user's rights
echo "revoke all privileges on "$localdb".* from '"$localuser"'@'localhost';" | mysql --user=$rootuser --password=$rootpasswd

# Delete the user account
echo "drop user '"$localuser"'@'localhost';" | mysql --user=$rootuser --password=$rootpasswd

# Reset the privileges so Piete doesn't get confused and think the account still exists
echo "flush privileges;" | mysql --user=$rootuser --password=$rootpasswd

# Remove the database
echo "drop database "$localdb";" | mysql --user=$rootuser --password=$rootpasswd

;;

	x"--help")

	helpme
;;
	x"--run")
echo "$2" | mysql --user=$localuser --password=$localpasswd $localdb
;;
	*)
	
	helpme
;;
esac
