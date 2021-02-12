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

# Usage
- Put all files in this repo into a webroot and rename config.php.example to config.php.
- Make all necessary changes to config.php
- If you don't want to have index.php in the URL, use Apache2 and make sure mod_rewrite is enabled and `AllowOverride All` is set in your Apache config
- Enjoy ;)


# Screenshots
### Landingpage
![Landingpage](https://github.com/Itsblue/landingpage/blob/main/screenshots/landingpage.png)
### Login (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/login.png)
### Change password (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/changePassword.png)
### Change email (when ldap is enabled)
![Login](https://github.com/Itsblue/landingpage/blob/main/screenshots/changeEmail.png)
