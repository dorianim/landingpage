# landingpage
A landingpage for users with some links and options to change teir ldap password and email.
This can be used at organizations where many different services are used (Rocket.Chat, Nextcloud, ...) to provide users with a nice looking overview.

# Features
- Give users an overview of all your services
- Let users change their password
- Force users to change their password if they are still using the default password
- Let users change their email
- Force users to change their email if they are still using the default email
- Let users generate Jitsi links (can be restricted to certain LDAP groups)

# Installation
- Put all files in this repo into a webroot.  
  `cd /var/www`  
  `git clone "https://github.com/Itsblue/landingpage"`
- Rename config.php.example to config.php  
  `cp config.php.example config.php`
- Make all necessary changes to config.php
## Configure Webserver
### Apache2
```
<VirtualHost *:80>
  ServerName example.com
  DocumentRoot /var/www/landingpage
  ErrorLog ${APACHE_LOG_DIR}/error.log

  <Directory "/var/www/landingpage">
    AllowOverride All
    RewriteEngine On

    RewriteCond %{REQUEST_URI} !^/index\.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .* index.php [L]
    RewriteRule ^(\.git)/(.*) error [F]
  </Directory>

</VirtualHost>
```
If you want to hide index.php from your URLs set `$serverConfig['hideIndexPhp'] = true;` in your config.php

### Nginx
```
server {
  listen 80 default_server;
  listen [::]:80 default_server;

  root /var/www/landingpage;
  server_name _;

  location ^~ / {
    index  index.php;
    try_files $uri $uri/ /index.php?$query_string;

    location ~* "\.php$" {
      # CHANGE TO YOUR NEEDS
      fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;

      fastcgi_split_path_info ^(.+?\.php)(/.*)$;
      try_files $fastcgi_script_name =404;
      fastcgi_index index.php;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      include fastcgi_params;
    }
  }
}
```
If you want to hide index.php from your URLs set `$serverConfig['hideIndexPhp'] = true;` in your config.php

# Updating
- To update, just go to your installation folder and pull  
  `cd /var/www/landingpage`  
  `git reset --hard`  
  `git pull`  

# Using LDAP over startTLS or SSl
For encrypted connections, there are two options:
1. Use SSL:
  - your host will have to look like this: `ldaps://<host>:636`
  - you will have to set useTls to false!
2. Use startTLS:
  - your host will have to look like this: `ldap://<host>:389`
  - you will have to set useTls to true!

In both cases your connection will be encrypted, and it will fail when there are certificate errors.  
By the way: You can get your SSL certificate by running:  
`echo -n | openssl s_client -connect <host>:636 | sed -ne '/-BEGIN CERTIFICATE-/,/-END CERTIFICATE-/p' > ldapserver.pem`

# Screenshots
### Landingpage
![Landingpage](https://github.com/Itsblue/landingpage/blob/main/screenshots/landingpage.png)
### Login (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/login.png)
### Change password (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/changePassword.png)
### Change email (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/changeEmail.png)
