#!/bin/bash

# Exit on error
set -o errexit -o pipefail

# Update yum
yum update -y

# Install packages
yum install -y curl
yum install -y git

# Remove current apache & php
yum -y remove httpd* php*

# Install PHP 7.1
yum install -y php73 php73-cli php73-fpm php73-mysql php73-xml php73-curl php73-opcache php73-pdo php73-gd php73-pecl-apcu php73-mbstring php73-imap php73-pecl-redis php73-mysqlnd mod24_ssl
#  php73-mcrypt

# Install Apache 2.4
yum -y install httpd24

# Allow URL rewrites
sed -i 's#AllowOverride None#AllowOverride All#' /etc/httpd/conf/httpd.conf

# Change apache document root
mkdir -p /var/www/html/public
sed -i 's#DocumentRoot "/var/www/html"#DocumentRoot "/var/www/html/public"#' /etc/httpd/conf/httpd.conf

# Change apache directory index
sed -e 's/DirectoryIndex.*/DirectoryIndex index.html index.php/' -i /etc/httpd/conf/httpd.conf

# Get Composer, and install to /usr/local/bin
if [ ! -f "/usr/local/bin/composer" ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    /usr/local/bin/composer self-update --stable --no-ansi --no-interaction
fi

# Restart apache
service httpd start

# Setup apache to start on boot
chkconfig httpd on

# Ensure aws-cli is installed and configured
if [ ! -f "/usr/bin/aws" ]; then
    curl "https://s3.amazonaws.com/aws-cli/awscli-bundle.zip" -o "awscli-bundle.zip"
    unzip awscli-bundle.zip
    ./awscli-bundle/install -b /usr/bin/aws
fi

# Ensure AWS Variables are available
if [[ -z "$AWS_ACCOUNT_ID" || -z "$AWS_DEFAULT_REGION " ]]; then
    echo "AWS Variables Not Set.  Either AWS_ACCOUNT_ID or AWS_DEFAULT_REGION"
fi
