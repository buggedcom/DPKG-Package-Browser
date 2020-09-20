#!/usr/bin/env bash

# Set timezone
ln -sf  /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

apt-get update
apt-get install -y curl git unzip

# Install php 7.2
add-apt-repository ppa:ondrej/php
apt-get update
apt-get install -y php7.2
apt-get install -y php-pear php7.2-fpm php7.2-mbstring php7.2-xml php7.2-gd php7.2-zip php7.2-curl php7.2-intl php7.2-mailparse php7.2-dev

# Install composer
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install nginx
systemctl disable apache2
systemctl stop apache2
apt-get install -y nginx
mv /home/vagrant/nginx.conf /etc/nginx/nginx.conf
mv /home/vagrant/app.conf /etc/nginx/sites-enabled/app.conf
rm /etc/nginx/sites-enabled/default
systemctl restart nginx
systemctl enable nginx

# Install nodejs, npm, yarn
apt-get install -y nodejs npm
npm cache clean
npm install n -g
npm i -g npm
n stable
apt-get purge -y nodejs npm
ln -sf /usr/local/bin/node /usr/bin/node
ln -sf /usr/local/bin/npm /usr/bin/npm
npm install -g webpack-dev-server
npm install -g nodemon
npm install -g yarn
npm install -g @vue/cli
npm install -g forever

# Install node modules for vue frontend
cd /vagrant/www/etc/vue/
yarn install
export DEBUG='express:*' # for debugging of requests in dev server

# Install node modules for backend
cd /vagrant/www/etc/node
yarn install

# Install php composer vendors
cd /vagrant/www/etc/php
composer install

# Sstart the node version of the app.
cd /vagrant/www/etc/node
forever start ./bin/www

service nginx restart

# Add ascii, cos why not
cp /vagrant/www/vagrant/motd.txt /etc/motd
cat /vagrant/www/vagrant/motd.txt