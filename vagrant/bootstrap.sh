#!/usr/bin/env bash

# start nginx
systemctl stop apache2
systemctl restart nginx
systemctl enable nginx

# start the node version of the app.
cd /vagrant/www/etc/node
forever start ./bin/www

cat /vagrant/www/vagrant/motd.txt
