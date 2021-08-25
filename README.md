<h1 align="center">
    Landingpage
</h1>

<p align="center">
    <a href="https://github.com/Itsblue/landingpage/actions/workflows/docker.yml">
        <img src="https://github.com/Itsblue/landingpage/actions/workflows/docker.yml/badge.svg" alt="Badge publish Docker image" />
    </a>
    <a href="https://raw.githubusercontent.com/ajenti/ajenti/master/LICENSE"> 
        <img src="https://img.shields.io/github/license/linuxmuster/linuxmuster-webui7?label=License" alt="Badge License" />
    </a>
    <a href="https://ask.linuxmuster.net">
        <img src="https://img.shields.io/discourse/users?logo=discourse&logoColor=white&server=https%3A%2F%2Fask.linuxmuster.net" alt="Community Forum"/>
    </a>
    <a href="https://crowdin.com/project/linuxmusternet">
        <img src="https://badges.crowdin.net/linuxmusternet/localized.svg" />
    </a>
</p>

A landingpage for users with some links and options to change teir ldap password and email.
This can be used at organizations where many different services are used (Rocket.Chat, Nextcloud, ...) to provide users with a nice looking overview.

## Features
- Give users an overview of all your services
- Let users change their password
- Force users to change their password if they are still using the default password
- Let users change their email
- Force users to change their email if they are still using the default email
- Let users generate Jitsi links (can be restricted to certain LDAP groups)

# Installation
The official installation method is using Docker:
1. Create a folder for installation:
    ```bash
    mkdir /opt/landingpage && cd /opt/landingpage
    ```
2. Create the file docker-compose.yml with this content:
    ```yaml
    version: "3.7"
    services:
      landingpage:
        image: itsblue/landingpage
        restart: always
        ports:
          - "5080:80"
        volumes:
          - ./data:/data
    ```
3. Adjust the port (default `5080`) to your needs
4. Start the landingpage:
    ```bash
    docker-compose up -d
    ```
5. Done! You can reach your landingpage on `localhost:5080`
6. Adjust you `config.php` in `/opt/landingpage/data/config.php`
7. [OPTIONAL] To setup ssl/https, please use a reverse proxy like nginx

# Updating
To update, just go to your installation folder and pull  
```bash
cd /opt/landingpage
docker-compose pull
docker-compose down && docker-compose up -d
```
  
# Troubleshooting
For troubleshooting, take a look at the logs:
```bash
cd /opt/landingpage
docker-compose logs -f
```

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
<table align="center">
    <tr>
        <td align="center">
            <a href="https://github.com/Itsblue/landingpage/blob/main/screenshots/landingpage.png">
                <img src="https://github.com/Itsblue/landingpage/blob/main/screenshots/landingpage.png" alt="Screenshot landingpage" width="500px" />
            </a>
        </td>
        <td align="center">
            <a href="https://github.com/Itsblue/landingpage/blob/main/screenshots/login.png">
                <img src="https://github.com/Itsblue/landingpage/blob/main/screenshots/login.png" alt="Screenshot login (LDAP)" width="500px" />
            </a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <a href="https://github.com/Itsblue/landingpage/blob/main/screenshots/changePassword.png">
                <img src="https://github.com/Itsblue/landingpage/blob/main/screenshots/changePassword.png" alt="Screenshot change password (LDAP)" width="500px" />
            </a>
        </td>
        <td align="center">
            <a href="https://github.com/Itsblue/landingpage/blob/main/screenshots/changeEmail.png">
                <img src="https://github.com/Itsblue/landingpage/blob/main/screenshots/changeEmail.png" alt="Screenshot change email (LDAP)" width="500px" />
            </a>
        </td>
    </tr>
</table>
