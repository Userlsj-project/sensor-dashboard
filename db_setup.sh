#!/bin/bash
sudo mysql -u root -e "CREATE USER IF NOT EXISTS 'sensor_user'@'localhost' IDENTIFIED BY 'sensor_pass';"
sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS sensor_db;"
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON sensor_db.* TO 'sensor_user'@'localhost';"
sudo mysql -u root -e "FLUSH PRIVILEGES;"
echo "Database setup complete."
