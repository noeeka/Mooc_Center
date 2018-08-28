yum -y update
yum -y upgrade
yum  install -y epel-release
yum install -y libmcrypt libmcrypt-devel mcrypt mhash git
git clone https://github.com/noeeka/Mooc_Center.git
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
rpm -Uvh http://nginx.org/packages/centos/6/noarch/RPMS/nginx-release-centos-6-0.el6.ngx.noarch.rpm
yum -y install nginx 
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
yum -y install php70w php70w-fpm php70w-mysql php70w-mbstring php70w-mcrypt php70w-gd php70w-imap php70w-ldap php70w-odbc php70w-pear php70w-xml php70w-xmlrpc php70w-pdo php70w-apcu php70w-pecl-redis php70w-pecl-memcached php70w-devel
yum install -y php70w-redis
rpm -Uvh http://mirrors.ustc.edu.cn/fedora/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum install -y redis php70w-redis
rpm -Uvh https://dev.mysql.com/get/mysql57-community-release-el6-9.noarch.rpm 
yum install -y mysql-community-server
yum install epel -y

chmod -R 777 /usr/local/go
echo 'export GOROOT=/usr/local/go
	  export GOPATH=/home/go
	  export PATH=$PATH:$GOROOT/bin'>> /etc/profile
source /etc/profile
echo 'server {
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
	access_log  /var/log/nginx/access.log  main;
	include /etc/nginx/conf.d/*.conf;
}'>/etc/nginx/nginx.conf

yum install -y gcc-c++ make
curl -sL https://rpm.nodesource.com/setup_6.x | sudo -E bash -
yum erase nodejs npm -y
yum install nodejs -y
yum install npm --enablerepo=epel
/etc/init.d/iptables stop
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
cd /data/www/iview
npm install --save iview
rm -rf node_modules && rm package-lock.json && npm cache clear --force && npm install && npm install jquery --save


wget https://ffmpeg.org/releases/ffmpeg-4.0.2.tar.bz2
tar -xvf ffmpeg-4.0.2.tar.bz2
cd ffmpeg-4.0.2
./configure --disable-x86asm
make && make install