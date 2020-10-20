# Transforms a default Laravel application into a Domain Driven Experience
This package is inspired by the work of [Brent Roose](https://github.com/brendt) and the [Spatie](https://spatie.be) team. Particularly by his [Domain Driven Development blog posts](https://stitcher.io/blog/laravel-beyond-crud-01-domain-oriented-laravel).

By utilizing the commands provided in this package, you can convert your Laravel application into a Domain Driven experience by reorganizing the app structure and updating namespaces. Laravel Fortify and Jetstream are supported.

This package also provides several useful make commands for generating classes within the domain.

# To Install
```bash

composer require anteris-dev/domain-driven-laravel

```

# To Setup your Domain
First you will need to reorganize your application. You can do this by running the domain setup command:

```bash

php artisan domain:setup {domain}

```
Where `{domain}` is the desired name of your domain.


This will perform the following actions.

1. Create a directory in `app` with the name of your domain
2. If Fortify or Jetstream is installed, move their Actions into the domain directory
    - The namespace of these actions is determined by whether or not Jetstream has team support installed. If team support is enabled, they will be placed in the `Team` namespace in your domain. Otherwise they will be placed in the `User` namespace
3. Move models into their own namespace within this domain directory
   - The namespace of the models is determined by the singular form of their filename. Models will be placed under this namespace in a `Models` directory
   - If Jetstream is installed with teams support, Jetstream models will be moved to the `Team` namespace
3. Move Laravel files to `app/Support`
4. Create an application layer directory at `app/App`
5. Update the bootstrap file to look for Laravel under `app/Support`
6. Update autoloading in composer
7. Store the current domain in composer under `extra.laravel.domain` for use during file generation and namespace updates
8. Dump composer autoloads

# Make Commands

This package provides the following make commands for your ease.

| Command | Description |
|--|--|
| make:app:controller {layer} {name} | Creates a new [application](https://stitcher.io/blog/laravel-beyond-crud-07-entering-the-application-layer) controller in the `app/App/{layer}/Controllers` directory. |
| make:app:viewmodel {layer} {name} | Creates a new [application](https://stitcher.io/blog/laravel-beyond-crud-07-entering-the-application-layer) view model in the `app/App/{layer}/ViewModels` directory. |
| make:domain:action {subdomain} {name} | Creates a new [action](https://stitcher.io/blog/laravel-beyond-crud-03-actions) in the `app/{domain}/{subdomain}/Actions` directory. |
| make:domain:dto {subdomain} {name} | Creates a new [data transfer object](https://stitcher.io/blog/laravel-beyond-crud-02-working-with-data) in the `app/{domain}/{subdomain}/DataTransferObjects` directory. |
| make:domain:model {subdomain} {name} | Creates a new [model](https://stitcher.io/blog/laravel-beyond-crud-04-models) in the `app/{domain}/{subdomain}/Models` directory. |
