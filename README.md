<h1 align="center">
    Landingpage
</h1>

<p align="center">
    <a href="https://github.com/dorianim/landingpage/releases/latest">
        <img src="https://img.shields.io/github/v/release/dorianim/landingpage?logo=github&logoColor=white" alt="GitHub release"/>
    </a>
    <a href="https://www.gnu.org/licenses/agpl-3.0">
        <img src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg" />
    </a>
    <a href="https://github.com/dorianim/landingpage/actions/workflows/release.yml">
        <img src="https://github.com/dorianim/landingpage/actions/workflows/release.yml/badge.svg" alt="Badge release image" />
    </a>
    <a href="https://hub.docker.com/r/dorianim/landingpage">
        <img src="https://img.shields.io/docker/pulls/dorianim/landingpage.svg" alt="Docker pulls" />
    </a>
</p>

A landingpage for users with some links and options to change their ldap password and email.
This can be used at organizations where many services are used (Rocket.Chat, Nextcloud, ...) to provide users with a nice looking overview.
The application has been purpose-built for a fairly simple use-case. Due to limited time, I don't have much of a desire to widen the scope.

## Features

- Give users an overview of all your services
- Categorize your services
- Let users change their password
- Force users to change their password if they are still using the default password
- Let users change their email
- Force users to change their email if they are still using the default email
- Let users generate Jitsi links (can be restricted to certain LDAP groups)
- Use OpenID-Connect user initial user authentication

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
       image: dorianim/landingpage
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
6. Adjust you `config.yaml` in `/opt/landingpage/data/config.yaml`
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
            <a href="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/landingpage.png">
                <img src="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/landingpage.png" alt="Screenshot landingpage" width="500px" />
            </a>
        </td>
        <td align="center">
            <a href="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/login.png">
                <img src="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/login.png" alt="Screenshot login (LDAP)" width="500px" />
            </a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <a href="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/changePassword.png">
                <img src="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/changePassword.png" alt="Screenshot change password (LDAP)" width="500px" />
            </a>
        </td>
        <td align="center">
            <a href="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/changeEmail.png">
                <img src="https://raw.githubusercontent.com/dorianim/landingpage/main/.github/media/changeEmail.png" alt="Screenshot change email (LDAP)" width="500px" />
            </a>
        </td>
    </tr>
</table>
