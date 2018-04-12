env.php
```php
<?php
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'develop');
```

hosts
```bash
127.0.0.1       yaf-project.my
```


nginx config
```bash
server{

        root /home/user/php/yaf/public/;
        index index.php;

        server_name yaf-project.my;

        access_log /var/log/nginx/access-yafproject.log;
        error_log /var/log/nginx/error-yaiproject.log;

        location / {
                try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.1-fpm.sock;
        }
        if (!-e $request_filename) {
                rewrite ^/(.*)  /index.php/$1 last;
        }
}
```