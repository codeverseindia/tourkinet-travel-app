# Tour Kinet Travel App

The Travel App provides the Tour Kinet API client, package and blog services, destination and inquiry pages, route registration, SEO metadata, and default Bootstrap-based travel templates. It depends on `codeverseindia/tourkinet-lightphp-core:^1.0`; the Core owns routing, HTTP transport, security, configuration, caching, and rendering primitives.

## Development

The package is developed in the parent starter repository:

```sh
composer install
php packages/travel-app/tests/run.php
```

API settings are supplied by the client website in its private `.env` and `app/Config/tourkinet.php`. The API key is sent only in a server-side Bearer header. Local HTTP is allowed only for `api.tourkinet.loc` while `APP_ENV=local`; all other API hosts require HTTPS and public DNS resolution. Package and blog responses may be cached; inquiries are never cached.

Endpoint paths are configured in the client site's `app/Config/tourkinet.php`. The current defaults reflect the existing documented v1 integration: `/packages`, `/packages/{slug}`, `/blogs`, `/blogs/{slug}`, and `/inquiries`.

## Template overrides

Templates under `resources/views/` are package defaults. The Core resolves client-site templates first, then Travel App templates, then Core error templates. Add a matching file under the website's `app/Views/` to override a default without changing this package or `vendor/`.

## Release

Export `packages/travel-app` as a standalone repository with `composer.json` at its root, then tag its releases independently from LightPHP Core. The package's `^1.0` Core requirement must remain compatible with the minimum Core release. Do not publish or push without explicit approval.
