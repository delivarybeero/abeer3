#!/bin/bash
# Script to add a new admin to the 'admins' table in MariaDB

# Variables
USER="abeer"
PASSWORD="abeer_zakut"
DATABASE="ecommerce_db"
NEW_ADMIN="ali@a.com"
NEW_PASSWORD="123"

# MariaDB command to insert data
mysql -u $USER -p$PASSWORD -D $DATABASE -e "INSERT INTO admins (username, password) VALUES ('$NEW_ADMIN', '$NEW_PASSWORD');"