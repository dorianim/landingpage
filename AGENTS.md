# Repository Guidelines

## Project Structure & Module Organization

This PHP 8.5.12 application runs on Apache in Docker. Request handling is in `src/index.php`; LDAP, Traefik forward-auth header handling, configuration, and CLI migration code are in `src/ldap.php`, `src/forwardAuth.php`, `src/configManager.php`, and `src/cli.php`. Themes and translations live in `src/themes/` and `src/translations/`; bundled CSS and JavaScript are in `src/css/` and `src/js/`. Refer to `src/config.example.yaml` for settings. Runtime configuration and custom assets belong in mounted `data/`. Container and Apache setup is in `Dockerfile`, `docker-compose.yml`, and `conf/`.

## Build, Test, and Development Commands

- `docker-compose up --build` builds the image and starts the app at `http://localhost:5080`; the compose file mounts `src/` and `data/` for local iteration.
- `docker-compose down` stops the container.
- `find src -name '*.php' -print0 | xargs -0 -n1 php -l` checks PHP syntax using the local PHP CLI. Keep changes compatible with PHP 8.5.

There is no automated test suite or configured linter. For behavior changes, run the app and exercise the affected login, LDAP, forward-auth, or landing page flow.

## Coding Style & Naming Conventions

Match existing PHP style: start files with `<?php`, use descriptive class names (for example, `LandingpageConfigManager`), and camelCase methods and configuration keys. Indentation varies between two and four spaces, so follow the surrounding file. Put user-facing strings in translation files and preserve the example YAML structure when adding settings.

## Testing Guidelines

No test framework, test directory, or coverage target is configured. Run the PHP syntax check for changed files; verify a Docker image build for deployment or dependency changes. Include manual verification steps in the pull request.

## Commit & Pull Request Guidelines

Recent commits use short subjects, often prefixed `Fix:` or `Feat:` (for example, `Fix: use local bootstrap dist`). Keep commits focused. Pull requests should explain the change, list verification, link related issues, and include screenshots for UI changes. Do not commit live credentials or deployment-specific configuration.

## Security & Configuration

Use `src/config.example.yaml` for documented options and keep secrets in runtime-mounted `/data/config.yaml`. Never add real credentials or private certificates to source control.
