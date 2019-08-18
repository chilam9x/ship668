# WE CARE 24/7 

## Install Environment

- Goto folder /var/www/html and clone source code from git by command : git clone https://NguyenBaQuan@bitbucket.org/hoanvusolutions/app-giaohang-web.git
- Goto folder /var/www/html/delivery
- Install Laravel Packages: `composer install` or update if packages existed `composer update`
- go to public folder and install bower.io : bower install `check node version`
- Publish Laravel Key: `php artisan key:generate`


## Start project
- Create table for database and run all seed: `php artisan migrate --seed`
- Cronjob update revenue of system : `* * * * * /usr/bin/php var/www/html/delivery/artisan schedule:run >> /dev/null 2>&1`
- Run Cronjob : `scheldule: /usr/bin/php `
- Command update database : `php artisan updatedistrict` and `php artisan update:province`
- Copy file .env.example into file .env & edit configurations: APP, DB, REDIS, MAIL, ... : .env template file:

## Start Server

### For Development Environment
- Run server: `php artisan serve`

### For Testing Environment:
*Config Nginx:* 
- Create file delivery.hoanvusolutions.com.vn.conf in folder /etc/nginx/site-availables with these following contents:
```
server {
     listen 80;
    
            server_name giaohang.hoanvusolutions.com.vn;
    
            #proxy_cache on;
            location / {
    
                    proxy_read_timeout 300;
                    proxy_connect_timeout 300;
                    proxy_send_timeout 300;
                    send_timeout 300;
    
                    proxy_set_header Host $http_host;
                    proxy_set_header X-Real-IP $remote_addr;
                    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                    proxy_pass       http://localhost:8000/;
            }
    
            location /assets/ {
                     expires 30d;
                add_header Pragma public;
                add_header Cache-Control "public";
    
                proxy_pass http://localhost:8000/assets/;
              # or proxy_pass http://localhost:2368;
                proxy_set_header Host $host;
                     proxy_buffering off;
            }

}
```
- Link that file to site-enabled: `ln -s ../sites-available/giaohang.hoanvusolutions.com.vn .`
- Reload Nginx: `service nginx reload`

*Start project on port 8009:* 
- Goto folder /var/www/html/delivery

### For Production Environment:
```
server {
    listen 80;
    server_name www.smartexpress.vn;
    root /www.smartexpress.vn/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
  
    location ~ \.(css|js)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public";
    }
   
}
```
- Link that file to site-enabled: `ln -s ../sites-available/www.smartexpress.vn.`
- Reload Nginx: `service nginx reload`

## Chức năng push notification to device
$ composer install
$ php artisan vendor:publish
Chỉnh thông tin Firebase FCM_SERVER_KEY, FCM_SENDER_ID trong .env

## Chức năng google map hiển thị shipper
1. Cài Redis trên server:  sudo apt-get install redis-server
2. Cài NodeJS trên server: npm install express redis socket.io --save
3. Chỉnh lại URL cho socket.io: /var/www/html/delivery/resources/views/admin/elements/users/shipper/maps.blade.php