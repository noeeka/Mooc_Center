yum -y update
yum -y upgrade
yum  install -y epel-release
yum install -y libmcrypt libmcrypt-devel mcrypt mhash
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
wget -c https://dl.gocn.io/golang/1.9/go1.9.linux-amd64.tar.gz
tar zxvf go1.9.linux-amd64.tar.gz  -C /usr/local
chmod -R 777 ./go
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
a=$(grep 'temporary password' /var/log/mysqld.log |cut -d ":" -f 4,10 | sed 's/^[ \t]*//g')
echo $a