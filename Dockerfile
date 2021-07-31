FROM phalconphp/php-apache:ubuntu-16.04
MAINTAINER Shreyas
#RUN apt-get update && apt-get -y install dos2unix && apt-get -y install vim
WORKDIR /app
COPY composer.* ./
RUN composer install
COPY . .
EXPOSE 9000