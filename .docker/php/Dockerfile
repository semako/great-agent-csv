FROM php:7.2.8-fpm

RUN apt update && \
	apt install -y \
		libmcrypt-dev \
		unzip \
		git \
		iputils-ping \
		nano \
		zlib1g-dev && \
	CFLAGS="-I/usr/src/php" docker-php-ext-install -j$(nproc) zip json hash && \
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    echo "upload_max_filesize = 128M" > /usr/local/etc/php/conf.d/upload-max-filesize.ini && \
    echo "post_max_size = 128M" > /usr/local/etc/php/conf.d/post-max-size.ini