username: vagrant
password: vagrant

sudo apt-get update
sudo apt-get install build-essential zlib1g-dev git-core sqlite3 libsqlite3-dev
sudo aptitude install mysql-server mysql-client


sudo nano /etc/mysql/my.cnf
change:
bind-address            = 0.0.0.0


mysql -u root -p

use mysql
GRANT ALL ON *.* to root@'33.33.33.1' IDENTIFIED BY 'jarvis';
FLUSH PRIVILEGES;
exit


sudo /etc/init.d/mysql restart
