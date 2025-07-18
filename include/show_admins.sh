#!/bin/bash
# Script to display the data from the 'admins' table in MariaDB

# Variables
USER="abeer"
PASSWORD="abeer_zakut"
DATABASE="ecommerce_db"

# MariaDB command to fetch data
mysql -u $USER -p$PASSWORD -D $DATABASE -e "SELECT * FROM admins;"