yum -y update
yum -y upgrade
rpm -Uvh http://mirror.centos.org/centos/6/extras/x86_64/Packages/epel-release-6-8.noarch.rpm
yum  install -y epel-release
rpm -Uvh http://www6.atomicorp.com/channels/atomic/centos/6/x86_64/RPMS/libmcrypt-2.5.7-5.el6.art.x86_64.rpm
yum install -y libmcrypt libmcrypt-devel mcrypt mhash git
git clone http://gitlab.szwhg.chaoxing.com/chenchen/Mooc_Center.git
mkdir /usr/local/go
cp -r ./Mooc_Center/go/* /usr/local/go
mkdir /usr/local/weeds
cp -r ./Mooc_Center/weed/* /usr/local/weeds
mkdir /data
mkdir /data/www
mkdir /data/www/mooc_center
mkdir /data/www/iview
cp -r ./Mooc_Center/code/* /data/www/mooc_center
cp -r ./Mooc_Center/iview/* /data/www/iview
mkdir /data/www/chaoxingwhg
cp -r ./Mooc_Center/chaoxingwhg/* /data/www/chaoxingwhg
rpm -Uvh http://nginx.org/packages/centos/6/noarch/RPMS/nginx-release-centos-6-0.el6.ngx.noarch.rpm
yum -y install nginx 
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
yum -y install php70w php70w-fpm php70w-mysql php70w-mbstring php70w-mcrypt php70w-gd php70w-imap php70w-ldap php70w-odbc php70w-pear php70w-xml php70w-xmlrpc php70w-pdo php70w-apcu php70w-pecl-redis php70w-pecl-memcached php70w-devel
rpm -Uvh http://mirrors.ustc.edu.cn/fedora/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum install -y redis
rpm -Uvh https://dev.mysql.com/get/mysql57-community-release-el6-9.noarch.rpm 
yum install -y mysql-community-server
yum install epel -y
yum install sudo -y
yum install python-devel -y
yum install libxml-devel  -y
yum install libxslt-devel -y

chmod -R 777 /usr/local/go
echo 'export GOROOT=/usr/local/go
	  export GOPATH=/home/go
	  export PATH=$PATH:$GOROOT/bin'>> /etc/profile
source /etc/profile
echo 'server{
	listen 80 default_server;
	listen [::]:80 default_server;
	root /data/www/mooc_center/public;
	index index.html index.htm index.php;
	server_name _;
	location / {
		if (!-e $request_filename) {
			rewrite  ^(.*)$  /index.php?s=$1  last;
			break;
		}
    }
	
	location ~ [^/]\.php(/|$){
		fastcgi_pass  127.0.0.1:9000;	
		fastcgi_buffer_size 128k;
		fastcgi_buffers 8 128k;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_param  QUERY_STRING       $query_string;
		fastcgi_param  REQUEST_METHOD     $request_method;
		fastcgi_param  CONTENT_TYPE       $content_type;
		fastcgi_param  CONTENT_LENGTH     $content_length;
		fastcgi_param  SCRIPT_NAME        $fastcgi_script_name;
		fastcgi_param  REQUEST_URI        $request_uri;
		fastcgi_param  DOCUMENT_URI       $document_uri;
		fastcgi_param  DOCUMENT_ROOT      $document_root;
		fastcgi_param  SERVER_PROTOCOL    $server_protocol;
		fastcgi_param  REQUEST_SCHEME     $scheme;
		fastcgi_param  HTTPS              $https if_not_empty;
		fastcgi_param  GATEWAY_INTERFACE  CGI/1.1;
		fastcgi_param  SERVER_SOFTWARE    nginx/$nginx_version;
		fastcgi_param  REMOTE_ADDR        $remote_addr;
		fastcgi_param  REMOTE_PORT        $remote_port;
		fastcgi_param  SERVER_ADDR        $server_addr;
		fastcgi_param  SERVER_PORT        $server_port;
		fastcgi_param  SERVER_NAME        $server_name;
		fastcgi_param  REDIRECT_STATUS    200;
	}
}'>/etc/nginx/conf.d/mooc_center.conf
echo 'server
{
        listen 80;
       
        server_name demo-mooc.com;
        index index.php index.html index.htm;
        root  /data/www/chaoxingwhg/public;


        #error_page   404   /404.html;
        #include enable-php.conf;
       
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;


		location = /themes/simpleboot3/mooc-admin {
			proxy_pass http://127.0.0.1:8082/#/login;
		}

		location ^~ /dist/{
			proxy_pass http://127.0.0.1:8082;
		}
        location /nginx_status
        {
            stub_status on;
            access_log   off;
        }

        location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
        {
            expires      30d;
        }

        location ~ .*\.(js|css)?$
        {
            expires      12h;
        }
        location ~ /\.
        {
            deny all;
        }
        location / {
			if (!-e $request_filename) {
				rewrite  ^(.*)$  /index.php?s=$1  last;
				break;
			}
        }
		location ~ [^/]\.php(/|$)
        {
            try_files $uri =404;
            fastcgi_pass  127.0.0.1:9000;# unix:/tmp/php-cgi.sock;
            fastcgi_buffer_size 128k;
            fastcgi_buffers 32 32k;
            fastcgi_index index.php;
            include fastcgi.conf;
            include pathinfo.conf;
        }
}'>/etc/nginx/conf.d/demo-mooc.conf
echo 'user nginx;
worker_processes  1;

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;


events {
    worker_connections  1024;
}


http {
	sendfile on;
	tcp_nopush on;
	tcp_nodelay on;
	keepalive_timeout 65;
	types_hash_max_size 2048;
	include /etc/nginx/mime.types;
	default_type application/octet-stream;
	access_log  /var/log/nginx/access.log;
	include /etc/nginx/conf.d/*.conf;
}'>/etc/nginx/nginx.conf
echo 'extension = "redis.so"'>> /etc/php.ini
rpm -Uvh http://mirror.centos.org/centos/6/os/x86_64/Packages/gcc-c++-4.4.7-23.el6.x86_64.rpm
yum install -y gcc-c++
rpm -Uvh http://mirror.centos.org/centos/6/os/x86_64/Packages/make-3.81-23.el6.x86_64.rpm
yum install -y make
yum install wget -y
wget https://github.com/nicolasff/phpredis/archive/master.zip
apt-get install unzip -y
yum install unzip -y
unzip master.zip 
cd phpredis-master
phpize
./configure --with-php-config=/usr/bin/php-config
make && make install
curl -sL https://rpm.nodesource.com/setup_6.x | sudo -E bash -
yum erase nodejs npm -y
yum install nodejs -y
yum install -y npm --enablerepo=epel
/etc/init.d/nginx restart
/etc/init.d/php-fpm restart
/etc/init.d/mysqld restart
a=$(grep 'temporary password' /var/log/mysqld.log |cut -d ":" -f 4,10 | sed 's/^[ \t]*//g')
echo "<?php
return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '127.0.0.1',
    // 数据库名
    'database'        => 'mooc_center',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => '${a}',
    // 端口
    'hostport'        => '',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => false,
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
];
">/data/www/mooc_center/application/database.php
echo "<?php
return [
    // 数据库类型
    'type'     => 'mysql',
    // 服务器地址
    'hostname' => 'localhost',
    // 数据库名
    'database' => 'demo-mooc',
    // 用户名
    'username' => 'root',
    // 密码
    'password' => '${a}',
    // 端口
    'hostport' => '3306',
    // 数据库编码默认采用utf8
    'charset'  => 'utf8mb4',
    // 数据库表前缀
    'prefix'   => 'cxtj_',
    'authcode' => 'HmyDzUUsgsAPtBNUpM',
    //#COOKIE_PREFIX#
];">/data/www/chaoxingwhg/data/conf/database.php
cd /data/www/iview
wget https://ffmpeg.org/releases/ffmpeg-4.0.2.tar.bz2
tar -xvf ffmpeg-4.0.2.tar.bz2
cd ffmpeg-4.0.2
./configure --disable-x86asm
make && make install
chmod -R 777 /usr/local/weeds
cd /usr/local/weeds && sh master.sh && sh volume.sh
chmod -R 777 /data/www/mooc_center
cd /data/www/mooc_center && php time.php start -d
chmod -R 777 /data/www/iview
cd /data/www/iview && sh mooc_admin.sh
mysql -uroot -p${a} <  /data/www/mooc_center/database/mooc_center.sql
mysql -uroot -p${a} <  /data/www/chaoxingwhg/demo-mooc.sql
